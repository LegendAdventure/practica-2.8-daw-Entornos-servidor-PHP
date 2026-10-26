<?php
/** @var array|null $posts */
require __DIR__ . '/../layout.php';
?>

<h2>Publicaciones</h2>

<form method="get" action="/posts" style="margin-bottom: 1rem;">
    <input type="text" name="q" placeholder="Buscar por título o categoría..."
           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    <button type="submit">Buscar</button>
</form>

<?php if (!empty($_SESSION['user_id'])): ?>
    <p><a href="/posts/create">+ Publicar anuncio</a></p>
<?php endif; ?>

<?php if ($posts === null || count($posts) === 0): ?>
    <p>No hay publicaciones que mostrar.</p>
<?php else: ?>
    <ul style="list-style: none; padding: 0;">
        <?php foreach ($posts as $post): ?>
            <li style="border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
                <h3 style="margin-top: 0;">
                    <a href="/posts/show/<?= (int) $post['id'] ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h3>
                <p style="margin: 0.5rem 0;">
                    <strong>Categoría:</strong> <?= htmlspecialchars($post['category']) ?><br>
                    <strong>Autor:</strong> <?= htmlspecialchars($post['author_email']) ?><br>
                    <strong>Fecha:</strong> <?= htmlspecialchars($post['created_at']) ?>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require __DIR__ . '/../footer.php'; ?>