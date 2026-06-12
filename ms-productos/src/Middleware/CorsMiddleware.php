<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Responder preflight OPTIONS sin llegar a los controladores
        if ($request->getMethod() === 'OPTIONS') {
            return $this->addHeaders(new SlimResponse());
        }

        return $this->addHeaders($handler->handle($request));
    }

    private function addHeaders(Response $response): Response
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin',  '*')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
    }
}
