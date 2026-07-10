<?php
// post.php of NoCMS Jan Montag 2026
require_once 'inc/functions.php';

// 1. Den sauberen Slug aus der URL holen (wird von Caddy via URL-Rewrite übergeben)
$slug = $_GET['slug'] ?? '';
$slug = trim($slug, '/');

$postsDir = __DIR__ . '/posts/';
$targetFile = null;

// 2. Passende Datei im posts/-Ordner anhand des Slugs finden
if (!empty($slug) && is_dir($postsDir)) {
    $files = glob($postsDir . '*.md');
    foreach ($files as $file) {
        $filename = basename($file);
        $fileSlug = preg_replace('/^\d{4}-\d{2}-\d{2}-/', '', str_replace('.md', '', $filename));
        
        if ($fileSlug === $slug) {
            $targetFile = $file;
            break;
        }
    }
}

// 3. Fallback auf die 404-Seite, falls die Datei nicht existiert
if (!$targetFile || !is_file($targetFile)) {
    http_response_code(404);
    $targetFile = $postsDir . 'not-found.md';
    if (!is_file($targetFile)) {
        die("<h1>404 — Beitrag nicht gefunden</h1>");
    }
}

// 4. Inhalt laden, Frontmatter parsen und über CommonMark jagen
$content = file_get_contents($targetFile);
$parsed = parseFrontmatter($content);
$meta = $parsed['meta'];
$markdown = $parsed['content'];

$converter = createMarkdownConverter();
$htmlContent = html_entity_decode($converter->convert($markdown)->getContent());

// Header-Variablen vorbereiten
$pageTitle = ($meta['title'] ?? str_replace('-', ' ', $slug)) . " — Jan Montag";
$bodyClass = "layout-post";
require_once 'inc/header.php';
?>
    <main>
        <article>
            <!-- Subtile Zurück-Navigation -->
            <nav class="back-nav">
                <a class="back-link" href="/">&larr; Index</a>
            </nav>

            <!-- Artikel-Header -->
            <header>
                <h1><?= htmlspecialchars($meta['title'] ?? str_replace('-', ' ', $slug)); ?></h1>
            </header>
            
            <!-- Der gerenderte Content aus deinem NoCMS Core -->
            <div class="entry-content">
                <?= $htmlContent; ?>
            </div>
        </article>
        <?php require_once 'footer.php'; ?>
    </main>
</body>
</html>