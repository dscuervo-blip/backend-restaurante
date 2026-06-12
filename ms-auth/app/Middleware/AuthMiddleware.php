<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if ($authHeader === '' || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Token de acceso no proporcionado.');
        }

        $token = trim(substr($authHeader, 7));

        if ($token === '') {
            return $this->unauthorized('Token de acceso vacio.');
        }

        $usuario = Usuario::where('token', $token)
            ->where('sesion_activa', true)
            ->where('estado', true)
            ->first();

        if ($usuario === null) {
            return $this->unauthorized('Token invalido o sesion no activa.');
        }

        return $handler->handle(
            $request->withAttribute('usuario', $usuario)
        );
    }

    private function unauthorized(string $mensaje): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(
            json_encode(
                ['success' => false, 'mensaje' => $mensaje],
                JSON_UNESCAPED_UNICODE
            )
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
