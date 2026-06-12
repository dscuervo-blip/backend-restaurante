<?php

declare(strict_types=1);

use App\Config\Database;
use App\Middleware\CorsMiddleware;
use App\Middleware\JsonMiddleware;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

// ── Variables de entorno (en producción usa un paquete como vlucas/phpdotenv) ──
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_NAME'] = 'ms_reservas';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

// ── Inicializar Eloquent ────────────────────────────────────────────────────────
Database::boot();

// ── Crear aplicación Slim ───────────────────────────────────────────────────────
$app = AppFactory::create();

// ── Middleware global ───────────────────────────────────────────────────────────
$app->addRoutingMiddleware();
$app->add(JsonMiddleware::class);
$app->add(new CorsMiddleware());

// Manejo de errores — en producción cambiar los booleanos a false
$errorMiddleware = $app->addErrorMiddleware(
    displayErrorDetails: true,
    logErrors:           true,
    logErrorDetails:     true,
);

// Formateador de errores en JSON
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

// ── Cargar rutas ────────────────────────────────────────────────────────────────
$routes = require __DIR__ . '/../app/Routes/routes.php';
$routes($app);

// ── Ejecutar ────────────────────────────────────────────────────────────────────
$app->run();
