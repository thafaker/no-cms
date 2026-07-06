<!DOCTYPE html>
<html lang="de">
<head>
    <!-- IT HAPPENED IN NOVEMBER -->
    <link rel="alternate" type="application/rss+xml" title="Jan 'thafaker' Montag" href="/feed.xml">
    <link rel="sitemap" type="application/xml" href="/sitemap.xml">
    <link rel="me" href="https://github.com/thafaker">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'JanMontag.de') ?></title>
    <link rel="stylesheet" href="/assets/style.css">

    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <?php if (isset($includePrism) && $includePrism): ?>
        <script>
            const prismTheme = savedTheme === 'light' ? 'prism' : 'prism-tomorrow';
            document.write('<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/' + prismTheme + '.min.css" rel="stylesheet" id="prism-css" />');
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js" defer></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js" defer></script>
    <?php endif; ?>

     <script defer src="https://umami.wochenstart.com/script.js" data-website-id="5e4aceef-d428-4ef3-958d-df37654a6d59"></script>
</head>
<body>
<div class="content">
