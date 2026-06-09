<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método não permitido.'], 405);
}

$data = getJsonInput();
$email = trim($data['email'] ?? '');
$senha = $data['senha'] ?? '';

if ($email === '' || $senha === '') {
    jsonResponse(['success' => false, 'message' => 'Informe e-mail e senha.'], 422);
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT id, nome, email, telefone, senha, tipo FROM usuarios WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    jsonResponse(['success' => false, 'message' => 'E-mail ou senha incorretos.'], 401);
}

loginUser($usuario);

jsonResponse([
    'success' => true,
    'message' => 'Login realizado com sucesso.',
    'usuario' => currentUser(),
]);
