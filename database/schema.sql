CREATE DATABASE IF NOT EXISTS barbearia
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE barbearia;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  telefone VARCHAR(20) DEFAULT NULL,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS precos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  servico VARCHAR(50) NOT NULL UNIQUE,
  valor DECIMAL(10, 2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS agendamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  data DATE NOT NULL,
  hora TIME NOT NULL,
  servicos JSON NOT NULL,
  total DECIMAL(10, 2) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_agendamento_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  UNIQUE KEY unique_horario (data, hora)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bloqueios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  data DATE NOT NULL,
  hora TIME NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_bloqueio (data, hora)
) ENGINE=InnoDB;

INSERT INTO precos (servico, valor) VALUES
  ('Degradê', 30.00),
  ('Social', 25.00),
  ('Tesoura', 35.00),
  ('Barba', 20.00),
  ('Sobrancelha', 10.00)
ON DUPLICATE KEY UPDATE valor = VALUES(valor);
