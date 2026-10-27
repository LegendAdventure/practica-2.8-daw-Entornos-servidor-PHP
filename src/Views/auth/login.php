<?php
// Incluye la cabecera común (DOCTYPE, <head>, <header> con navegación,
// mensajes flash y apertura de <main>).
// __DIR__ es src/Views/auth; /../layout.php sube a src/Views/layout.php.
require __DIR__ . '/../layout.php';
?>

<h2>Iniciar sesión</h2>

<!-- Formulario de login. method="post" envía los datos por POST (no por GET).
     action="/login" los envía a la ruta /login, registrada en index.php como
     $router->post('/login', [AuthController::class, 'login']). -->
<form method="post" action="/login">
    <?php
    // Pinta el campo oculto con el token CSRF.
    // csrf_field() está definida en public/index.php y devuelve
    // <input type="hidden" name="csrf_token" value="...">.
    // Al enviar el formulario, el token viaja en el POST y AuthController::login()
    // lo valida con checkCsrfToken() (hash_equals). Si no coincide → 403.
    ?>
    <?= csrf_field() ?>

    <!-- Etiqueta del email. for="email" la asocia con el input id="email". -->
    <label for="email">Email</label>
    <!-- Campo email. type="email" valida el formato en el navegador.
         name="email" es la clave en $_POST['email'].
         required impide enviar el formulario vacío (validación del cliente). -->
    <input type="email" name="email" id="email" required>

    <!-- Etiqueta de la contraseña. -->
    <label for="password">Contraseña</label>
    <!-- Campo contraseña. type="password" oculta los caracteres.
         name="password" es la clave en $_POST['password']. -->
    <input type="password" name="password" id="password" required>

    <!-- Botón de envío. type="submit" envía el formulario (POST a /login). -->
    <button type="submit">Entrar</button>
</form>

<!-- Enlace al registro. href="/register" es una ruta limpia registrada como
     $router->get('/register', [AuthController::class, 'showRegisterForm']). -->
<p>¿No tienes cuenta? <a href="/register">Regístrate</a></p>

<?php
// Incluye el pie común: cierra </main>, </body> y </html>
// (que abrió layout.php). Así el HTML queda bien formado.
require __DIR__ . '/../footer.php';
?>