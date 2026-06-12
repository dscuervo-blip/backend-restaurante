# ms-pedidos

Microservicio REST de gestión de pedidos para sistema de restaurante.
Construido con PHP 8, Slim Framework 4 y Eloquent ORM.

Solo backend — todas las respuestas son JSON.

---

## Tecnologías

| Tecnología | Versión |
|---|---|
| PHP | ^8.0 |
| Slim Framework | ^4.11 |
| Eloquent ORM (illuminate/database) | ^10.0 |
| vlucas/phpdotenv | ^5.5 |
| MySQL | 5.7+ / 8.0+ |

---

## Estructura del proyecto

```
ms-pedidos/
├── app/
│   ├── Config/
│   │   └── Database.php                  ← Inicializa Eloquent ORM
│   ├── Controllers/
│   │   └── PedidoController.php          ← CRUD completo
│   ├── Middleware/
│   │   ├── AuthMiddleware.php             ← Validación Bearer token
│   │   └── JsonBodyParserMiddleware.php   ← Deserializa body JSON
│   ├── Models/
│   │   ├── Pedido.php
│   │   └── DetallePedido.php
│   └── Routes/
│       └── routes.php
├── public/
│   └── index.php                         ← Entry point de Slim
├── composer.json
├── .env
└── README.md
```

---

## Instalación

```bash
# 1. Instalar dependencias
composer install

# 2. Configurar .env
copy .env.example .env
```

`.env`:

```env
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=ms_pedidos
DB_USER=root
DB_PASS=
DEBUG=true
API_SECRET=ms-pedidos-secret-2024
```

---

## Base de datos — SQL

```sql
-- La base de datos ya debe existir
USE ms_pedidos;

CREATE TABLE pedidos (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido  VARCHAR(25)   NOT NULL UNIQUE,
    mesa_id        INT           NOT NULL,
    fecha          DATETIME      NOT NULL,
    total          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado         ENUM(
                       'pendiente',
                       'en_preparacion',
                       'entregado',
                       'pagado',
                       'cancelado'
                   ) NOT NULL DEFAULT 'pendiente',
    created_at     TIMESTAMP NULL DEFAULT NULL,
    updated_at     TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE detalles_pedidos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id       INT           NOT NULL,
    nombre_producto VARCHAR(150)  NOT NULL,
    cantidad        INT           NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal        DECIMAL(10,2) NOT NULL,
    created_at      TIMESTAMP NULL DEFAULT NULL,
    updated_at      TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_detalle_pedido
        FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id)
        ON DELETE CASCADE
);
```

> El campo `nombre_producto` se almacena directamente desde el frontend.
> Este microservicio NO consulta ms-productos ni ningún otro servicio.

---

## Rutas REST

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/pedidos` | Lista todos los pedidos |
| GET | `/pedidos/{id}` | Obtiene un pedido por ID |
| POST | `/pedidos` | Crea un pedido nuevo |
| PUT | `/pedidos/{id}` | Actualiza pedido completo |
| PATCH | `/pedidos/{id}/estado` | Cambia solo el estado |
| GET | `/pedidos/{id}/detalle` | Pedido con detalles completos |

---

## Autenticación

Todas las rutas requieren Bearer token:

```
Authorization: Bearer ms-pedidos-secret-2024
```

---

## Ejemplos Postman

### Headers requeridos en todas las peticiones

```
Content-Type:  application/json
Authorization: Bearer ms-pedidos-secret-2024
```

---

### GET /pedidos

```
GET http://localhost/ms-pedidos/public/pedidos
```

Respuesta `200`:

```json
{
  "data": [
    {
      "id": 1,
      "numero_pedido": "PED-20241001-0001",
      "mesa_id": 3,
      "fecha": "2024-10-01T12:30:00.000000Z",
      "total": 38500,
      "estado": "pendiente",
      "created_at": "2024-10-01T12:30:00.000000Z",
      "updated_at": "2024-10-01T12:30:00.000000Z"
    }
  ],
  "total": 1
}
```

---

### GET /pedidos/{id}

```
GET http://localhost/ms-pedidos/public/pedidos/1
```

Respuesta `200`:

```json
{
  "data": {
    "id": 1,
    "numero_pedido": "PED-20241001-0001",
    "mesa_id": 3,
    "fecha": "2024-10-01T12:30:00.000000Z",
    "total": 38500,
    "estado": "pendiente"
  }
}
```

---

### GET /pedidos/{id}/detalle

```
GET http://localhost/ms-pedidos/public/pedidos/1/detalle
```

Respuesta `200`:

```json
{
  "data": {
    "id": 1,
    "numero_pedido": "PED-20241001-0001",
    "mesa_id": 3,
    "total": 38500,
    "estado": "pendiente",
    "detalles": [
      {
        "id": 1,
        "pedido_id": 1,
        "nombre_producto": "Hamburguesa clásica",
        "cantidad": 2,
        "precio_unitario": 15000,
        "subtotal": 30000
      },
      {
        "id": 2,
        "pedido_id": 1,
        "nombre_producto": "Jugo de naranja",
        "cantidad": 1,
        "precio_unitario": 8500,
        "subtotal": 8500
      }
    ]
  }
}
```

---

### POST /pedidos

```
POST http://localhost/ms-pedidos/public/pedidos
```

Body:

```json
{
  "mesa_id": 3,
  "detalles": [
    {
      "nombre_producto": "Hamburguesa clásica",
      "cantidad": 2,
      "precio_unitario": 15000
    },
    {
      "nombre_producto": "Jugo de naranja",
      "cantidad": 1,
      "precio_unitario": 8500
    }
  ]
}
```

Respuesta `201`:

```json
{
  "data": {
    "id": 1,
    "numero_pedido": "PED-20241001-0001",
    "mesa_id": 3,
    "total": 38500,
    "estado": "pendiente",
    "detalles": [...]
  }
}
```

---

### PUT /pedidos/{id}

```
PUT http://localhost/ms-pedidos/public/pedidos/1
```

Body (reemplaza detalles completos):

```json
{
  "mesa_id": 5,
  "detalles": [
    {
      "nombre_producto": "Pizza margarita",
      "cantidad": 1,
      "precio_unitario": 32000
    }
  ]
}
```

---

### PATCH /pedidos/{id}/estado

```
PATCH http://localhost/ms-pedidos/public/pedidos/1/estado
```

Body:

```json
{
  "estado": "en_preparacion"
}
```

Estados válidos:

| Estado | Descripción |
|---|---|
| `pendiente` | Pedido recibido |
| `en_preparacion` | Cocina en proceso |
| `entregado` | Llevado a la mesa |
| `pagado` | Cuenta cancelada |
| `cancelado` | Pedido anulado |

Respuesta `422` si el estado es inválido:

```json
{
  "error": "Estado no válido",
  "estados_validos": ["pendiente", "en_preparacion", "entregado", "pagado", "cancelado"]
}
```

---

## Notas de diseño

- `numero_pedido` se genera automáticamente con formato `PED-YYYYMMDD-XXXX` (secuencial diario).
- `total` se calcula automáticamente sumando los subtotales de cada detalle.
- `subtotal` = `cantidad × precio_unitario`, calculado en el servidor.
- El microservicio es autónomo: no depende de ningún otro servicio externo.
