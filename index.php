<?php
// index.php of NoCMS Jan Montag 2026
require_once 'inc/functions.php';

$posts = getPosts();

$pageTitle = '404 · JanMontag.de';
require 'inc/header.php';
?>

<!-- .content wurde bereits in header.php geöffnet! -->

<div class="status">404 — Gone</div>
<div class="command-wrapper">
    <div class="command">
        <span class="prompt">$</span> rm -rf /
    </div>
</div>

<div class="nowplaying" id="nowplaying">
    <span class="label">$ nowplaying</span><br>
    <span id="track">lade…</span>
</div>

<div class="counter">
    <span class="label">$ wc -l visitors.log</span><br>
    <span id="counter">...</span>
</div>

<div class="counter">
    <span class="label">$ ls -la posts/</span>
    <?php foreach ($posts as $post): ?>
        <?php
        $name = basename($post);
        $date = date("M d H:i", filemtime($post));
        $size = filesize($post);
        ?>
        <div class="postline">
            -rw-r--r-- <?= $size ?> <?= $date ?>
            <a href="post.php?file=<?= urlencode($name) ?>">
                <?= htmlspecialchars($name) ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Footer (bleibt innerhalb von .content) -->
<?php include "footer.php"; ?>

<!-- .content wird in footer.php geschlossen? -->
<!-- Falls nicht: hier ein </div> einfügen -->

<script src="/assets/script.js" defer></script>
<script defer src="https://umami.wochenstart.com/script.js" data-website-id="5e4aceef-d428-4ef3-958d-df37654a6d59"></script>

</body>
</html>
