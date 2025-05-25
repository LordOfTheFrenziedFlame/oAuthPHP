<?php

declare(strict_types=1);

namespace OAuth\Interfaces;

interface OAuthProviderInterface
{
    public function getAuthUrl(): string;
    public function getAccessToken(string $code, ?string $deviceId = null, ?string $state = null): array;
    public function getUserInfo(string $accessToken): array;
} 