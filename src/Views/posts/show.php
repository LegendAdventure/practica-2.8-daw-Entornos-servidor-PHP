<?php
/** @var array $post */
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

<p style="margin-top: 2rem;"><a href="/posts">← Volver al listado</a></p>

<?php require __DIR__ . '/../footer.php'; ?>