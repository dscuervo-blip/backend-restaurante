<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    // POST /login
    public function login(Request $request, Response $response): Response
    {
        $body = (array) ($request->getParsedBody() ?? []);

        $usuarioInput    = trim((string) ($body['usuario'] ?? ''));
        $contrasenaInput = trim((string) ($body['contrasena'] ?? ''));

        if ($usuarioInput === '' || $contrasenaInput === '') {
            return $this->json($response, [
                'success' => false,
                'mensaje' => 'Los campos usuario y contrasena son obligatorios.',
            ], 400);
        }

        $usuario = Usuario::where('usuario', $usuarioInput)
            ->where('estado', true)
            ->first();

        if ($usuario === null || !password_verify($contrasenaInput, $usuario->contrasena)) {
            return $this->json($response, [
                'success' => false,
                'mensaje' => 'Credenciales invalidas.',
            ], 401);
        }

        if ($usuario->sesion_activa) {
            return $this->json($response, [
                'success' => false,
                'mensaje' => 'Ya existe una sesion activa para este usuario.',
            ], 409);
        }

        $token = bin2hex(random_bytes(32));

        $usuario->token         = $token;
        $usuario->sesion_activa = true;
        $usuario->save();

        return $this->json($response, [
            'success' => true,
            'mensaje' => 'Inicio de sesion exitoso.',
            'token'   => $token,
            'usuario' => [
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'rol'    => $usuario->rol,
            ],
        ]);
    }

    // POST /logout
    public function logout(Request $request, Response $response): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->getAttribute('usuario');

        $usuario->token         = null;
        $usuario->sesion_activa = false;
        $usuario->save();

        return $this->json($response, [
            'success' => true,
            'mensaje' => 'Sesion cerrada correctamente.',
        ]);
    }

    // GET /validate
    public function validate(Request $request, Response $response): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->getAttribute('usuario');

        return $this->json($response, [
            'success'       => true,
            'mensaje'       => 'Token valido. Sesion activa.',
            'sesion_activa' => $usuario->sesion_activa,
            'usuario_id'    => $usuario->id,
        ]);
    }

    // GET /usuario
    public function usuario(Request $request, Response $response): Response
    {
        /** @var Usuario $usuario */
        $usuario = $request->getAttribute('usuario');

        return $this->json($response, [
            'success' => true,
            'usuario' => [
                'id'      => $usuario->id,
                'nombre'  => $usuario->nombre,
                'correo'  => $usuario->correo,
                'usuario' => $usuario->usuario,
                'rol'     => $usuario->rol,
                'estado'  => $usuario->estado,
            ],
        ]);
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
