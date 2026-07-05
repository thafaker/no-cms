<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="alternate" type="application/rss+xml" title="Jan Montag" href="/feed.xml">
    <link rel="sitemap" type="application/xml" href="/sitemap.xml">
    <link rel="me" href="https://github.com/thafaker">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'JanMontag.de') ?></title>
    <link rel="stylesheet" href="/assets/style.css">
<!-- code highling klauen wir uns von der prism js -->
    <?php if (isset($includePrism) && $includePrism): ?>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js" defer></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js" defer></script>
    <?php endif; ?>
<!-- // code highling klauen wir uns von der prism js -->
<!-- wir laden umami counter -->
     <script defer src="https://umami.wochenstart.com/script.js" data-website-id="5e4aceef-d428-4ef3-958d-df37654a6d59"></script>
<!-- // wir laden umami counter -->
</head>
<body>
<div class="content">