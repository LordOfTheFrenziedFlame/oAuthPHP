<?php

if (php_sapi_name() === 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
    if ($url['path'] === '/oauth/vk/callback') {
        require __DIR__ . '/oauth/vk/callback.php';
        return true;
    }
}

require __DIR__ . '/index.php'; 