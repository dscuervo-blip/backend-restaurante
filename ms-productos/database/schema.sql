-- Base de datos: db_productos
CREATE DATABASE IF NOT EXISTS db_productos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_productos;

-- ── Tabla categorias ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categorias (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100)    NOT NULL,
    descripcion TEXT            NULL,
    created_at  TIMESTAMP       NULL DEFAULT NULL,
    updated_at  TIMESTAMP       NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla productos ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
    id           INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(150)    NOT NULL,
    descripcion  TEXT            NULL,
    precio       DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    stock        INT UNSIGNED    NOT NULL DEFAULT 0,
    imagen       VARCHAR(500)    NULL,
    disponible   TINYINT(1)      NOT NULL DEFAULT 1,
    categoria_id INT UNSIGNED    NOT NULL,
    created_at   TIMESTAMP       NULL DEFAULT NULL,
    updated_at   TIMESTAMP       NULL DEFAULT NULL,
    CONSTRAINT fk_producto_categoria
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Datos de ejemplo ──────────────────────────────────────────────────────────
INSERT INTO categorias (nombre, descripcion, created_at, updated_at) VALUES
('Entradas',   'Platos para comenzar',              NOW(), NOW()),
('Platos fuertes', 'Platos principales del menú',   NOW(), NOW()),
('Bebidas',    'Bebidas frías y calientes',          NOW(), NOW()),
('Postres',    'Dulces y postres de la casa',        NOW(), NOW());

INSERT INTO productos (nombre, descripcion, precio, stock, imagen, disponible, categoria_id, created_at, updated_at) VALUES
('Ceviche de camarón', 'Camarones frescos con limón y cilantro', 28000.00, 20, NULL, 1, 1, NOW(), NOW()),
('Bandeja paisa',      'Fríjoles, chicharrón, huevo, arroz',    38000.00, 15, NULL, 1, 2, NOW(), NOW()),
('Limonada de coco',   'Limonada natural con leche de coco',    12000.00, 50, NULL, 1, 3, NOW(), NOW()),
('Tres leches',        'Pastel húmedo con tres tipos de leche', 15000.00, 10, NULL, 1, 4, NOW(), NOW());
