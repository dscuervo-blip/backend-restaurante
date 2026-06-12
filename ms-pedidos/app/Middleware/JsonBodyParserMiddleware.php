<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Deserializa el body JSON de la petición y lo inyecta como parsed body
 * para que $request->getParsedBody() devuelva un array en los controladores.
 */
class JsonBodyParserMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $rawBody = (string) $request->getBody();

            if ($rawBody !== '') {
                $decoded = json_decode($rawBody, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $request = $request->withParsedBody($decoded);
                }
            }
        }

        return $handler->handle($request);
    }
}
