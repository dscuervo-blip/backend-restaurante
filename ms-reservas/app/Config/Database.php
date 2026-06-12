<?php

declare(strict_types=1);

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        $capsule = new Capsule();

        $capsule->addConnection([
            'driver'    => $_ENV['DB_DRIVER']   ?? 'mysql',
            'host'      => $_ENV['DB_HOST']     ?? '127.0.0.1',
            'port'      => $_ENV['DB_PORT']     ?? '3306',
            'database'  => $_ENV['DB_NAME']     ?? 'ms_reservas',
            'username'  => $_ENV['DB_USER']     ?? 'root',
            'password'  => $_ENV['DB_PASS']     ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        self::$booted = true;
    }
}
