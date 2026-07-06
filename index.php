<?php
// index.php of NoCMS Jan Montag 2026
// The sore in my soul
// The mark in my heart
// Her acid reign
require_once 'inc/functions.php';

//$posts = getPosts();
// zeige nicht mehr als 5 Beiträge auf der Startseite
$posts = array_slice(getPosts(), 0, 5);

$pageTitle = '404 · JanMontag.de';
require 'inc/header.php';
?>
<!-- lost and dumbfunded -->
<div class="status" style="display: flex; justify-content: space-between; align-items: center;">
    <span>404 — Gone</span>
    <span id="themeToggle" style="cursor: pointer; font-size: 0.9rem; text-transform: none; letter-spacing: normal;">$ theme --toggle</span>
</div>
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

<!-- Footer (bleibt innerhalb von .content sonst kaputt mit die CSS) -->
<?php include "footer.php"; ?>

<script src="/assets/script.js" defer></script>
<script defer src="https://umami.wochenstart.com/script.js" data-website-id="5e4aceef-d428-4ef3-958d-df37654a6d59"></script>

</body>
</html>
