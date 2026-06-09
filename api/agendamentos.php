<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

function mapAgendamento(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'usuario_id' => (int) $row['usuario_id'],
        'nome' => $row['nome'],
        'telefone' => $row['telefone'] ?? '',
        'data' => $row['data'],
        'hora' => formatHora($row['hora']),
        'servicos' => json_decode($row['servicos'], true) ?: [],
        'total' => number_format((float) $row['total'], 2, '.', ''),
    ];
}

if ($method === 'GET') {
    $usuario = requireLogin();
    $dataFiltro = $_GET['data'] ?? null;

    if ($usuario['tipo'] === 'admin') {
        $sql = 'SELECT a.*, u.nome, u.telefone
                FROM agendamentos a
                INNER JOIN usuarios u ON u.id = a.usuario_id';
        $params = [];

        if ($dataFiltro) {
            $sql .= ' WHERE a.data = :data';
            $params['data'] = $dataFiltro;
        }

        $sql .= ' ORDER BY a.data ASC, a.hora ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $sql = 'SELECT a.*, u.nome, u.telefone
                FROM agendamentos a
                INNER JOIN usuarios u ON u.id = a.usuario_id
                WHERE a.usuario_id = :usuario_id';
        $params = ['usuario_id' => $usuario['id']];

        if ($dataFiltro) {
            $sql .= ' AND a.data = :data';
            $params['data'] = $dataFiltro;
        }

        $sql .= ' ORDER BY a.data ASC, a.hora ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    $agendamentos = array_map('mapAgendamento', $stmt->fetchAll());

    jsonResponse(['success' => true, 'agendamentos' => $agendamentos]);
}

if ($method === 'POST') {
    $usuario = requireCliente();
    $data = getJsonInput();

    $dataAgendamento = $data['data'] ?? '';
    $hora = $data['hora'] ?? '';
    $servicos = $data['servicos'] ?? [];
    $total = $data['total'] ?? null;

    if ($dataAgendamento === '' || $hora === '') {
        jsonResponse(['success' => false, 'message' => 'Informe data e horário.'], 422);
    }

    if (!is_array($servicos) || $servicos === []) {
        jsonResponse(['success' => false, 'message' => 'Selecione pelo menos um serviço.'], 422);
    }

    if ($total === null || !is_numeric($total)) {
        jsonResponse(['success' => false, 'message' => 'Total inválido.'], 422);
    }

    $horaNormalizada = normalizeHora($hora);

    $stmt = $pdo->prepare('SELECT id FROM agendamentos WHERE data = :data AND hora = :hora LIMIT 1');
    $stmt->execute(['data' => $dataAgendamento, 'hora' => $horaNormalizada]);

    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Esse horário já está ocupado.'], 409);
    }

    $stmt = $pdo->prepare('SELECT id FROM bloqueios WHERE data = :data AND hora = :hora LIMIT 1');
    $stmt->execute(['data' => $dataAgendamento, 'hora' => $horaNormalizada]);

    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Esse horário está bloqueado.'], 409);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO agendamentos (usuario_id, data, hora, servicos, total)
         VALUES (:usuario_id, :data, :hora, :servicos, :total)'
    );
    $stmt->execute([
        'usuario_id' => $usuario['id'],
        'data' => $dataAgendamento,
        'hora' => $horaNormalizada,
        'servicos' => json_encode($servicos, JSON_UNESCAPED_UNICODE),
        'total' => (float) $total,
    ]);

    $id = (int) $pdo->lastInsertId();

    jsonResponse([
        'success' => true,
        'message' => 'Agendamento realizado com sucesso.',
        'agendamento' => [
            'id' => $id,
            'nome' => $usuario['nome'],
            'telefone' => $usuario['telefone'],
            'data' => $dataAgendamento,
            'hora' => formatHora($horaNormalizada),
            'servicos' => $servicos,
            'total' => number_format((float) $total, 2, '.', ''),
        ],
    ], 201);
}

if ($method === 'DELETE') {
    $usuario = requireLogin();
    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'Agendamento inválido.'], 422);
    }

    $stmt = $pdo->prepare('SELECT * FROM agendamentos WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $agendamento = $stmt->fetch();

    if (!$agendamento) {
        jsonResponse(['success' => false, 'message' => 'Agendamento não encontrado.'], 404);
    }

    if ($usuario['tipo'] !== 'admin' && (int) $agendamento['usuario_id'] !== $usuario['id']) {
        jsonResponse(['success' => false, 'message' => 'Você não pode cancelar este agendamento.'], 403);
    }

    $stmt = $pdo->prepare('DELETE FROM agendamentos WHERE id = :id');
    $stmt->execute(['id' => $id]);

    jsonResponse(['success' => true, 'message' => 'Agendamento cancelado com sucesso.']);
}

jsonResponse(['success' => false, 'message' => 'Método não permitido.'], 405);
