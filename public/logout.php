<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use OAuth\Core\SessionManager;

$session = new SessionManager();

$session->clear(true);

header('Location: /');
exit;
