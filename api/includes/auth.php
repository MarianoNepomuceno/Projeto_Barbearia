<?php

declare(strict_types=1);

function currentUser(): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'telefone' => $_SESSION['usuario_telefone'] ?? '',
        'tipo' => $_SESSION['usuario_tipo'] ?? '',
    ];
}

function loginUser(array $usuario): void
{
    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_telefone'] = $usuario['telefone'] ?? '';
    $_SESSION['usuario_tipo'] = $usuario['tipo'];
}

function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}

function requireLogin(): array
{
    $usuario = currentUser();

    if (!$usuario) {
        jsonResponse([
            'success' => false,
            'message' => 'Faça login para continuar.',
        ], 401);
    }

    return $usuario;
}

function requireAdmin(): array
{
    $usuario = requireLogin();

    if ($usuario['tipo'] !== 'admin') {
        jsonResponse([
            'success' => false,
            'message' => 'Acesso restrito ao administrador.',
        ], 403);
    }

    return $usuario;
}

function requireCliente(): array
{
    $usuario = requireLogin();

    if ($usuario['tipo'] !== 'cliente') {
        jsonResponse([
            'success' => false,
            'message' => 'Acesso restrito a clientes.',
        ], 403);
    }

    return $usuario;
}
