<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $usuario = requireCliente();

    $stmt = $pdo->prepare(
        'SELECT id, nome, email, telefone, tipo, criado_em FROM usuarios WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $usuario['id']]);
    $dados = $stmt->fetch();

    if (!$dados) {
        jsonResponse(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
    }

    jsonResponse([
        'success' => true,
        'perfil' => [
            'id' => (int) $dados['id'],
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? '',
            'tipo' => $dados['tipo'],
            'criado_em' => $dados['criado_em'],
        ],
    ]);
}

if ($method === 'PUT') {
    $usuario = requireCliente();
    $data = getJsonInput();

    $nome = trim($data['nome'] ?? '');
    $email = trim($data['email'] ?? '');
    $telefone = trim($data['telefone'] ?? '');
    $senhaAtual = $data['senha_atual'] ?? '';
    $novaSenha = $data['nova_senha'] ?? '';

    if ($nome === '' || $email === '') {
        jsonResponse(['success' => false, 'message' => 'Preencha nome e e-mail.'], 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Informe um e-mail válido.'], 422);
    }

    $stmt = $pdo->prepare('SELECT senha FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $usuario['id']]);
    $atual = $stmt->fetch();

    if (!$atual) {
        jsonResponse(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
    }

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1');
    $stmt->execute(['email' => $email, 'id' => $usuario['id']]);

    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Este e-mail já está em uso.'], 409);
    }

    $senhaHash = $atual['senha'];

    if ($novaSenha !== '') {
        if ($senhaAtual === '') {
            jsonResponse(['success' => false, 'message' => 'Informe a senha atual para alterá-la.'], 422);
        }

        if (!password_verify($senhaAtual, $atual['senha'])) {
            jsonResponse(['success' => false, 'message' => 'Senha atual incorreta.'], 401);
        }

        if (strlen($novaSenha) < 6) {
            jsonResponse(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres.'], 422);
        }

        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
    }

    $stmt = $pdo->prepare(
        'UPDATE usuarios
         SET nome = :nome, email = :email, telefone = :telefone, senha = :senha
         WHERE id = :id'
    );
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone !== '' ? $telefone : null,
        'senha' => $senhaHash,
        'id' => $usuario['id'],
    ]);

    loginUser([
        'id' => $usuario['id'],
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone,
        'tipo' => 'cliente',
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Perfil atualizado com sucesso.',
        'usuario' => currentUser(),
    ]);
}

jsonResponse(['success' => false, 'message' => 'Método não permitido.'], 405);
