-- ============================================================
-- Base de datos: db_auth
-- Microservicio: ms-auth
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_auth
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_auth;

CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(100)  NOT NULL,
    correo        VARCHAR(150)  NOT NULL,
    usuario       VARCHAR(50)   NOT NULL,
    contrasena    VARCHAR(255)  NOT NULL,
    rol           VARCHAR(30)   NOT NULL DEFAULT 'cliente',
    token         VARCHAR(64)   NULL,
    sesion_activa TINYINT(1)    NOT NULL DEFAULT 0,
    estado        TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    TIMESTAMP     NULL,
    updated_at    TIMESTAMP     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_correo  (correo),
    UNIQUE KEY uq_usuario (usuario),
    KEY idx_token         (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Usuario de prueba
-- Generar hash con PHP: password_hash('Admin123!', PASSWORD_BCRYPT)
-- Ejecutar en terminal: php -r "echo password_hash('Admin123!', PASSWORD_BCRYPT);"
-- ============================================================
INSERT INTO usuarios (nombre, correo, usuario, contrasena, rol, estado)
VALUES (
    'Administrador',
    'admin@restaurante.com',
    'admin',
    '$2y$12$REEMPLAZAR_CON_HASH_GENERADO',
    'admin',
    1
);
