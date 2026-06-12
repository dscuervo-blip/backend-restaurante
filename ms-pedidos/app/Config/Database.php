<?php

declare(strict_types=1);

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    public static function initialize(): void
    {
        $capsule = new Capsule();

        $capsule->addConnection([
            'driver'    => $_ENV['DB_DRIVER']   ?? 'mysql',
            'host'      => $_ENV['DB_HOST']     ?? 'localhost',
            'port'      => $_ENV['DB_PORT']     ?? '3306',
            'database'  => $_ENV['DB_NAME']     ?? 'ms_pedidos',
            'username'  => $_ENV['DB_USER']     ?? 'root',
            'password'  => $_ENV['DB_PASS']     ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        // Hace disponible el Capsule globalmente (Modelo::query(), DB::table(), etc.)
        $capsule->setAsGlobal();

        // Activa los eventos y el query builder de Eloquent
        $capsule->bootEloquent();
    }
}
