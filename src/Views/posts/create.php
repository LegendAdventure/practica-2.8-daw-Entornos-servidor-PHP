<?php
/** @var void */
require __DIR__ . '/../layout.php';
?>

<h2>Nueva publicación</h2>

<form method="post" action="/posts/store">
    <label for="title">Título</label>
    <input type="text" name="title" id="title" required>

    <label for="category">Categoría</label>
    <input type="text" name="category" id="category" required>

    <label for="body">Cuerpo</label>
    <textarea name="body" id="body" rows="8" required></textarea>

    <button type="submit">Publicar</button>
</form>

<p><a href="/posts">← Volver al listado</a></p>

<?php require __DIR__ . '/../footer.php'; ?>