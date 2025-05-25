<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use OAuth\Core\SessionManager;

$session = new SessionManager();

$user = $session->get('user', []);
$error = $session->get('error', '');

if ($error) {
    $session->remove('error');
}

require_once __DIR__ . '/../templates/welcome.php';
