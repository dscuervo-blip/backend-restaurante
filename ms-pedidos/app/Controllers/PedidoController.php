<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Pedido;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PedidoController
{
    private const ESTADOS_VALIDOS = [
        'pendiente',
        'en_preparacion',
        'entregado',
        'pagado',
        'cancelado',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // GET /pedidos
    // ──────────────────────────────────────────────────────────────────────────
    public function index(Request $request, Response $response): Response
    {
        $pedidos = Pedido::orderBy('created_at', 'desc')->get();

        return $this->json($response, [
            'data'  => $pedidos->toArray(),
            'total' => $pedidos->count(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /pedidos/{id}
    // ──────────────────────────────────────────────────────────────────────────
    public function show(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find((int) $args['id']);

        if (!$pedido) {
            return $this->json($response, ['error' => 'Pedido no encontrado'], 404);
        }

        return $this->json($response, ['data' => $pedido->toArray()]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /pedidos/{id}/detalle
    // ──────────────────────────────────────────────────────────────────────────
    public function detalle(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::with('detalles')->find((int) $args['id']);

        if (!$pedido) {
            return $this->json($response, ['error' => 'Pedido no encontrado'], 404);
        }

        return $this->json($response, ['data' => $pedido->toArray()]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /pedidos
    // El frontend envía nombre_producto y precio_unitario directamente.
    // Este microservicio no consulta ms-productos.
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

        if (empty($data['mesa_id'])) {
            return $this->json($response, ['error' => 'El campo mesa_id es requerido'], 422);
        }

        if (empty($data['detalles']) || !is_array($data['detalles'])) {
            return $this->json($response, ['error' => 'Debe incluir al menos un detalle'], 422);
        }

        $pedido = Pedido::create([
            'numero_pedido' => $this->generarNumeroPedido(),
            'mesa_id'       => (int) $data['mesa_id'],
            'fecha'         => $data['fecha'] ?? date('Y-m-d H:i:s'),
            'total'         => 0.00,
            'estado'        => 'pendiente',
        ]);

        $total = 0.0;

        foreach ($data['detalles'] as $item) {
            if (empty($item['nombre_producto']) || empty($item['cantidad']) || !isset($item['precio_unitario'])) {
                $pedido->delete();
                return $this->json($response, [
                    'error' => 'Cada detalle requiere: nombre_producto, cantidad, precio_unitario',
                ], 422);
            }

            $subtotal = (float) $item['cantidad'] * (float) $item['precio_unitario'];
            $total   += $subtotal;

            $pedido->detalles()->create([
                'nombre_producto' => trim($item['nombre_producto']),
                'cantidad'        => (int)   $item['cantidad'],
                'precio_unitario' => (float) $item['precio_unitario'],
                'subtotal'        => $subtotal,
            ]);
        }

        $pedido->update(['total' => $total]);
        $pedido->load('detalles');

        return $this->json($response, ['data' => $pedido->toArray()], 201);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUT /pedidos/{id}
    // ──────────────────────────────────────────────────────────────────────────
    public function update(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find((int) $args['id']);

        if (!$pedido) {
            return $this->json($response, ['error' => 'Pedido no encontrado'], 404);
        }

        $data = (array) $request->getParsedBody();

        $pedido->update([
            'mesa_id' => $data['mesa_id'] ?? $pedido->mesa_id,
            'fecha'   => $data['fecha']   ?? $pedido->fecha,
            'estado'  => $data['estado']  ?? $pedido->estado,
        ]);

        // Si vienen detalles, reemplaza todos los existentes
        if (isset($data['detalles']) && is_array($data['detalles'])) {
            $pedido->detalles()->delete();
            $total = 0.0;

            foreach ($data['detalles'] as $item) {
                $subtotal = (float) $item['cantidad'] * (float) $item['precio_unitario'];
                $total   += $subtotal;

                $pedido->detalles()->create([
                    'nombre_producto' => trim($item['nombre_producto']),
                    'cantidad'        => (int)   $item['cantidad'],
                    'precio_unitario' => (float) $item['precio_unitario'],
                    'subtotal'        => $subtotal,
                ]);
            }

            $pedido->update(['total' => $total]);
        }

        $pedido->load('detalles');

        return $this->json($response, ['data' => $pedido->toArray()]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PATCH /pedidos/{id}/estado
    // ──────────────────────────────────────────────────────────────────────────
    public function updateEstado(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find((int) $args['id']);

        if (!$pedido) {
            return $this->json($response, ['error' => 'Pedido no encontrado'], 404);
        }

        $data  = (array) $request->getParsedBody();
        $nuevo = $data['estado'] ?? '';

        if ($nuevo === '') {
            return $this->json($response, ['error' => 'El campo estado es requerido'], 422);
        }

        if (!in_array($nuevo, self::ESTADOS_VALIDOS, true)) {
            return $this->json($response, [
                'error'           => 'Estado no válido',
                'estados_validos' => self::ESTADOS_VALIDOS,
            ], 422);
        }

        $pedido->estado = $nuevo;
        $pedido->save();

        return $this->json($response, ['data' => $pedido->toArray()]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────────────────

    private function generarNumeroPedido(): string
    {
        $fecha      = date('Ymd');
        $cantidad   = Pedido::whereDate('created_at', date('Y-m-d'))->count();
        $secuencial = str_pad((string) ($cantidad + 1), 4, '0', STR_PAD_LEFT);

        return "PED-{$fecha}-{$secuencial}";
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
