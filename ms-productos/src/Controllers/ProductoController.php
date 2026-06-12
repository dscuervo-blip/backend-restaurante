<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoController
{
    public function index(Request $request, Response $response): Response
    {
        $productos = Producto::with('categoria')
            ->get()
            ->map(fn(Producto $p) => $this->serialize($p));

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data'   => $productos,
        ]));

        return $response->withStatus(200);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::with('categoria')->find($args['id']);

        if (!$producto) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Producto no encontrado',
            ]));
            return $response->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data'   => $this->serialize($producto),
        ]));

        return $response->withStatus(200);
    }

    public function store(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();

        $errors = $this->validateStore($body);
        if (!empty($errors)) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Errores de validación',
                'errors'  => $errors,
            ]));
            return $response->withStatus(422);
        }

        $categoriaId = $this->resolveCategoria($body['categoria'] ?? null);
        if ($categoriaId === null) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'La categoría especificada no existe',
            ]));
            return $response->withStatus(422);
        }

        $producto = Producto::create([
            'nombre'       => trim($body['nombre']),
            'descripcion'  => $body['descripcion'] ?? null,
            'precio'       => (float) $body['precio'],
            'stock'        => (int) ($body['stock'] ?? 0),
            'imagen'       => $body['imagen'] ?? null,
            'disponible'   => isset($body['disponible']) ? (bool) $body['disponible'] : true,
            'categoria_id' => $categoriaId,
        ]);

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => 'Producto creado exitosamente',
            'data'    => $this->serialize($producto->load('categoria')),
        ]));

        return $response->withStatus(201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Producto no encontrado',
            ]));
            return $response->withStatus(404);
        }

        $body = (array) $request->getParsedBody();

        if (isset($body['precio']) && !is_numeric($body['precio'])) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'El campo precio debe ser numérico',
            ]));
            return $response->withStatus(422);
        }

        $categoriaId = $producto->categoria_id;
        if (isset($body['categoria'])) {
            $resolved = $this->resolveCategoria($body['categoria']);
            if ($resolved === null) {
                $response->getBody()->write(json_encode([
                    'status'  => 'error',
                    'message' => 'La categoría especificada no existe',
                ]));
                return $response->withStatus(422);
            }
            $categoriaId = $resolved;
        }

        $producto->update([
            'nombre'       => isset($body['nombre']) ? trim($body['nombre']) : $producto->nombre,
            'descripcion'  => array_key_exists('descripcion', $body) ? $body['descripcion'] : $producto->descripcion,
            'precio'       => isset($body['precio']) ? (float) $body['precio'] : $producto->precio,
            'stock'        => isset($body['stock']) ? (int) $body['stock'] : $producto->stock,
            'imagen'       => array_key_exists('imagen', $body) ? $body['imagen'] : $producto->imagen,
            'disponible'   => isset($body['disponible']) ? (bool) $body['disponible'] : $producto->disponible,
            'categoria_id' => $categoriaId,
        ]);

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => 'Producto actualizado exitosamente',
            'data'    => $this->serialize($producto->fresh()->load('categoria')),
        ]));

        return $response->withStatus(200);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Producto no encontrado',
            ]));
            return $response->withStatus(404);
        }

        $producto->delete();

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => 'Producto eliminado exitosamente',
        ]));

        return $response->withStatus(200);
    }

    public function toggleDisponibilidad(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Producto no encontrado',
            ]));
            return $response->withStatus(404);
        }

        $body       = (array) $request->getParsedBody();
        $disponible = isset($body['disponible'])
            ? (bool) $body['disponible']
            : !$producto->disponible;

        $producto->update(['disponible' => $disponible]);

        $response->getBody()->write(json_encode([
            'status'  => 'success',
            'message' => $disponible ? 'Producto habilitado' : 'Producto deshabilitado',
            'data'    => $this->serialize($producto->fresh()->load('categoria')),
        ]));

        return $response->withStatus(200);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function serialize(Producto $p): array
    {
        return [
            'id'           => $p->id,
            'nombre'       => $p->nombre,
            'descripcion'  => $p->descripcion,
            'precio'       => $p->precio,
            'stock'        => $p->stock,
            'imagen'       => $p->imagen,
            'disponible'   => (bool) $p->disponible,
            'categoria_id' => $p->categoria_id,
            'categoria'    => $p->relationLoaded('categoria') ? $p->categoria?->nombre : null,
            'created_at'   => $p->created_at,
            'updated_at'   => $p->updated_at,
        ];
    }

    private function validateStore(array $body): array
    {
        $errors = [];

        if (empty($body['nombre'])) {
            $errors[] = 'El campo nombre es requerido';
        }

        if (!isset($body['precio']) || !is_numeric($body['precio'])) {
            $errors[] = 'El campo precio es requerido y debe ser numérico';
        }

        if (empty($body['categoria'])) {
            $errors[] = 'El campo categoria es requerido';
        }

        return $errors;
    }

    /**
     * Resuelve categoria a un ID.
     * - Si el valor es numérico: busca la categoría por ID.
     * - Si es texto: crea la categoría si no existe (firstOrCreate).
     */
    private function resolveCategoria(mixed $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return Categoria::find((int) $value)?->id;
        }

        return Categoria::firstOrCreate(['nombre' => trim((string) $value)])->id;
    }
}
