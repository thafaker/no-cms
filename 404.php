<?php
// Echten 404-Status senden
http_response_code(404);

// Seite für den Browser
$pageTitle = '404 · Seite nicht gefunden';
require 'inc/header.php';
?>

<div class="status">404 — Seite nicht gefunden</div>

<div class="command-wrapper">
    <div class="command">
        <span class="prompt">$</span> cd /var/www/404-theme_indieweb/posts
    </div>
</div>

<div style="margin-top:2rem;">
    <p>Die angeforderte Seite existiert nicht.</p>
    <p style="margin-top:1rem;">
        <a href="/">← zurück zur Startseite</a>
    </p>
</div>

<!-- Optional: Letzte Posts anzeigen -->
<div class="counter" style="margin-top:2rem;">
    <span class="label">$ ls -la posts/ (aktuelle Posts)</span>
    <?php
    require_once 'inc/functions.php';
    $posts = getPosts();
    $posts = array_slice($posts, 0, 5);
    foreach ($posts as $post):
        $name = basename($post);
        $slug = pathinfo($name, PATHINFO_FILENAME);
    ?>
    <div class="postline">
        <a href="/posts/<?= urlencode($slug) ?>"><?= htmlspecialchars($name) ?></a>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>