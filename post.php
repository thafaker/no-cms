<?php
// post.php of NoCMS Jan Montag 2026
// Wurstwasser --> Arschwasser
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

$converter = createMarkdownConverter();
$htmlContent = html_entity_decode($converter->convert($markdown)->getContent());

// ==========================================================================
// OUTGOING WEBMENTIONS: Links im Text automatisch anpingen
// ==========================================================================
$currentUrl = "https://janmontag.de/" . $slug;

// Verhindert lange Ladezeiten beim lokalen Entwickeln
if (!str_contains($_SERVER['HTTP_HOST'], 'localhost') && !str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')) {
    sendOutgoingWebmentionsFromHtml($htmlContent, $currentUrl);
}
// ==========================================================================

// WEBMENTIONS FÜR DIESEN POST HOLEN (Incoming)
$webmentions = getWebmentions($currentUrl);

// 5. WEBMENTIONS FÜR DIESEN POST HOLEN
$currentUrl = "https://janmontag.de/" . $slug;
$webmentions = getWebmentions($currentUrl);

// Sortierung in Interaktionen (Likes/Reposts) und Text-Kommentare
$interactions = [];
$comments = [];

foreach ($webmentions as $mention) {
    $type = $mention['wm-property'] ?? 'mention-of';
    if (in_array($type, ['like-of', 'repost-of', 'bookmark-of'])) {
        $interactions[] = $mention;
    } else {
        $comments[] = $mention;
    }
}

// Header-Variablen vorbereiten
$pageTitle = ($meta['title'] ?? str_replace('-', ' ', $slug)) . " — Jan Montag";
$bodyClass = "layout-post";
require_once 'inc/header.php';
?>
    <!-- Prism.js Support für Code-Syntax-Highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js" defer></script>
    
    <main>
        <article class="h-entry"> <!-- h-entry für semantisches IndieWeb Parsing -->
            <!-- Subtile Zurück-Navigation -->
            <nav class="back-nav">
                <a class="back-link" href="/">&larr; Index</a>
            </nav>

            <!-- Artikel-Header -->
            <header>
                <h1 class="p-name"><?= htmlspecialchars($meta['title'] ?? str_replace('-', ' ', $slug)); ?></h1>
                <a class="u-url" href="<?= $currentUrl ?>" style="display:none;"></a>
            </header>
            
            <!-- Der gerenderte Content aus deinem NoCMS Core -->
            <div class="entry-content e-content">
                <?= $htmlContent; ?>
            </div>

            <!-- ==========================================================================
               WEBMENTIONS SEKTION
               ========================================================================== -->
            <section class="webmentions-container" style="margin-top: 4rem; padding-top: 2rem; border-top: 1px dotted var(--line-color); font-size: 0.9rem;">
                
                <!-- 1. Likes & Reposts (Kompakte Avatare) -->
                <?php if (!empty($interactions)): ?>
                    <div class="webmention-interactions" style="margin-bottom: 2rem; opacity: 0.8;">
                        <strong>Interaktionen:</strong>
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem; flex-wrap: wrap;">
                            <?php foreach ($interactions as $inter): 
                                $author = $inter['author'] ?? [];
                                $avatar = !empty($author['photo']) ? $author['photo'] : 'https://herrmontag.de/content/images/2026/03/avar.jpg';
                                $actionText = ($inter['wm-property'] === 'like-of') ? 'gefällt das' : 'hat geshared';
                            ?>
                                <a href="<?= htmlspecialchars($inter['wm-source'] ?? '#') ?>" title="<?= htmlspecialchars($author['name'] ?? 'Jemand') ?> <?= $actionText ?>" target="_blank" rel="noopener">
                                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" style="width: 28px; height: 28px; border-radius: 50%; margin: 0; border: 1px solid var(--line-color);">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 2. Echte Text-Erwähnungen & Antworten -->
                <div class="webmention-comments">
                    <h3 style="font-size: 1rem; margin-bottom: 1.5rem;">Reaktionen (<?= count($comments) ?>)</h3>
                    <?php if (!empty($comments)): ?>
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <?php foreach ($comments as $comment): 
                                $author = $comment['author'] ?? [];
                                $avatar = !empty($author['photo']) ? $author['photo'] : 'https://herrmontag.de/content/images/2026/03/avar.jpg';
                                $pubDate = isset($comment['published']) ? date("d. M Y", strtotime($comment['published'])) : 'Kürzlich';
                                $text = $comment['content']['text'] ?? $comment['content']['html'] ?? 'Hat diesen Beitrag erwähnt.';
                                // Text einkürzen, falls jemand ein gigantisches Posting verlinkt
                                if (strlen($text) > 280) $text = substr(strip_tags($text), 0, 280) . '...';
                            ?>
                                <div class="webmention-item" style="display: flex; gap: 1rem; align-items: flex-start;">
                                    <img src="<?= htmlspecialchars($avatar) ?>" alt="" style="width: 36px; height: 36px; border-radius: 50%; margin: 0; flex-shrink: 0; border: 1px solid var(--line-color);">
                                    <div>
                                        <div style="opacity: 0.6; font-size: 0.8rem;">
                                            <a href="<?= htmlspecialchars($author['url'] ?? '#') ?>" target="_blank" rel="noopener" style="font-weight: 600; border-bottom: none;">
                                                <?= htmlspecialchars($author['name'] ?? 'Anonym') ?>
                                            </a> · 
                                            <a href="<?= htmlspecialchars($comment['wm-source']) ?>" target="_blank" rel="noopener" style="border-bottom: none;">
                                                <?= $pubDate ?>
                                            </a>
                                        </div>
                                        <div style="margin-top: 0.25rem; line-height: 1.5;">
                                            <?= htmlspecialchars($text) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="opacity: 0.5; font-style: italic;">Noch keine Webmentions vorhanden. Schreibe eine Antwort auf deinem Blog oder via Mastodon und verlinke diesen Post!</p>
                    <?php endif; ?>
                </div>
            </section>
        </article>
        <?php require_once 'footer.php'; ?>
    </main>
</body>
</html>