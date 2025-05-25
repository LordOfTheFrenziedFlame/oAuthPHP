<?php

declare(strict_types=1);

namespace OAuth\Factory;

use OAuth\Interfaces\HttpClientInterface;
use OAuth\Interfaces\OAuthProviderInterface;
use OAuth\Interfaces\SessionInterface;
use OAuth\VkOAuth;

class OAuthProviderFactory
{
    private HttpClientInterface $httpClient;
    private SessionInterface $session;

    public function __construct(
        HttpClientInterface $httpClient,
        SessionInterface $session
    ) {
        $this->httpClient = $httpClient;
        $this->session = $session;
    }

    public function create(string $provider, array $config): OAuthProviderInterface
    {
        return match($provider) {
            'vk' => new VkOAuth($config, $this->session, $this->httpClient),
            default => throw new \InvalidArgumentException("Unknown provider: $provider")
        };
    }
} 