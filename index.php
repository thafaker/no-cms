<?php
// index.php of NoCMS Jan Montag 2026
require_once 'inc/functions.php';

$allPosts = getPosts();
$latestPosts = array_slice($allPosts, 0, 3);

$pageTitle = "Jan Montag";
$bodyClass = "layout-index";
require_once 'inc/header.php';
?>
    <main>
        <div id="lastfm-box" class="lastfm-widget" style="display: none;">
            <span class="now-playing-icon">♫</span> <span id="lastfm-track">Lade Musik...</span>
        </div>

        <section>
            <i>Writing</i>
            <ul>
                <?php if (!empty($latestPosts)): ?>
                    <?php foreach ($latestPosts as $postPath): 
                        $content = file_get_contents($postPath);
                        $parsed = parseFrontmatter($content);
                        $meta = $parsed['meta'];
                        $filename = basename($postPath);
                        
                        $slug = preg_replace('/^\d{4}-\d{2}-\d{2}-/', '', str_replace('.md', '', $filename));
                        $title = $meta['title'] ?? $slug;
                        $desc = $meta['description'] ?? date("d. M Y", isset($meta['date']) ? strtotime($meta['date']) : filemtime($postPath));
                    ?>
                        <li>
                            <a class="title" href="/<?= urlencode($slug) ?>"><?= htmlspecialchars($title) ?></a>
                            <span class="dotted-line"></span>
                            <a class="desc" href="/<?= urlencode($slug) ?>"><?= htmlspecialchars($desc) ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Keine Beiträge vorhanden.</li>
                <?php endif; ?>
            </ul>
            <a class="archive-link" href="/archive.php">Zum Archiv &rarr;</a>
        </section>

        <section style="margin-top: 1rem;">
            <i>Other</i>
            <ul>
                <li>
                    <a class="title" href="https://github.com/thafaker" target="_blank" rel="noopener">Projects</a>
                    <span class="dotted-line"></span>
                    <span style="opacity: 0.4; font-size: 0.9rem; white-space: nowrap;">GitHub Repo</span>
                </li>
                <li>
                    <a class="title" href="/about">Über</a>
                    <span class="dotted-line"></span>
                    <span style="opacity: 0.4; font-size: 0.9rem; white-space: nowrap;">Info</span>
                </li>
               <li>
                    <a class="title" href="/links">Links</a>
                    <span class="dotted-line"></span>
                    <span style="opacity: 0.4; font-size: 0.9rem; white-space: nowrap;">My Websites</span>
                </li>
            </ul>
        </section>

        <?php include 'footer.php'; ?>
    </main>
</body>
</html>