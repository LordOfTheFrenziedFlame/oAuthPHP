<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use OAuth\Core\SessionManager;
use OAuth\Factory\OAuthProviderFactory;
use OAuth\Http\CurlHttpClient;

$session = new SessionManager();
$config = require __DIR__ . '/../config/vk-config.php';

$factory = new OAuthProviderFactory(
    new CurlHttpClient(),
    $session
);

try {
    $vkOAuth = $factory->create('vk', $config);
    $authUrl = $vkOAuth->getAuthUrl();
    header('Location: ' . $authUrl);
    exit;
} catch (\Exception $e) {
    $session->set('error', $e->getMessage());
    header('Location: /');
    exit;
}