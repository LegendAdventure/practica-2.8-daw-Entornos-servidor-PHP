<?php
// Incluye la cabecera común (DOCTYPE, <head>, <header> con navegación,
// mensajes flash y apertura de <main>).
// __DIR__ es src/Views/auth; /../layout.php sube a src/Views/layout.php.
require __DIR__ . '/../layout.php';
?>

<h2>Crear cuenta</h2>

<!-- Formulario de registro. method="post" envía los datos por POST.
     action="/register" los envía a la ruta /register, registrada en index.php como
     $router->post('/register', [AuthController::class, 'register']). -->
<form method="post" action="/register">
    <?php
    // Pinta el campo oculto con el token CSRF.
    // csrf_field() está definida en public/index.php y devuelve
    // <input type="hidden" name="csrf_token" value="...">.
    // Al enviar el formulario, el token viaja en el POST y AuthController::register()
    // lo valida con checkCsrfToken() (hash_equals). Si no coincide → 403.
    ?>
    <?= csrf_field() ?>

    <!-- Etiqueta del email. for="email" la asocia con el input id="email". -->
    <label for="email">Email</label>
    <!-- Campo email. type="email" valida el formato en el navegador.
         name="email" es la clave en $_POST['email'].
         required impide enviar el formulario vacío (validación del cliente). -->
    <input type="email" name="email" id="email" required>

    <!-- Etiqueta del nombre de usuario. for="username" la asocia con id="username". -->
    <label for="username">Nombre de usuario</label>
    <!-- Campo texto para el nombre de usuario.
         name="username" es la clave en $_POST['username'].
         En AuthController::register() se valida que no esté vacío. -->
    <input type="text" name="username" id="username" required>

    <!-- Etiqueta de la contraseña. -->
    <label for="password">Contraseña</label>
    <!-- Campo contraseña. type="password" oculta los caracteres.
         name="password" es la clave en $_POST['password'].
         En register() se hashea con password_hash() antes de guardar. -->
    <input type="password" name="password" id="password" required>

    <!-- Botón de envío. type="submit" envía el formulario (POST a /register). -->
    <button type="submit">Registrarme</button>
</form>

<!-- Enlace al login. href="/login" es una ruta limpia registrada como
     $router->get('/login', [AuthController::class, 'showLoginForm']). -->
<p>¿Ya tienes cuenta? <a href="/login">Inicia sesión</a></p>

<?php
// Incluye el pie común: cierra </main>, </body> y </html>
// (que abrió layout.php). Así el HTML queda bien formado.
require __DIR__ . '/../footer.php';
?>