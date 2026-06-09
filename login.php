<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Location: pages/login.html');
    exit;
}

require __DIR__ . '/api/auth/login.php';
