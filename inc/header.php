<?php
// header.php of NoCMS Jan Montag 2026
$bodyClass = $bodyClass ?? '';
$pageTitle = $pageTitle ?? 'NoCMS Project';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Die H-Karte Junge (IndieWeb Microformats2) -->
    <div class="h-card" style="display: none !important;">
        <p class="p-name">Jan Montag</p>
        <p class="p-note">Herr Montag being the Web since 1997. Herr Montag works and lives in Erfurt, Thuringia, Germany.</p>
        
        <!-- Deine Domänen-Struktur mit Identitäts-Verknüpfung -->
        <a class="u-url" rel="me" href="https://janmontag.de">.microblog JanMontag.de</a>
        <a class="u-url" rel="me" href="https://herrmontag.de">.macroblog HerrMontag.de</a>
        <a class="u-url" rel="me" href="https://apfelhammer.de">Apfelhammer</a>
        <a class="u-url" rel="me" href="https://thahipster.de">thahipster.de</a>
        <a class="u-url" rel="me" href="https://wochenstart.com">Wochenstart.com</a>
        <a class="u-url" rel="me" href="https://github.com/thafaker">GitHub</a>
        
        <img class="u-photo" src="https://herrmontag.de/content/images/2026/03/avar.jpg" alt="Jan Montag">
    </div>
    <!-- /Die H-Karte Junge -->

    <!-- 
      ===========================================================
      HUMAN.TXT & TECH STACK
      Architect: Jan Montag
      Engine: NoCMS (Custom Flat-File PHP)
      Server: Caddy / Docker / ARM64 Pi
      Focus: Road Cycling, Web & Pure Minimalism
      ===========================================================
    -->
    <meta name="author" content="Jan Montag">
    <meta name="description" content="Minimalistisches Weblog von Jan Montag.">

    <!-- IndieWeb & GitHub Verifizierung -->
    <link rel="me" href="https://github.com/thafaker">
    <link rel="me" href="mailto:hallo@wochenstart.com">
    <link rel="me" href="mailto:hallo@herrmontag.de">
    <!-- webmention -->
    <link rel="pingback" href="https://webmention.io/janmontag.de/xmlrpc" />
    <link rel="webmention" href="https://webmention.io/janmontag.de/webmention" />
    <!--// webmention -->

    <!-- Performance & DNS-Prefetch für deine Micro-Netzwerke -->
    <link rel="dns-prefetch" href="//herrmontag.de">
    <link rel="dns-prefetch" href="//status.herrmontag.de">
    <link rel="dns-prefetch" href="//umami.wochenstart.com">
    <link rel="dns-prefetch" href="//wochenstart.com">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Theme-CSS -->
    <link rel="stylesheet" href="/assets/style.css?v=<?= filemtime(__DIR__ . '/../assets/style.css') ?>">
    
    <!-- Analytics -->
    <script defer src="https://umami.wochenstart.com/script.js" data-website-id="5e4aceef-d428-4ef3-958d-df37654a6d59"></script>
</head>
<body class="<?php echo htmlspecialchars($bodyClass); ?>">