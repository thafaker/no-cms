<?php
require_once 'inc/functions.php';

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

$posts = getPosts();
$baseUrl = 'https://janmontag.de';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Startseite -->
    <url>
        <loc><?= $baseUrl ?>/</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>1.0</priority>
    </url>

    <!-- Alle Posts -->
    <?php foreach ($posts as $post): 
        $name = basename($post);
        $date = date('Y-m-d', filemtime($post));
    ?>
    <url>
        <loc><?= $baseUrl ?>/post.php?file=<?= urlencode($name) ?></loc>
        <lastmod><?= $date ?></lastmod>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
</urlset>