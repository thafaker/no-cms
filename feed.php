<?php
require_once 'inc/functions.php';

header('Content-Type: application/rss+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

$posts = getPosts();
$posts = array_slice($posts, 0, 20); // nur die 20 neuesten

$baseUrl = 'https://janmontag.de';
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

        $title = $meta['title'] ?? basename($post);
        $date = $meta['date'] ?? date('r', filemtime($post));
        $link = $baseUrl . '/post.php?file=' . urlencode(basename($post));
        
        // Zusammenfassung: ersten 500 Zeichen
        $description = strip_tags($body);
        $description = substr($description, 0, 500) . '…';
    ?>
    <item>
        <title><?= htmlspecialchars($title) ?></title>
        <link><?= $link ?></link>
        <guid><?= $link ?></guid>
        <pubDate><?= date('r', strtotime($date)) ?></pubDate>
        <description><![CDATA[<?= $description ?>]]></description>
    </item>
    <?php endforeach; ?>
</channel>
</rss>