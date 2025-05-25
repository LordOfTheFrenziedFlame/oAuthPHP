<?php

declare(strict_types=1);

namespace OAuth;

use OAuth\Core\SessionManager;

class VkOAuth
{
    private array $config;
    private SessionManager $session;

    public function __construct(array $config, SessionManager $session)
    {
        $this->config = $config;
        $this->session = $session;
    }

    private function generateCodeVerifier(int $length = 64): string
    {
        return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
    }

    private function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    public function getAuthUrl(): string
    {
        $state = bin2hex(random_bytes(16));
        $this->session->set('oauth_state', $state);
        error_log('Generated state: ' . $state);
        error_log('Saved state in session: ' . $this->session->get('oauth_state'));


        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        $this->session->set('pkce_code_verifier', $codeVerifier);
        error_log('Generated code_verifier: ' . $codeVerifier);
        error_log('Saved code_verifier in session: ' . $this->session->get('pkce_code_verifier'));

        $params = [
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'scope' => implode(',', $this->config['scope']),
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ];

        return 'https://id.vk.com/authorize?' . http_build_query($params);
    }

    public function getAccessToken(string $code, ?string $deviceId = null, ?string $state = null): array
    {
        $savedState = $this->session->get('oauth_state');
        if (!$savedState) {
            throw new \RuntimeException('State not found in session');
        }
        $this->session->remove('oauth_state');

        $codeVerifier = $this->session->get('pkce_code_verifier');
        error_log('Using code_verifier from session: ' . ($codeVerifier ?: 'not set'));
        if (!$codeVerifier) {
            throw new \RuntimeException('PKCE code_verifier not found in session');
        }
        $this->session->remove('pkce_code_verifier');

        if (!$deviceId) {
            throw new \RuntimeException('device_id is required for VK ID token exchange');
        }
        if (!$state) {
            throw new \RuntimeException('state is required for VK ID token exchange');
        }

        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->config['redirect_uri'],
            'client_id' => $this->config['client_id'],
            'code_verifier' => $codeVerifier,
            'device_id' => $deviceId,
            'state' => $state,
        ];

        $url = 'https://id.vk.com/oauth2/auth';

        error_log('VK TOKEN REQUEST:');
        error_log('URL: ' . $url);
        error_log('Method: POST');
        error_log('Headers: Content-Type: application/x-www-form-urlencoded');
        error_log('Params: ' . print_r($params, true));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        error_log('VK TOKEN RESPONSE:');
        error_log('HTTP Code: ' . $httpCode);
        error_log('Headers: ' . $headers);
        error_log('Body: ' . $body);

        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('Failed to get access token (no response from VK)');
        }

        $data = json_decode($body, true);

        if ($data === null) {
            throw new \RuntimeException('VK token response is not valid JSON: ' . $body);
        }

        if (isset($data['error'])) {
            throw new \RuntimeException($data['error_description'] ?? 'Unknown error: ' . json_encode($data));
        }

        return $data;
    }

    public function getUserInfo(string $accessToken): array
    {
        $url = 'https://id.vk.com/oauth2/user_info';
        $params = [
            'client_id' => $this->config['client_id'],
            'access_token' => $accessToken,
        ];
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($params),
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            throw new \RuntimeException('Failed to get user info');
        }
        $data = json_decode($response, true);
        if (isset($data['error'])) {
            throw new \RuntimeException($data['error_description'] ?? 'Unknown error');
        }
        return $data;
    }
}
