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
    
    <!-- Google Fonts: Inter für das moderne, saubere Schriftbild -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Das ausgelagerte Theme-CSS -->
    <link rel="stylesheet" href="/assets/style.css?v=<?= filemtime(__DIR__ . '/../assets/style.css') ?>">
    <script defer src="https://umami.wochenstart.com/script.js" data-website-id="5e4aceef-d428-4ef3-958d-df37654a6d59"></script>
</head>
<body class="<?php echo htmlspecialchars($bodyClass); ?>">