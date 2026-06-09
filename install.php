<?php

declare(strict_types=1);

$configPath = __DIR__ . '/api/config/database.php';

if (!file_exists($configPath)) {
    exit("Copie api/config/database.example.php para api/config/database.php e configure o MySQL.\n");
}

$config = require $configPath;

try {
    $dsn = sprintf('mysql:host=%s;charset=%s', $config['host'], $config['charset']);
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    if ($schema === false) {
        throw new RuntimeException('Não foi possível ler database/schema.sql');
    }

    $statements = array_filter(array_map('trim', explode(';', $schema)));

    foreach ($statements as $statement) {
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }

    $pdo->exec('USE ' . $config['dbname']);

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => 'admin@barbearia.com']);

    if (!$stmt->fetch()) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare(
            'INSERT INTO usuarios (nome, email, telefone, senha, tipo)
             VALUES (:nome, :email, :telefone, :senha, :tipo)'
        );
        $insert->execute([
            'nome' => 'Administrador',
            'email' => 'admin@barbearia.com',
            'telefone' => null,
            'senha' => $hash,
            'tipo' => 'admin',
        ]);

        echo "Banco criado com sucesso.\n";
        echo "Admin padrão: admin@barbearia.com / admin123\n";
    } else {
        echo "Banco já configurado. Admin padrão já existe.\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Erro na instalação: ' . $e->getMessage() . "\n";
    exit(1);
}
