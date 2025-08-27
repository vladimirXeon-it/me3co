<?php

namespace App\Services;

use GroceryCrud\Core\GroceryCrud;

class GcFactory
{
    public static function make(): GroceryCrud
    {
        $default = config('database.default'); // p.ej. 'mysql'
        $c = config("database.connections.$default");

        $map = [
            'mysql'  => 'Pdo_Mysql',
            'pgsql'  => 'Pdo_Pgsql',
            'sqlsrv' => 'Pdo_SqlSrv',
            'sqlite' => 'Pdo_Sqlite',
        ];

        $database = [
            'adapter' => [
                'driver'   => $map[$c['driver']] ?? 'Pdo_Mysql',
                'host'     => $c['host']     ?? null,
                'port'     => $c['port']     ?? null,
                'database' => $c['database'] ?? null,
                'username' => $c['username'] ?? null,
                'password' => $c['password'] ?? null,
                'charset'  => $c['charset']  ?? 'utf8mb4',
            ],
        ];

        $config = config('grocerycrud');
        $crud   = new GroceryCrud($config, $database);

        $crud->setCsrfTokenName('_token');
        $crud->setCsrfTokenValue(csrf_token());

        return $crud;
    }
}