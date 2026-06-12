<?php

declare(strict_types=1);

use App\Controllers\PedidoController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app): void {

    // Captura cualquier ruta OPTIONS para el preflight CORS
    $app->options('/{routes:.+}', function (Request $request, Response $response): Response {
        return $response;
    });

    $app->group('/api', function (\Slim\Routing\RouteCollectorProxy $group) use ($app) {
        $group->get('/pedidos',               [PedidoController::class, 'index']);
        $group->get('/pedidos/{id}',          [PedidoController::class, 'show']);
        $group->get('/pedidos/{id}/detalle',  [PedidoController::class, 'detalle']);
        $group->post('/pedidos',              [PedidoController::class, 'store']);
        $group->put('/pedidos/{id}',          [PedidoController::class, 'update']);
        $group->patch('/pedidos/{id}/estado', [PedidoController::class, 'updateEstado']);
    });
};
