<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use OAuth\Core\SessionManager;
use OAuth\VkOAuth;

$session = new SessionManager();

$config = require __DIR__ . '/../config/vk-config.php';

$vkOAuth = new VKOAuth($config,$session);

try {
    $authUrl = $vkOAuth->getAuthUrl();
    header('Location: ' . $authUrl);
    exit;
} catch (\Exception $e) {
    $session->set('error', $e->getMessage());
    header('Location: /');
    exit;
}