<?php

declare(strict_types=1);

namespace OAuth\Http;

use OAuth\Interfaces\HttpClientInterface;

class CurlHttpClient implements HttpClientInterface
{
    public function post(string $url, array $data, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('Failed to make HTTP request');
        }

        $data = json_decode($body, true);
        if ($data === null) {
            throw new \RuntimeException('Response is not valid JSON: ' . $body);
        }

        return $data;
    }

    public function get(string $url, array $params = [], array $headers = []): array
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('Failed to make HTTP request');
        }

        $data = json_decode($body, true);
        if ($data === null) {
            throw new \RuntimeException('Response is not valid JSON: ' . $body);
        }

        return $data;
    }
} 