<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use OAuth\Core\SessionManager;
use OAuth\VkOAuth;

$session = new SessionManager();
$config = require __DIR__ . '/../../../config/vk-config.php';
$vkOAuth = new VkOAuth($config, $session);

if (isset($_GET['error'])) {
    $session->set('error', $_GET['error_description'] ?? $_GET['error']);
    header('Location: /');
    exit;
}

if (!isset($_GET['code'])) {
    $session->set('error', 'Не получен код авторизации от VK');
    header('Location: /');
    exit;
}


error_log('Received state from VK: ' . ($_GET['state'] ?? 'not set'));
error_log('State in session: ' . $session->get('oauth_state'));

if (!isset($_GET['state']) || $_GET['state'] !== $session->get('oauth_state')) {
    $session->set('error', 'Invalid state parameter');
    header('Location: /');
    exit;
}

try {
    $code = $_GET['code'];
    $deviceId = $_GET['device_id'] ?? null;
    $extId = $_GET['ext_id'] ?? null;
    
    error_log('Processing VK callback:');
    error_log('Code: ' . $code);
    error_log('Device ID: ' . ($deviceId ?? 'not set'));
    error_log('Ext ID: ' . ($extId ?? 'not set'));

    $tokenData = $vkOAuth->getAccessToken($code, $deviceId, $extId);
    $accessToken = $tokenData['access_token'] ?? null;
    if (!$accessToken) {
        throw new Exception('Не удалось получить access_token');
    }

    $userInfo = $vkOAuth->getUserInfo($accessToken);

    if (isset($tokenData['email'])) {
        $userInfo['email'] = $tokenData['email'];
    }

    $session->set('user', $userInfo);
    $session->remove('error');
    header('Location: /');
    exit;
} catch (Exception $e) {
    $session->set('error', $e->getMessage());
    header('Location: /');
    exit;
} 