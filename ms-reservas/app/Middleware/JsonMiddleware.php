<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class JsonMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');

        // Parsear body JSON automáticamente si el Content-Type lo indica
        if (str_contains($contentType, 'application/json')) {
            $raw = (string) $request->getBody();
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $request = $request->withParsedBody($decoded);
                }
            }
        }

        $response = $handler->handle($request);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
