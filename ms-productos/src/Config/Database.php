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
            'driver'    => $_ENV['DB_DRIVER']    ?? 'mysql',
            'host'      => $_ENV['DB_HOST']      ?? 'localhost',
            'database'  => $_ENV['DB_NAME']      ?? 'db_productos',
            'username'  => $_ENV['DB_USER']      ?? 'root',
            'password'  => $_ENV['DB_PASS']      ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
