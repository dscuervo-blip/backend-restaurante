<?php

declare(strict_types=1);

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\JsonBodyParserMiddleware;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// ── 1. Variables de entorno ───────────────────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// ── 2. Conexión Eloquent ORM ──────────────────────────────────────────────────
Database::initialize();

// ── 3. Crear aplicación Slim ──────────────────────────────────────────────────
$app = AppFactory::create();

// ── 4. Middlewares (orden: último en registrar = primero en ejecutar) ─────────

// Parsea el body JSON → disponible en $request->getParsedBody()
$app->add(new JsonBodyParserMiddleware());

// Valida el Bearer token en cada petición
$app->add(new AuthMiddleware());

// CORS — va después de Auth para ejecutarse primero (capa más externa en Slim 4)
$app->add(new CorsMiddleware());

// Resuelve la ruta antes de ejecutar el handler
$app->addRoutingMiddleware();

// Manejo centralizado de excepciones (debe ser el último en registrarse)
$app->addErrorMiddleware(
    displayErrorDetails: filter_var($_ENV['DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    logErrors: true,
    logErrorDetails: true
);

// ── 5. Rutas ──────────────────────────────────────────────────────────────────
(require __DIR__ . '/../app/Routes/routes.php')($app);

// ── 6. Ejecutar ───────────────────────────────────────────────────────────────
$app->run();
