<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function getConfig(): array
{
    $configPath = __DIR__ . '/../config/database.php';

    if (!file_exists($configPath)) {
        jsonResponse([
            'success' => false,
            'message' => 'Arquivo api/config/database.php não encontrado. Copie database.example.php.',
        ], 500);
    }

    return require $configPath;
}

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = getConfig();

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['dbname'],
        $config['charset']
    );

    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '[]', true);

    return is_array($data) ? $data : [];
}

function normalizeHora(string $hora): string
{
    if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
        return $hora . ':00';
    }

    return $hora;
}

function formatHora(string $hora): string
{
    return substr($hora, 0, 5);
}
