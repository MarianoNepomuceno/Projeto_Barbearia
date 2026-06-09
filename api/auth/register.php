<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método não permitido.'], 405);
}

$data = getJsonInput();
$nome = trim($data['nome'] ?? '');
$email = trim($data['email'] ?? '');
$telefone = trim($data['telefone'] ?? '');
$senha = $data['senha'] ?? '';
$confirmarSenha = $data['confirmar_senha'] ?? '';

if ($nome === '' || $email === '' || $senha === '') {
    jsonResponse(['success' => false, 'message' => 'Preencha nome, e-mail e senha.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Informe um e-mail válido.'], 422);
}

if (strlen($senha) < 6) {
    jsonResponse(['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres.'], 422);
}

if ($senha !== $confirmarSenha) {
    jsonResponse(['success' => false, 'message' => 'As senhas não coincidem.'], 422);
}

$pdo = getPDO();

$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);

if ($stmt->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Este e-mail já está cadastrado.'], 409);
}

$hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO usuarios (nome, email, telefone, senha, tipo) VALUES (:nome, :email, :telefone, :senha, :tipo)'
);
$stmt->execute([
    'nome' => $nome,
    'email' => $email,
    'telefone' => $telefone !== '' ? $telefone : null,
    'senha' => $hash,
    'tipo' => 'cliente',
]);

$usuario = [
    'id' => (int) $pdo->lastInsertId(),
    'nome' => $nome,
    'email' => $email,
    'telefone' => $telefone,
    'tipo' => 'cliente',
];

loginUser($usuario);

jsonResponse([
    'success' => true,
    'message' => 'Cadastro realizado com sucesso.',
    'usuario' => currentUser(),
], 201);
