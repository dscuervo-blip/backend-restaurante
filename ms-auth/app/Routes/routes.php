<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use Slim\App;

return static function (App $app): void {

    // Ruta publica: no requiere autenticacion
    $app->post('/login', [AuthController::class, 'login']);

    // Rutas protegidas: requieren Bearer token valido
    $app->group('', static function ($group): void {
        $group->post('/logout',  [AuthController::class, 'logout']);
        $group->get('/validate', [AuthController::class, 'validate']);
        $group->get('/usuario',  [AuthController::class, 'usuario']);
    })->add(AuthMiddleware::class);
};
