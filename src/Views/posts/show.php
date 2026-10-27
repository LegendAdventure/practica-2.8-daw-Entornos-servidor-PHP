<?php
/** @var array $post */
// Comentario para Intelephense: $post es un array (viene de PostController::show()).
/** @var array|null $comments */
// Comentario para Intelephense: $comments es array o null.

// Incluye la cabecera común (DOCTYPE, <head>, <header>, flash, <main>).
require __DIR__ . '/../layout.php';
?>

<!-- Título del post, escapado con htmlspecialchars para evitar XSS. -->
<h2><?= htmlspecialchars($post['title']) ?></h2>

<p>
    <!-- Categoría, autor y fecha del post. author_email viene del JOIN con users
         en Post::getByIdWithAuthor(). Todo escapado con htmlspecialchars. -->
    <strong>Categoría:</strong> <?= htmlspecialchars($post['category']) ?><br>
    <strong>Autor:</strong> <?= htmlspecialchars($post['author_email']) ?><br>
    <strong>Fecha:</strong> <?= htmlspecialchars($post['created_at']) ?>
</p>

<!-- Cuerpo del post. white-space: pre-wrap respeta los saltos de línea y espacios
     que haya metido el usuario. htmlspecialchars evita XSS. -->
<div style="white-space: pre-wrap; border-top: 1px solid #ccc; padding-top: 1rem;">
    <?= htmlspecialchars($post['body']) ?>
</div>

<?php
// Solo el autor del post puede editar/borrar.
// - !empty($_SESSION['user_id']): hay sesión.
// - (int) $_SESSION['user_id'] === (int) $post['user_id']: el usuario logueado
//   es el mismo que creó el post. (int) castea ambos a entero para comparar bien.
?>
<?php if (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $post['user_id']): ?>
    <p style="margin-top: 1rem;">
        <!-- Enlace al formulario de edición. Ruta limpia /posts/edit/<id>. -->
        <a href="/posts/edit/<?= (int) $post['id'] ?>">Editar</a>
        |
        <!-- Formulario de borrado. method="post" porque borrar por GET es mala
             práctica (un crawler o un prefetch podría borrar sin querer).
             action con ruta limpia: /posts/delete/<id>.
             csrf_field() añade el token CSRF.
             onclick="return confirm(...)" pide confirmación antes de borrar. -->
        <form method="post" action="/posts/delete/<?= (int) $post['id'] ?>" style="display: inline;">
            <?= csrf_field() ?>
            <button type="submit" onclick="return confirm('¿Seguro que quieres borrar esta publicación?')">Borrar</button>
        </form>
    </p>
<?php endif; ?>

<hr style="margin: 2rem 0;">

<!-- Cabecera de comentarios. Muestra el número de comentarios.
     $comments === null ? 0 : count($comments) → si no hay comentarios (null),
     muestra 0; si hay, cuenta cuántos. -->
<h3>Comentarios (<?= $comments === null ? 0 : count($comments) ?>)</h3>

<?php
// Si no hay comentarios (null o array vacío), muestra un mensaje.
// $comments viene de PostController::show() (Database::fetchAll con JOIN a users).
?>
<?php if ($comments === null || count($comments) === 0): ?>
    <p>Todavía no hay comentarios.</p>
<?php else: ?>
    <ul style="list-style: none; padding: 0;">
        <?php
        // Recorre cada comentario y pinta una tarjeta con autor, fecha y cuerpo.
        // Todo escapado con htmlspecialchars para evitar XSS.
        ?>
        <?php foreach ($comments as $comment): ?>
            <li style="border-left: 3px solid #ccc; padding: 0.5rem 1rem; margin-bottom: 0.75rem;">
                <p style="margin: 0 0 0.25rem 0;">
                    <!-- Autor (email) y fecha del comentario. -->
                    <strong><?= htmlspecialchars($comment['author_email']) ?></strong>
                    <small><?= htmlspecialchars($comment['created_at']) ?></small>
                </p>
                <!-- Cuerpo del comentario. white-space: pre-wrap respeta saltos. -->
                <p style="margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($comment['body']) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
// Si hay sesión, muestra el formulario para añadir un comentario.
// Si no hay sesión, muestra un enlace a /login.
?>
<?php if (!empty($_SESSION['user_id'])): ?>
    <h4>Añadir comentario</h4>
    <!-- Formulario de comentario. POST a /comments/store.
         csrf_field() añade el token CSRF.
         post_id va en un campo oculto (no es sensible: es el post al que se comenta).
         El user_id NO va en el formulario: lo pone CommentController::store()
         desde $_SESSION. Así un atacante no puede suplantar a otro usuario. -->
    <form method="post" action="/comments/store">
        <?= csrf_field() ?>
        <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
        <textarea name="body" rows="4" required></textarea>
        <button type="submit">Comentar</button>
    </form>
<?php else: ?>
    <!-- Sin sesión: enlace al login para poder comentar. -->
    <p><a href="/login">Inicia sesión</a> para comentar.</p>
<?php endif; ?>

<!-- Enlace para volver al listado de posts. -->
<p style="margin-top: 2rem;"><a href="/posts">← Volver al listado</a></p>

<?php
// Incluye el pie común: cierra </main>, </body> y </html>.
require __DIR__ . '/../footer.php';
?>