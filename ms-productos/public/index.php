<?php

declare(strict_types=1);

// ── Autoloader (vendor está un nivel arriba de public/) ───────────────────────
require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Middleware\CorsMiddleware;
use App\Middleware\JsonMiddleware;
use App\Routes\Routes;
use Slim\Factory\AppFactory;

// ── Variables de entorno (.env en la raíz del proyecto) ───────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// ── Conexión a la base de datos mediante Eloquent ─────────────────────────────
Database::initialize();

// ── Crear la aplicación Slim ──────────────────────────────────────────────────
$app = AppFactory::create();

// Base path para XAMPP (Apache sirve desde htdocs/ms-productos/public/)
// Descomentar si se accede como http://localhost/ms-productos/public/
// $app->setBasePath('/ms-productos/public');

// ── Middleware (se aplican en orden inverso: último registrado = primero activo)
$app->add(new CorsMiddleware());   // 1.° CORS + preflight OPTIONS
$app->add(new JsonMiddleware());   // 2.° Parsea JSON body, fuerza Content-Type

// ── Manejo de errores (siempre en JSON) ───────────────────────────────────────
$debug           = filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN);
$errorMiddleware = $app->addErrorMiddleware($debug, true, true);
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

// ── Registrar rutas desde la clase Routes ─────────────────────────────────────
Routes::register($app);

$app->run();
