<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    requireLogin();

    $dataFiltro = $_GET['data'] ?? null;
    $sql = 'SELECT id, data, hora FROM bloqueios';
    $params = [];

    if ($dataFiltro) {
        $sql .= ' WHERE data = :data';
        $params['data'] = $dataFiltro;
    }

    $sql .= ' ORDER BY data ASC, hora ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $bloqueios = array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'data' => $row['data'],
            'hora' => formatHora($row['hora']),
        ];
    }, $stmt->fetchAll());

    jsonResponse(['success' => true, 'bloqueios' => $bloqueios]);
}

if ($method === 'POST') {
    requireAdmin();
    $data = getJsonInput();

    $dataBloqueio = $data['data'] ?? '';
    $hora = $data['hora'] ?? '';

    if ($dataBloqueio === '' || $hora === '') {
        jsonResponse(['success' => false, 'message' => 'Informe data e horário.'], 422);
    }

    $horaNormalizada = normalizeHora($hora);

    $stmt = $pdo->prepare('SELECT id FROM agendamentos WHERE data = :data AND hora = :hora LIMIT 1');
    $stmt->execute(['data' => $dataBloqueio, 'hora' => $horaNormalizada]);

    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Já existe um agendamento neste horário.'], 409);
    }

    $stmt = $pdo->prepare('SELECT id FROM bloqueios WHERE data = :data AND hora = :hora LIMIT 1');
    $stmt->execute(['data' => $dataBloqueio, 'hora' => $horaNormalizada]);

    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Horário já está bloqueado.'], 409);
    }

    $stmt = $pdo->prepare('INSERT INTO bloqueios (data, hora) VALUES (:data, :hora)');
    $stmt->execute(['data' => $dataBloqueio, 'hora' => $horaNormalizada]);

    jsonResponse([
        'success' => true,
        'message' => 'Horário bloqueado com sucesso.',
        'bloqueio' => [
            'id' => (int) $pdo->lastInsertId(),
            'data' => $dataBloqueio,
            'hora' => formatHora($horaNormalizada),
        ],
    ], 201);
}

if ($method === 'DELETE') {
    requireAdmin();
    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'Bloqueio inválido.'], 422);
    }

    $stmt = $pdo->prepare('DELETE FROM bloqueios WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(['success' => false, 'message' => 'Bloqueio não encontrado.'], 404);
    }

    jsonResponse(['success' => true, 'message' => 'Horário liberado com sucesso.']);
}

jsonResponse(['success' => false, 'message' => 'Método não permitido.'], 405);
