<?php
// With that skill that was hers alone (C) Jan Montag 2026
require_once 'inc/functions.php';

header('Content-Type: application/rss+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

$posts = getPosts();
$posts = array_slice($posts, 0, 20); // nur die 20 neuesten

// Dynamische Domain-Erkennung für die URLs im Feed
$baseUrl = 'https://janmontag.de';
if (strpos($_SERVER['HTTP_HOST'], 'dev.') === 0) {
    $baseUrl = 'https://dev.janmontag.de';
}

$now = date('r');
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>Jan Montag</title>
    <link><?= $baseUrl ?></link>
    <description>Persönliches Weblog von Jan Montag – Gedanken, Code und mehr</description>
    <language>de</language>
    <lastBuildDate><?= $now ?></lastBuildDate>
    <atom:link href="<?= $baseUrl ?>/feed.xml" rel="self" type="application/rss+xml" />

    <?php foreach ($posts as $post): 
        $content = file_get_contents($post);
        $parsed = parseFrontmatter($content);
        $meta = $parsed['meta'];
        $body = $parsed['content'];

        $filename = basename($post);
        
        // Exakt dieselbe Pretty-URL Logik: Datum (YYYY-MM-DD-) und .md abschneiden
        $slug = preg_replace('/^\d{4}-\d{2}-\d{2}-/', '', str_replace('.md', '', $filename));

        $title = $meta['title'] ?? str_replace('-', ' ', $slug);
        
        // Formatiere das Datum für den RSS-Standard (RFC 2822)
        $date = isset($meta['date']) ? date('r', strtotime($meta['date'])) : date('r', filemtime($post));
        
        // Die neue, saubere URL ohne post.php und Parameter
        $link = $baseUrl . '/' . urlencode($slug);
        
        // Zusammenfassung: ersten 500 Zeichen sauber extrahieren
        $description = strip_tags($body);
        $description = mb_substr($description, 0, 500, 'UTF-8') . '…';
    ?>
    <item>
        <title><?= htmlspecialchars($title) ?></title>
        <link><?= $link ?></link>
        <guid><?= $link ?></guid>
        <pubDate><?= $date ?></pubDate>
        <description><?= htmlspecialchars($description) ?></description>
    </item>
    <?php endforeach; ?>
</channel>
</rss>