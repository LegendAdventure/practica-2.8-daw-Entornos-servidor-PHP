<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User extends BaseModel
{
    public function __construct()
    {
        parent::__construct('users');
    }

    /**
     * Busca un usuario por email y comprueba que la contraseña coincide.
     * Devuelve el id del usuario si todo es correcto, o null si no.
     */
    public function findByEmail(string $email, string $password): ?int
    {
        $user = Database::fetchOne(
            'SELECT * FROM users WHERE email = :email',
            ['email' => $email]
        );

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        return (int) $user['id'];
    }
}