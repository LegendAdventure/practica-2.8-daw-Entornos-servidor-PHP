<?php
// Layout común: cabecera, mensajes flash y apertura del <main>.
// Cada vista hace require de este archivo al principio y de footer.php al final.
// Este archivo NO cierra <main>, </body> ni </html>: eso lo hace footer.php.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Charset UTF-8: soporta tildes, eñes, emojis… -->
    <meta charset="UTF-8">
    <title>Tablón de anuncios</title>
    <!-- Estilos inline (sin CSS externo, para simplificar la práctica). -->
    <style>
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; padding-bottom: 0.5rem; margin-bottom: 1rem; }
        header h1 { margin: 0; font-size: 1.4rem; }
        header h1 a { color: inherit; text-decoration: none; }
        nav a { margin-left: 0.75rem; }
        /* Mensajes flash: error en rojo, éxito en verde. */
        .flash { padding: 0.5rem 1rem; margin: 0.5rem 0; border-radius: 4px; }
        .flash.error   { background: #fdd; color: #900; }
        .flash.success { background: #dfd; color: #060; }
        main { margin-top: 1rem; }
        /* Estilos básicos para los formularios. */
        form label { display: block; margin-top: 0.5rem; }
        form input, form textarea, form select { width: 100%; padding: 0.4rem; box-sizing: border-box; }
        form button { margin-top: 1rem; padding: 0.5rem 1rem; }
    </style>
</head>
<body>
    <header>
        <!-- Título de la web, enlace a la home (/). -->
        <h1><a href="/">Tablón de anuncios</a></h1>
        <nav>
            <?php
            // Navegación según sesión:
            // - Si hay sesión ($_SESSION['user_id']), muestra el email y "Cerrar sesión".
            // - Si no hay sesión, muestra "Iniciar sesión" y "Registrarse".
            ?>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <!-- Email del usuario logueado. htmlspecialchars escapa por si tuviera
                     caracteres raros (no debería, pero por seguridad).
                     ?? '' → si $_SESSION['email'] no existe, cadena vacía. -->
                <span><?= htmlspecialchars($_SESSION['email'] ?? '') ?></span>
                <a href="/logout">Cerrar sesión</a>
            <?php else: ?>
                <a href="/login">Iniciar sesión</a>
                <a href="/register">Registrarse</a>
            <?php endif; ?>
        </nav>
    </header>

    <?php
    // Mensaje flash de error (si existe en la sesión).
    // Se pinta en rojo, escapado con htmlspecialchars, y se borra con unset()
    // para que no se repita en la siguiente petición.
    ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php
    // Mensaje flash de éxito (si existe en la sesión).
    // Igual que el de error, pero en verde. Se borra con unset() tras pintarlo.
    ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Abre <main>. footer.php lo cerrará. El contenido de cada vista va aquí. -->
    <main>