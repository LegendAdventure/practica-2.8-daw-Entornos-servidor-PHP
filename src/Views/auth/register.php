<?php require __DIR__ . '/../layout.php'; ?>

<h2>Crear cuenta</h2>

<form method="post" action="/register">
    <label for="email">Email</label>
    <input type="email" name="email" id="email" required>

    <label for="username">Nombre de usuario</label>
    <input type="text" name="username" id="username" required>

    <label for="password">Contraseña</label>
    <input type="password" name="password" id="password" required>

    <button type="submit">Registrarme</button>
</form>

<p>¿Ya tienes cuenta? <a href="/login">Inicia sesión</a></p>

<?php require __DIR__ . '/../footer.php'; ?>