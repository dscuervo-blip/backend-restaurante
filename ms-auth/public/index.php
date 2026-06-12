<?php
declare(strict_types=1);

use App\Config\Database;
use App\Middleware\CorsMiddleware;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Inicializar Eloquent ORM
Database::initialize();

// Crear aplicacion Slim
$app = AppFactory::create();

// Parseo de cuerpo: JSON, form-data, x-www-form-urlencoded
$app->addBodyParsingMiddleware();

// Middleware de routing
$app->addRoutingMiddleware();

// Manejo de errores — todas las respuestas en JSON
$errorMiddleware = $app->addErrorMiddleware(
    displayErrorDetails: true,  // Cambiar a false en produccion
    logErrors: true,
    logErrorDetails: true,
);
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

// Registrar rutas
(require __DIR__ . '/../app/Routes/routes.php')($app);

// CORS — debe agregarse al final para que sea la capa más externa
$app->add(new CorsMiddleware());

$app->run();
