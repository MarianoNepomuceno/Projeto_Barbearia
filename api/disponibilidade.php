<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Método não permitido.'], 405);
}

requireLogin();

$data = $_GET['data'] ?? '';

if ($data === '') {
    jsonResponse(['success' => false, 'message' => 'Informe a data.'], 422);
}

$pdo = getPDO();

$stmt = $pdo->prepare('SELECT hora FROM agendamentos WHERE data = :data ORDER BY hora ASC');
$stmt->execute(['data' => $data]);
$ocupados = array_map(static fn (array $row): string => formatHora($row['hora']), $stmt->fetchAll());

$stmt = $pdo->prepare('SELECT hora FROM bloqueios WHERE data = :data ORDER BY hora ASC');
$stmt->execute(['data' => $data]);
$bloqueados = array_map(static fn (array $row): string => formatHora($row['hora']), $stmt->fetchAll());

jsonResponse([
    'success' => true,
    'ocupados' => $ocupados,
    'bloqueados' => $bloqueados,
]);
