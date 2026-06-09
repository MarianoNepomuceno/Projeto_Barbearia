<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT servico, valor FROM precos ORDER BY id');
    $rows = $stmt->fetchAll();

    $precos = [];
    foreach ($rows as $row) {
        $precos[$row['servico']] = (float) $row['valor'];
    }

    jsonResponse(['success' => true, 'precos' => $precos]);
}

if ($method === 'PUT') {
    requireAdmin();
    $data = getJsonInput();
    $precos = $data['precos'] ?? null;

    if (!is_array($precos) || $precos === []) {
        jsonResponse(['success' => false, 'message' => 'Preços inválidos.'], 422);
    }

    $stmt = $pdo->prepare('UPDATE precos SET valor = :valor WHERE servico = :servico');

    foreach ($precos as $servico => $valor) {
        $stmt->execute([
            'servico' => $servico,
            'valor' => (float) $valor,
        ]);
    }

    jsonResponse(['success' => true, 'message' => 'Preços atualizados com sucesso.']);
}

jsonResponse(['success' => false, 'message' => 'Método não permitido.'], 405);
