-- Script SQL para criação das tabelas do Sistema de Gestão de Lavagem de Veículos
-- Banco de Dados: MySQL

-- Tabela de Unidades/Locais
CREATE TABLE IF NOT EXISTS `units` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `address` TEXT,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `header_image_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Horários de Funcionamento das Unidades
CREATE TABLE IF NOT EXISTS `unit_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `unit_id` INT NOT NULL,
  `day_of_week` INT NOT NULL COMMENT '0=Domingo, 1=Segunda, ..., 6=Sábado',
  `open_time` TIME NULL,
  `close_time` TIME NULL,
  `is_closed` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unit_day` (`unit_id`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Serviços Oferecidos
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `duration_minutes` INT NOT NULL COMMENT 'Duração em minutos',
  `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Preço do serviço',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Ligação: Serviços Disponíveis por Unidade
CREATE TABLE IF NOT EXISTS `unit_services` (
  `unit_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  PRIMARY KEY (`unit_id`, `service_id`),
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Usuários (Clientes, Operadores, Admins)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `address` TEXT NULL COMMENT 'Endereço do cliente',
  `cpf` VARCHAR(14) NULL UNIQUE COMMENT 'CPF do cliente',
  `role` ENUM('admin', 'operator', 'customer') DEFAULT 'customer',
  `unit_id` INT NULL COMMENT 'Unidade associada (para operadores)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Veículos dos Clientes
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL COMMENT 'Dono do veículo',
  `model` VARCHAR(100) NOT NULL,
  `year` INT NULL,
  `color` VARCHAR(50) NULL,
  `plate` VARCHAR(10) NOT NULL UNIQUE COMMENT 'Placa',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Agendamentos
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `unit_id` INT NOT NULL,
  `user_id` INT NOT NULL COMMENT 'Cliente que agendou',
  `vehicle_id` INT NOT NULL COMMENT 'Veículo agendado',
  `service_id` INT NOT NULL COMMENT 'Serviço agendado',
  `operator_id` INT NULL COMMENT 'Operador que realizou (pode ser atribuído)',
  `start_datetime` DATETIME NOT NULL COMMENT 'Data e hora de início',
  `end_datetime` DATETIME NOT NULL COMMENT 'Data e hora de término (calculado)',
  `status` ENUM('scheduled', 'in_progress', 'completed', 'paid', 'cancelled') DEFAULT 'scheduled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`operator_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_appointment_unit_start` (`unit_id`, `start_datetime`),
  INDEX `idx_appointment_user` (`user_id`),
  INDEX `idx_appointment_operator` (`operator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar restrições ou índices adicionais conforme necessário

