<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Mesa;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaController
{
    // GET /mesas
    public function index(Request $request, Response $response): Response
    {
        $mesas = Mesa::with('reservas')->get();

        return $this->json($response, $mesas->toArray());
    }

    // POST /mesas
    public function store(Request $request, Response $response): Response
    {
        $data = $this->parseBody($request);

        $errors = $this->validateMesa($data);
        if ($errors) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        if (Mesa::where('numero', $data['numero'])->exists()) {
            return $this->json($response, ['error' => "Ya existe una mesa con el número '{$data['numero']}'."], 409);
        }

        $mesa = Mesa::create([
            'numero'    => trim($data['numero']),
            'capacidad' => (int) $data['capacidad'],
            'estado'    => $data['estado'] ?? 'disponible',
        ]);

        return $this->json($response, $mesa->toArray(), 201);
    }

    // PUT /mesas/{id}
    public function update(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);
        if (!$mesa) {
            return $this->json($response, ['error' => 'Mesa no encontrada.'], 404);
        }

        $data = $this->parseBody($request);

        $errors = $this->validateMesa($data);
        if ($errors) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        $duplicado = Mesa::where('numero', $data['numero'])
            ->where('id', '!=', $mesa->id)
            ->exists();

        if ($duplicado) {
            return $this->json($response, ['error' => "Ya existe otra mesa con el número '{$data['numero']}'."], 409);
        }

        $mesa->update([
            'numero'    => trim($data['numero']),
            'capacidad' => (int) $data['capacidad'],
            'estado'    => $data['estado'] ?? $mesa->estado,
        ]);

        return $this->json($response, $mesa->fresh()->toArray());
    }

    // PATCH /mesas/{id}/estado
    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);
        if (!$mesa) {
            return $this->json($response, ['error' => 'Mesa no encontrada.'], 404);
        }

        $data = $this->parseBody($request);

        if (empty($data['estado']) || !in_array($data['estado'], Mesa::ESTADOS, true)) {
            return $this->json($response, [
                'error'    => 'Estado inválido.',
                'permitidos' => Mesa::ESTADOS,
            ], 422);
        }

        $mesa->update(['estado' => $data['estado']]);

        return $this->json($response, $mesa->fresh()->toArray());
    }

    // ── Helpers privados ────────────────────────────────────────────────────────

    private function validateMesa(array $data): array
    {
        $errors = [];

        if (empty($data['numero']) || !is_string($data['numero']) || trim($data['numero']) === '') {
            $errors['numero'] = 'El número de mesa es obligatorio (ej: MESA-1).';
        } elseif (strlen(trim($data['numero'])) > 20) {
            $errors['numero'] = 'El número de mesa no puede superar 20 caracteres.';
        }

        if (!isset($data['capacidad']) || !is_numeric($data['capacidad']) || (int)$data['capacidad'] <= 0) {
            $errors['capacidad'] = 'La capacidad es obligatoria y debe ser un entero positivo.';
        }

        if (isset($data['estado']) && !in_array($data['estado'], Mesa::ESTADOS, true)) {
            $errors['estado'] = 'Estado inválido. Permitidos: ' . implode(', ', Mesa::ESTADOS);
        }

        return $errors;
    }

    private function parseBody(Request $request): array
    {
        $parsed = $request->getParsedBody();
        if (is_array($parsed) && count($parsed) > 0) {
            return $parsed;
        }

        $raw = (string) $request->getBody();
        return json_decode($raw, true) ?? [];
    }

    private function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
