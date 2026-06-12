<?php

declare(strict_types=1);

use App\Controllers\MesaController;
use App\Controllers\ReservaController;
use Slim\App;

return function (App $app): void {

    $app->options('/{routes:.+}', function ($request, $response) {
        return $response;
    });

    $app->group('/api', function (\Slim\Routing\RouteCollectorProxy $group) {
        // ── Mesas ──────────────────────────────────────────────────────────────
        $group->get('/mesas', [MesaController::class, 'index']);
        $group->post('/mesas', [MesaController::class, 'store']);
        $group->put('/mesas/{id:[0-9]+}', [MesaController::class, 'update']);
        $group->patch('/mesas/{id:[0-9]+}/estado', [MesaController::class, 'cambiarEstado']);

        // ── Reservas ───────────────────────────────────────────────────────────
        $group->get('/reservas', [ReservaController::class, 'index']);
        $group->post('/reservas', [ReservaController::class, 'store']);
        $group->put('/reservas/{id:[0-9]+}', [ReservaController::class, 'update']);
        $group->delete('/reservas/{id:[0-9]+}', [ReservaController::class, 'destroy']);
    });
};
