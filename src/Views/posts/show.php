<?php
/** @var array $post */
/** @var array|null $comments */
require __DIR__ . '/../layout.php';
?>

<h2><?= htmlspecialchars($post['title']) ?></h2>

<p>
    <strong>Categoría:</strong> <?= htmlspecialchars($post['category']) ?><br>
    <strong>Autor:</strong> <?= htmlspecialchars($post['author_email']) ?><br>
    <strong>Fecha:</strong> <?= htmlspecialchars($post['created_at']) ?>
</p>

<div style="white-space: pre-wrap; border-top: 1px solid #ccc; padding-top: 1rem;">
    <?= htmlspecialchars($post['body']) ?>
</div>

<?php if (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] === (int) $post['user_id']): ?>
    <p style="margin-top: 1rem;">
        <a href="/posts/edit?id=<?= (int) $post['id'] ?>">Editar</a>
        |
        <form method="post" action="/posts/delete" style="display: inline;">
            <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
            <button type="submit" onclick="return confirm('¿Seguro que quieres borrar esta publicación?')">Borrar</button>
        </form>
    </p>
<?php endif; ?>

<hr style="margin: 2rem 0;">

<h3>Comentarios (<?= $comments === null ? 0 : count($comments) ?>)</h3>

<?php if ($comments === null || count($comments) === 0): ?>
    <p>Todavía no hay comentarios.</p>
<?php else: ?>
    <ul style="list-style: none; padding: 0;">
        <?php foreach ($comments as $comment): ?>
            <li style="border-left: 3px solid #ccc; padding: 0.5rem 1rem; margin-bottom: 0.75rem;">
                <p style="margin: 0 0 0.25rem 0;">
                    <strong><?= htmlspecialchars($comment['author_email']) ?></strong>
                    <small><?= htmlspecialchars($comment['created_at']) ?></small>
                </p>
                <p style="margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($comment['body']) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (!empty($_SESSION['user_id'])): ?>
    <h4>Añadir comentario</h4>
    <form method="post" action="/comments/store">
        <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
        <textarea name="body" rows="4" required></textarea>
        <button type="submit">Comentar</button>
    </form>
<?php else: ?>
    <p><a href="/login">Inicia sesión</a> para comentar.</p>
<?php endif; ?>

<p style="margin-top: 2rem;"><a href="/posts">← Volver al listado</a></p>

<?php require __DIR__ . '/../footer.php'; ?>