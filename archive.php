<?php
// archive.php of NoCMS Jan Montag 2026
require_once 'inc/functions.php';

// Holt alle jemals geschriebenen Blogposts über deine native NoCMS-Funktion
$allPosts = getPosts();

// Variablen für die inc/header.php setzen
$pageTitle = "Archiv — Jan Montag";
$bodyClass = "layout-index"; // Nutzt dasselbe zentrierte Listen-Layout wie die Startseite
require_once 'inc/header.php';
?>
    <main>
        <section>
            <div style="margin-bottom: 2rem; width: 100%;">
                <a href="/" style="font-size: 0.9rem; opacity: 0.4; border-bottom: none;">&larr; Zurück</a>
            </div>
            
            <i>Archiv</i>
            <ul>
                <?php if (!empty($allPosts)): ?>
                    <?php foreach ($allPosts as $postPath): 
                        $content = file_get_contents($postPath);
                        $parsed = parseFrontmatter($content);
                        $meta = $parsed['meta'];
                        $filename = basename($postPath);
                        
                        // Generiere den schönen, sauberen URL-Slug (ohne Datum und .md)
                        $slug = preg_replace('/^\d{4}-\d{2}-\d{2}-/', '', str_replace('.md', '', $filename));
                        $title = $meta['title'] ?? $slug;
                        
                        // Nutzt die Beschreibung oder formatiert das Erstellungsdatum
                        $desc = $meta['description'] ?? date("d. M Y", isset($meta['date']) ? strtotime($meta['date']) : filemtime($postPath));
                    ?>
                        <li>
                            <a class="title" href="/<?= urlencode($slug) ?>"><?= htmlspecialchars($title) ?></a>
                            <span class="dotted-line"></span>
                            <a class="desc" href="/<?= urlencode($slug) ?>"><?= htmlspecialchars($desc) ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Keine Beiträge im Archiv gefunden.</li>
                <?php endif; ?>
            </ul>
        </section>
        <?php require_once 'footer.php'; ?>
    </main>
</body>
</html>