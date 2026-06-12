<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Categoria;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoriaController
{
    public function index(Request $request, Response $response): Response
    {
        $categorias = Categoria::all();

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data'   => $categorias,
        ]));

        return $response->withStatus(200);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $categoria = Categoria::find($args['id']);

        if (!$categoria) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Categoría no encontrada',
            ]));
            return $response->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data'   => $categoria,
        ]));

        return $response->withStatus(200);
    }

    public function store(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        if (empty($body['nombre'])) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'El campo nombre es requerido',
            ]));
            return $response->withStatus(422);
        }

        $categoria = Categoria::create([
            'nombre'      => trim($body['nombre']),
            'descripcion' => $body['descripcion'] ?? null,
        ]);

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => 'Categoría creada exitosamente',
            'data'    => $categoria,
        ]));

        return $response->withStatus(201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $categoria = Categoria::find($args['id']);

        if (!$categoria) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Categoría no encontrada',
            ]));
            return $response->withStatus(404);
        }

        $body = (array) $request->getParsedBody();

        $categoria->update([
            'nombre'      => isset($body['nombre']) ? trim($body['nombre']) : $categoria->nombre,
            'descripcion' => array_key_exists('descripcion', $body) ? $body['descripcion'] : $categoria->descripcion,
        ]);

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => 'Categoría actualizada exitosamente',
            'data'    => $categoria->fresh(),
        ]));

        return $response->withStatus(200);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $categoria = Categoria::find($args['id']);

        if (!$categoria) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Categoría no encontrada',
            ]));
            return $response->withStatus(404);
        }

        $categoria->delete();

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => 'Categoría eliminada exitosamente',
        ]));

        return $response->withStatus(200);
    }
}
