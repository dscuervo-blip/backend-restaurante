CREATE DATABASE IF NOT EXISTS ms_reservas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ms_reservas;

CREATE TABLE IF NOT EXISTS mesas (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero      INT UNSIGNED NOT NULL UNIQUE,
    capacidad   INT UNSIGNED NOT NULL,
    estado      ENUM('disponible', 'ocupada', 'reservada') NOT NULL DEFAULT 'disponible',
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservas (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mesa_id          INT UNSIGNED NOT NULL,
    cliente_nombre   VARCHAR(150) NOT NULL,
    cliente_email    VARCHAR(150) NOT NULL,
    fecha_reserva    DATETIME NOT NULL,
    num_personas     INT UNSIGNED NOT NULL,
    estado           ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    notas            TEXT NULL,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    CONSTRAINT fk_reservas_mesa FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
