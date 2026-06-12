<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware implements MiddlewareInterface
{
    private string $authValidateUrl;

    public function __construct(string $authValidateUrl = 'http://localhost:8001/validate')
    {
        $this->authValidateUrl = $authValidateUrl;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Token de autorización requerido.');
        }

        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => "Authorization: {$authHeader}\r\nContent-Type: application/json\r\n",
                'timeout' => 3,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($this->authValidateUrl, false, $context);

        if ($body === false) {
            return $this->unauthorized('No se pudo contactar el servicio de autenticación.');
        }

        $data = json_decode($body, true);

        if (empty($data['success'])) {
            return $this->unauthorized('Token inválido o sesión expirada.');
        }

        return $handler->handle($request);
    }

    private function unauthorized(string $mensaje): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error'  => $mensaje,
            'status' => 401,
        ], JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
