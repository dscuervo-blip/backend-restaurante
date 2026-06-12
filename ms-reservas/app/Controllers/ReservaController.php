<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservaController
{
    // GET /reservas
    public function index(Request $request, Response $response): Response
    {
        $query  = Reserva::with('mesa');
        $params = $request->getQueryParams();

        if (!empty($params['mesa_id'])) {
            $query->where('mesa_id', (int) $params['mesa_id']);
        }

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        if (!empty($params['fecha'])) {
            $query->where('fecha', $params['fecha']);
        }

        $reservas = $query->orderBy('fecha')->orderBy('hora')->get();

        return $this->json($response, $reservas->toArray());
    }

    // POST /reservas
    public function store(Request $request, Response $response): Response
    {
        $data = $this->parseBody($request);

        $errors = $this->validateReserva($data);
        if ($errors) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        $mesa = Mesa::find($data['mesa_id']);
        if (!$mesa) {
            return $this->json($response, ['error' => 'Mesa no encontrada.'], 404);
        }

        if (!$mesa->estaDisponible()) {
            return $this->json($response, [
                'error' => "La mesa {$mesa->numero} no está disponible (estado actual: {$mesa->estado}).",
            ], 409);
        }

        if ((int)$data['cantidad_personas'] > $mesa->capacidad) {
            return $this->json($response, [
                'error' => "La mesa {$mesa->numero} tiene capacidad máxima de {$mesa->capacidad} personas.",
            ], 422);
        }

        $reserva = Reserva::create([
            'nombre_cliente'    => trim($data['nombre_cliente']),
            'telefono_cliente'  => trim($data['telefono_cliente']),
            'cantidad_personas' => (int) $data['cantidad_personas'],
            'fecha'             => $data['fecha'],
            'hora'              => $data['hora'],
            'observaciones'     => $data['observaciones'] ?? null,
            'estado'            => $data['estado'] ?? 'pendiente',
            'mesa_id'           => (int) $data['mesa_id'],
        ]);

        // Marcar mesa como reservada
        $mesa->update(['estado' => 'reservada']);

        return $this->json($response, $reserva->load('mesa')->toArray(), 201);
    }

    // PUT /reservas/{id}
    public function update(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::find($args['id']);
        if (!$reserva) {
            return $this->json($response, ['error' => 'Reserva no encontrada.'], 404);
        }

        $data = $this->parseBody($request);

        $errors = $this->validateReserva($data);
        if ($errors) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        $nuevaMesaId = (int) $data['mesa_id'];

        // Si cambia la mesa, verificar disponibilidad de la nueva
        if ($nuevaMesaId !== $reserva->mesa_id) {
            $nuevaMesa = Mesa::find($nuevaMesaId);
            if (!$nuevaMesa) {
                return $this->json($response, ['error' => 'Mesa no encontrada.'], 404);
            }
            if (!$nuevaMesa->estaDisponible()) {
                return $this->json($response, [
                    'error' => "La mesa {$nuevaMesa->numero} no está disponible (estado: {$nuevaMesa->estado}).",
                ], 409);
            }

            // Liberar la mesa anterior si ya no tiene reservas activas
            $this->liberarMesaSiProcede($reserva->mesa_id, $reserva->id);

            $nuevaMesa->update(['estado' => 'reservada']);
        }

        $reserva->update([
            'nombre_cliente'    => trim($data['nombre_cliente']),
            'telefono_cliente'  => trim($data['telefono_cliente']),
            'cantidad_personas' => (int) $data['cantidad_personas'],
            'fecha'             => $data['fecha'],
            'hora'              => $data['hora'],
            'observaciones'     => $data['observaciones'] ?? $reserva->observaciones,
            'estado'            => $data['estado'] ?? $reserva->estado,
            'mesa_id'           => $nuevaMesaId,
        ]);

        return $this->json($response, $reserva->fresh()->load('mesa')->toArray());
    }

    // DELETE /reservas/{id}
    public function destroy(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::find($args['id']);
        if (!$reserva) {
            return $this->json($response, ['error' => 'Reserva no encontrada.'], 404);
        }

        $mesaId    = $reserva->mesa_id;
        $reservaId = $reserva->id;

        $reserva->delete();

        $this->liberarMesaSiProcede($mesaId, $reservaId);

        return $this->json($response, ['message' => 'Reserva eliminada correctamente.']);
    }

    // ── Helpers privados ────────────────────────────────────────────────────────

    private function liberarMesaSiProcede(int $mesaId, int $excludeReservaId): void
    {
        $tieneActivas = Reserva::where('mesa_id', $mesaId)
            ->where('id', '!=', $excludeReservaId)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->exists();

        if (!$tieneActivas) {
            Mesa::where('id', $mesaId)->update(['estado' => 'disponible']);
        }
    }

    private function validateReserva(array $data): array
    {
        $errors = [];

        if (empty($data['mesa_id']) || !is_numeric($data['mesa_id'])) {
            $errors['mesa_id'] = 'El ID de mesa es obligatorio.';
        }

        if (empty($data['nombre_cliente']) || trim($data['nombre_cliente']) === '') {
            $errors['nombre_cliente'] = 'El nombre del cliente es obligatorio.';
        }

        if (empty($data['telefono_cliente']) || trim($data['telefono_cliente']) === '') {
            $errors['telefono_cliente'] = 'El teléfono del cliente es obligatorio.';
        }

        if (!isset($data['cantidad_personas']) || !is_numeric($data['cantidad_personas']) || (int)$data['cantidad_personas'] <= 0) {
            $errors['cantidad_personas'] = 'La cantidad de personas es obligatoria y debe ser mayor a 0.';
        }

        if (empty($data['fecha'])) {
            $errors['fecha'] = 'La fecha es obligatoria (formato: YYYY-MM-DD).';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha'])) {
            $errors['fecha'] = 'Formato de fecha inválido, use YYYY-MM-DD.';
        }

        if (empty($data['hora'])) {
            $errors['hora'] = 'La hora es obligatoria (formato: HH:MM o HH:MM:SS).';
        } elseif (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $data['hora'])) {
            $errors['hora'] = 'Formato de hora inválido, use HH:MM o HH:MM:SS.';
        }

        if (isset($data['estado']) && !in_array($data['estado'], Reserva::ESTADOS, true)) {
            $errors['estado'] = 'Estado inválido. Permitidos: ' . implode(', ', Reserva::ESTADOS);
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
