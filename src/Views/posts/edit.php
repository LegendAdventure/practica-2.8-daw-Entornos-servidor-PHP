<?php
/** @var array $post */
require __DIR__ . '/../layout.php';
?>

<h2>Editar publicación</h2>

<form method="post" action="/posts/update">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">

    <label for="title">Título</label>
    <input type="text" name="title" id="title"
           value="<?= htmlspecialchars($post['title']) ?>" required>

    <label for="category">Categoría</label>
    <input type="text" name="category" id="category"
           value="<?= htmlspecialchars($post['category']) ?>" required>

    <label for="body">Cuerpo</label>
    <textarea name="body" id="body" rows="8" required><?= htmlspecialchars($post['body']) ?></textarea>

    <button type="submit">Guardar cambios</button>
</form>

<p><a href="/posts/show?id=<?= (int) $post['id'] ?>">← Volver al detalle</a></p>

<?php require __DIR__ . '/../footer.php'; ?>