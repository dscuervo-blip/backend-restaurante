<?php

/**
 * Compatibilidad: este archivo es requerido desde index.php (raíz).
 * Las rutas están definidas en App\Routes\Routes::register().
 */

declare(strict_types=1);

use App\Routes\Routes;
use Slim\App;

return function (App $app): void {
    Routes::register($app);
};
