<?php require __DIR__ . '/../layout.php'; ?>

<h2>Iniciar sesión</h2>

<form method="post" action="/login">
    <label for="email">Email</label>
    <input type="email" name="email" id="email" required>

    <label for="password">Contraseña</label>
    <input type="password" name="password" id="password" required>

    <button type="submit">Entrar</button>
</form>

<p>¿No tienes cuenta? <a href="/register">Regístrate</a></p>

<?php require __DIR__ . '/../footer.php'; ?>