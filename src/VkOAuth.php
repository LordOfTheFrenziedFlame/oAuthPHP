<?php

declare(strict_types=1);

namespace OAuth;

use OAuth\Interfaces\HttpClientInterface;
use OAuth\Interfaces\OAuthProviderInterface;
use OAuth\Interfaces\SessionInterface;

class VkOAuth implements OAuthProviderInterface
{
    private array $config;
    private SessionInterface $session;
    private HttpClientInterface $httpClient;

    public function __construct(
        array $config,
        SessionInterface $session,
        HttpClientInterface $httpClient
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->httpClient = $httpClient;
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

        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        $this->session->set('pkce_code_verifier', $codeVerifier);

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

        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

        return $this->httpClient->post('https://id.vk.com/oauth2/auth', $params, $headers);
    }

    public function getUserInfo(string $accessToken): array
    {
        $params = [
            'client_id' => $this->config['client_id'],
            'access_token' => $accessToken,
        ];

        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

        return $this->httpClient->post('https://id.vk.com/oauth2/user_info', $params, $headers);
    }
}
