<?php
// Archivo de configuración de la aplicación.
//
// Lee las variables del archivo .env (situado en la misma carpeta) y
// devuelve un array con la configuración. De esta forma, Database.php
// no necesita saber de dónde salen los valores: solo hace require de
// este archivo y usa $config['db'].
//
// En un proyecto real, tanto .env como config.php se añadirían a
// .gitignore para no versionar credenciales. En esta práctica se
// pueden compartir.

$envPath = __DIR__ . '/.env';

if (!is_readable($envPath)) {
    die('No se encuentra el archivo .env en la raíz del proyecto.');
}

// Parseo mínimo del .env: una línea "CLAVE=valor" por variable.
// Ignora líneas vacías y comentarios que empiezan por "#".
$env = [];

foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}

return [
    'db' => [
        'host'    => $env['DB_HOST']    ?? 'db',
        'dbname'  => $env['DB_NAME']    ?? 'practica',
        'user'    => $env['DB_USER']    ?? 'dev',
        'pass'    => $env['DB_PASS']    ?? 'dev',
        'charset' => $env['DB_CHARSET'] ?? 'utf8mb4',
    ],
];