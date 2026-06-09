<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    requireAdmin();

    $stmt = $pdo->query(
        "SELECT id, nome, email, telefone, criado_em
         FROM usuarios
         WHERE tipo = 'cliente'
         ORDER BY nome ASC"
    );

    $clientes = array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'nome' => $row['nome'],
            'email' => $row['email'],
            'telefone' => $row['telefone'] ?? '',
            'criado_em' => $row['criado_em'],
        ];
    }, $stmt->fetchAll());

    jsonResponse(['success' => true, 'clientes' => $clientes]);
}

if ($method === 'DELETE') {
    requireAdmin();

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'Cliente inválido.'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, tipo FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        jsonResponse(['success' => false, 'message' => 'Cliente não encontrado.'], 404);
    }

    if ($usuario['tipo'] !== 'cliente') {
        jsonResponse(['success' => false, 'message' => 'Somente contas de clientes podem ser excluídas.'], 403);
    }

    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $id]);

    jsonResponse(['success' => true, 'message' => 'Conta do cliente excluída com sucesso.']);
}

jsonResponse(['success' => false, 'message' => 'Método não permitido.'], 405);
