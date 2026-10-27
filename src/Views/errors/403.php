<?php
// Incluye la cabecera común (DOCTYPE, <head>, <header> con navegación,
// mensajes flash y apertura de <main>).
// __DIR__ es src/Views/errors; /../layout.php sube a src/Views/layout.php.
require __DIR__ . '/../layout.php';
?>

<h2>403 — Prohibido</h2>

<!-- Mensaje de error. Se muestra cuando un usuario logueado intenta
     editar o borrar un post que no es suyo. El código HTTP 403 ya se
     ha enviado antes desde el controlador con http_response_code(403). -->
<p>No tienes permiso para realizar esta acción.</p>

<!-- Enlace para volver al listado de posts (ruta limpia /posts). -->
<p><a href="/posts">← Volver al listado</a></p>

<?php
// Incluye el pie común: cierra </main>, </body> y </html>
// (que abrió layout.php). Así el HTML queda bien formado.
require __DIR__ . '/../footer.php';
?>