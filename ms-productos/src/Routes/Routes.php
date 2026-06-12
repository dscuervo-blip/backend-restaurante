<?php

declare(strict_types=1);

namespace App\Routes;

use App\Controllers\CategoriaController;
use App\Controllers\ProductoController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Routes
{
    /**
     * Registra todas las rutas del microservicio en la aplicación Slim.
     *
     * Prefijo /api para separar recursos REST de cualquier otro endpoint futuro.
     */
    public static function register(App $app): void
    {
        $app->group('/api', function (RouteCollectorProxy $group): void {

            // ── Categorías ────────────────────────────────────────────────────
            $group->get('/categorias',         [CategoriaController::class, 'index']);
            $group->get('/categorias/{id}',    [CategoriaController::class, 'show']);
            $group->post('/categorias',        [CategoriaController::class, 'store']);
            $group->put('/categorias/{id}',    [CategoriaController::class, 'update']);
            $group->delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

            // ── Productos ─────────────────────────────────────────────────────
            $group->get('/productos',                        [ProductoController::class, 'index']);
            $group->get('/productos/{id}',                   [ProductoController::class, 'show']);
            $group->post('/productos',                       [ProductoController::class, 'store']);
            $group->put('/productos/{id}',                   [ProductoController::class, 'update']);
            $group->delete('/productos/{id}',                [ProductoController::class, 'destroy']);
            $group->patch('/productos/{id}/disponibilidad',  [ProductoController::class, 'toggleDisponibilidad']);
        });
    }
}
