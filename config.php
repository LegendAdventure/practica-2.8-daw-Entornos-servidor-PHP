<?php
// Archivo de configuración de la aplicación.
// En esta práctica se puede compartir en el repositorio.
// En un proyecto real se añadiría a .gitignore y cada entorno tendría el suyo.
//
// IMPORTANTE: estos valores deben coincidir con los del docker-compose.yml.
//   - 'host'   -> nombre del servicio de MySQL en docker-compose.yml ("db")
//   - 'dbname' -> MYSQL_DATABASE
//   - 'user'   -> MYSQL_USER
//   - 'pass'   -> MYSQL_PASSWORD
return [
    'db' => [
        'host'    => 'db',
        'dbname'  => 'practica',
        'user'    => 'dev',
        'pass'    => 'dev',
        'charset' => 'utf8mb4',
    ],
];