# backend-restaurante
modificaciones 

CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'empleado') NOT NULL,
    token VARCHAR(255) NULL,
    sesion_activa BOOLEAN DEFAULT FALSE,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO usuarios (
    nombre,
    correo,
    usuario,
    contrasena,
    rol,
    token,
    sesion_activa,
    estado,
    created_at,
    updated_at
)
VALUES
(
    'Administrador General',
    'admin@restaurante.com',
    'admin',
    'admin123',
    'administrador',
    NULL,
    FALSE,
    'activo',
    NOW(),
    NOW()
),
(
    'Empleado Restaurante',
    'empleado@restaurante.com',
    'empleado',
    'empleado123',
    'empleado',
    NULL,
    FALSE,
    'activo',
    NOW(),
    NOW()
);










CREATE TABLE mesas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL UNIQUE,
    capacidad INT NOT NULL,
    estado ENUM(
        'disponible',
        'reservada',
        'ocupada',
        'fuera_servicio'
    ) DEFAULT 'disponible',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE reservas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(150) NOT NULL,
    telefono_cliente VARCHAR(30) NOT NULL,
    cantidad_personas INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    observaciones TEXT NULL,
    estado ENUM(
        'pendiente',
        'confirmada',
        'cancelada',
        'finalizada'
    ) DEFAULT 'pendiente',

    mesa_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_reservas_mesas
        FOREIGN KEY (mesa_id)
        REFERENCES mesas(id)
);

INSERT INTO mesas (
    numero,
    capacidad,
    estado,
    created_at,
    updated_at
)
VALUES
('MESA-1', 2, 'disponible', NOW(), NOW()),
('MESA-2', 4, 'disponible', NOW(), NOW()),
('MESA-3', 6, 'disponible', NOW(), NOW()),
('MESA-4', 8, 'disponible', NOW(), NOW());

INSERT INTO reservas (
    nombre_cliente,
    telefono_cliente,
    cantidad_personas,
    fecha,
    hora,
    observaciones,
    estado,
    mesa_id,
    created_at,
    updated_at
)
VALUES
(
    'Carlos Ramirez',
    '3001234567',
    4,
    '2026-06-10',
    '19:00:00',
    'Reserva familiar',
    'confirmada',
    2,
    NOW(),
    NOW()
);












CREATE TABLE categorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE productos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(10,2) NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,

    categoria_id BIGINT UNSIGNED NOT NULL,

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,

    CONSTRAINT fk_productos_categorias
        FOREIGN KEY (categoria_id)
        REFERENCES categorias(id)
);

INSERT INTO categorias (
    nombre,
    descripcion,
    created_at,
    updated_at
)
VALUES
('Entradas', 'Productos de entrada', NOW(), NOW()),
('Bebidas', 'Bebidas frías y calientes', NOW(), NOW()),
('Platos fuertes', 'Platos principales', NOW(), NOW()),
('Postres', 'Productos dulces', NOW(), NOW());

INSERT INTO productos (
    nombre,
    descripcion,
    precio,
    disponible,
    categoria_id,
    created_at,
    updated_at
)
VALUES
(
    'Hamburguesa Especial',
    'Hamburguesa con queso y tocineta',
    28000,
    TRUE,
    3,
    NOW(),
    NOW()
),
(
    'Limonada Natural',
    'Bebida natural de limón',
    8000,
    TRUE,
    2,
    NOW(),
    NOW()
),
(
    'Cheesecake',
    'Postre de queso',
    12000,
    TRUE,
    4,
    NOW(),
    NOW()
);












