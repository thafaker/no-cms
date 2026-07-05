<?php
require_once 'inc/functions.php';

/* ---------------------------
   LOAD FILE
---------------------------- */
$file = $_GET['file'] ?? '';
$file = basename($file);
$path = __DIR__ . '/posts/' . $file;

if (!$file || !is_file($path)) {
    http_response_code(404);
    $file = 'not-found.md';
    $content = "# 404\nFile not found.";
} else {
    $content = file_get_contents($path);
}

/* ---------------------------
   FRONTMATTER PARSEN (ausgelagert)
---------------------------- */
$parsed = parseFrontmatter($content);
$meta = $parsed['meta'];
$markdown = $parsed['content'];

/* ---------------------------
   MARKDOWN ZU HTML (ausgelagert)
---------------------------- */
$converter = createMarkdownConverter();
$html = $converter->convert($markdown)->getContent();
// HTML-Entities dekodieren, für Iframes etc.
$html = html_entity_decode($html);


/* ---------------------------
   META FÜR ANZEIGE
---------------------------- */
$title = $meta['title'] ?? $file;
$date  = $meta['date'] ?? '';
$tags = '';

if (!empty($meta['tags'])) {
    $tags = is_array($meta['tags']) ? implode(', ', $meta['tags']) : $meta['tags'];
}

/* ---------------------------
   Prism JS für schön formatierte Wurstgesichter aber nur in den posts nicht INDEX
---------------------------- */
$includePrism = true;

/* ---------------------------
   HEADER (ausgelagert)
---------------------------- */
$pageTitle = $title . ' · JanMontag.de';
require 'inc/header.php';
?>

<!-- .content wurde bereits in header.php geöffnet -->

<div class="status">
    $ cat <?= htmlspecialchars($file) ?>
</div>

<div class="meta">
    <?php if ($title): ?>
        <div><span class="key">title</span> : <?= htmlspecialchars($title) ?></div>
    <?php endif; ?>
    <?php if ($date): ?>
        <div><span class="key">date&nbsp;</span> : <?= htmlspecialchars($date) ?></div>
    <?php endif; ?>
    <?php if ($tags): ?>
        <div><span class="key">tags&nbsp;</span> : <?= htmlspecialchars($tags) ?></div>
    <?php endif; ?>
</div>

<hr>

<div class="article">
    <?= $html ?>
</div>

<p style="margin-top:2rem;">
    <a href="/">cd ..</a>
</p>

<?php include "footer.php"; ?>
    <div class="footer">
        <span class="label">$ wc -l visitors.log</span><br>
        <span id="counter">...</span>
    </div>

</div> <!-- .content schließen (falls nicht in footer.php) -->

</body>
<!-- JavaScript (Counter + last.fm) -->
<script src="/assets/script.js" defer></script>
</html>