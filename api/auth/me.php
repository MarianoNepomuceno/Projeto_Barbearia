<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Método não permitido.'], 405);
}

$usuario = currentUser();

if (!$usuario) {
    jsonResponse(['success' => false, 'message' => 'Não autenticado.'], 401);
}

jsonResponse([
    'success' => true,
    'usuario' => $usuario,
]);
