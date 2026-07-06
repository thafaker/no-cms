<?php
// archive.php of NoCMS Jan Montag 2026
require_once 'inc/functions.php';

// Hier holen wir die komplette Liste aller Posts (ohne array_slice)
$posts = getPosts();

$pageTitle = 'Archive · JanMontag.de';
require 'inc/header.php';
?>

<div class="status" style="display: flex; justify-content: space-between; align-items: center;">
    <span>200 — OK (Archiv)</span>
    <span id="themeToggle" style="cursor: pointer; font-size: 0.9rem; text-transform: none; letter-spacing: normal;">$ theme --toggle</span>
</div>

<div class="counter">
    <span class="label">$ cat archive/history.log</span>
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

<p style="margin-top:2rem;">
    <a href="/">cd ..</a>
</p>

<?php include "footer.php"; ?>

<script src="/assets/script.js" defer></script>
<script defer src="https://umami.wochenstart.com/script.js" data-website-id="5e4aceef-d428-4ef3-958d-df37654a6d59"></script>

</body>
</html>
