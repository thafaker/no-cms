<?php
// header.php of NoCMS Jan Montag 2026
$bodyClass = $bodyClass ?? '';
$pageTitle = $pageTitle ?? 'NoCMS Project';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Google Fonts: Inter für das moderne, saubere Schriftbild -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Prism.js Support für Code-Syntax-Highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js" defer></script>
    
    <!-- Das ausgelagerte Theme-CSS -->
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="<?php echo htmlspecialchars($bodyClass); ?>">