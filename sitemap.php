<?php
// sitemap.php of NoCMS Jan Montag 2026
require_once 'inc/functions.php';

// Setze den korrekten Content-Type, damit Browser und Suchmaschinen sie als XML erkennen
header("Content-Type: application/xml; charset=utf-8");

// Deine Domain (passe sie ggf. an, falls du das Protokoll erzwingen willst)
$baseUrl = "https://janmontag.de"; 
if (strpos($_SERVER['HTTP_HOST'], 'dev.') === 0) {
    $baseUrl = "https://dev.janmontag.de";
}

// Alle Blogposts über deine native NoCMS-Funktion laden
$allPosts = getPosts();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    
    <url>
        <loc><?= $baseUrl ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc><?= $baseUrl ?>/archive.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    <?php foreach ($allPosts as $postPath): 
        $filename = basename($postPath);
        
        // Exakt dieselbe Logik wie in der index.php: Datum und Endung entfernen
        $slug = preg_replace('/^\d{4}-\d{2}-\d{2}-/', '', str_replace('.md', '', $filename));
        
        // Letztes Änderungsdatum der Datei für <lastmod> ermitteln
        $lastMod = date('Y-m-d', filemtime($postPath));
    ?>
    <url>
        <loc><?= $baseUrl ?>/<?= urlencode($slug) ?></loc>
        <lastmod><?= $lastMod ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

</urlset>