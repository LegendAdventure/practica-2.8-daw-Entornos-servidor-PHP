<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    private function checkCsrfToken(): void
    {
        $token = $_POST['csrf_token'] ?? '';

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Token CSRF inválido. Recarga la página e inténtalo de nuevo.');
        }
    }

    // Mostrar el formulario de registro
    public function showRegisterForm(): void
    {
        require __DIR__ . '/../Views/auth/register.php';
    }

    // Procesar el registro
    public function register(): void
    {
        $this->checkCsrfToken();

        $email    = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $username === '' || $password === '') {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: /register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El email no es válido.';
            header('Location: /register');
            exit;
        }

        $userModel = new User();

        // Comprobar que no existe ya un usuario con ese email
        $existing = \App\Core\Database::fetchOne(
            'SELECT id FROM users WHERE email = :email',
            ['email' => $email]
        );

        if ($existing !== null) {
            $_SESSION['error'] = 'Ya existe un usuario con ese email.';
            header('Location: /register');
            exit;
        }

        $userModel->insert([
            'email'    => $email,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $_SESSION['success'] = 'Registro correcto. Ya puedes iniciar sesión.';
        header('Location: /login');
        exit;
    }

    // Mostrar el formulario de login
    public function showLoginForm(): void
    {
        require __DIR__ . '/../Views/auth/login.php';
    }

    // Procesar el login
    public function login(): void
    {
        $this->checkCsrfToken();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Email y contraseña son obligatorios.';
            header('Location: /login');
            exit;
        }

        $userModel = new User();
        $userId = $userModel->findByEmail($email, $password);

        if ($userId === null) {
            $_SESSION['error'] = 'Credenciales incorrectas.';
            header('Location: /login');
            exit;
        }

        $_SESSION['user_id'] = $userId;
        $_SESSION['email']   = $email;

        header('Location: /');
        exit;
    }

    // Cerrar sesión
    public function logout(): void
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}