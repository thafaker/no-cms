<?php
// admin.php - kleines Admin Interface für mein NoCMS (C) Jan Montag 2026
require_once __DIR__ . '/inc/functions.php';

$postsDir = __DIR__ . '/posts/';
$imagesDir = __DIR__ . '/images/';

// Sicherstellen, dass die Ordner existieren
if (!is_dir($postsDir)) mkdir($postsDir, 0775, true);
if (!is_dir($imagesDir)) mkdir($imagesDir, 0775, true);

// Helferfunktion: Text für Dateinamen bereinigen (Slug)
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'beitrag' : $text;
}

// ==========================================
// 1. API-ENDPUNKTE (Geben JSON zurück)
// ==========================================

// A. BILD-UPLOAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'upload_image') {
    header('Content-Type: application/json');
    if (!isset($_FILES['image'])) {
        echo json_encode(['error' => 'Keine Datei empfangen']);
        exit;
    }
    $filename = basename($_FILES['image']['name']);
    if (move_uploaded_file($_FILES['image']['tmp_name'], $imagesDir . $filename)) {
        echo json_encode([
            'success' => true,
            'markdown' => '![Beschreibung](/images/' . $filename . ')'
        ]);
    } else {
        echo json_encode(['error' => 'Fehler beim Speichern des Bildes']);
    }
    exit;
}

// B. POST LADEN (FÜR BEITRAGS-EDITIERUNG)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'load_post') {
    header('Content-Type: application/json');
    $file = basename($_GET['file'] ?? '');
    $path = $postsDir . $file;
    if ($file && is_file($path)) {
        $rawContent = file_get_contents($path);
        
        // Nutzt deine originale Funktion aus functions.php!
        $parsed = parseFrontmatter($rawContent);
        
        // Tags wieder kommagetrennt zusammenbauen, falls sie ein Array sind
        $tagsStr = '';
        if (!empty($parsed['meta']['tags'])) {
            $tagsStr = is_array($parsed['meta']['tags']) ? implode(', ', $parsed['meta']['tags']) : $parsed['meta']['tags'];
        }

        echo json_encode([
            'success' => true,
            'filename' => str_replace('.md', '', $file),
            'title' => $parsed['meta']['title'] ?? '',
            'date' => $parsed['meta']['date'] ?? '',
            'tags' => $tagsStr,
            'content' => $parsed['content']
        ]);
    } else {
        echo json_encode(['error' => 'Datei nicht gefunden']);
    }
    exit;
}

// ==========================================
// 2. NORMALER FORMULAR-SUBMIT (POST SPEICHERN)
// ==========================================
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = stripslashes($_POST['title']);
    $filenameInput = trim($_POST['filename'] ?? '');
    $isEdit = !empty($_POST['is_edit_mode']);
    
    // Automatische Namensgenerierung mit aktuellem Tag, falls leergelassen
    if (empty($filenameInput)) {
        $filename = date('Y-m-d') . '-' . slugify($title) . '.md';
    } else {
        $filenameInput = str_replace('.md', '', $filenameInput);
        $filename = ($isEdit) ? $filenameInput . '.md' : slugify($filenameInput) . '.md';
    }
    
    $date = $_POST['date'] ?: date('Y-m-d H:i');
    $tags = $_POST['tags'] ?? '';
    $content = stripslashes($_POST['content'] ?? '');

    // Frontmatter generieren
    $fileContent = "---\n";
    $fileContent .= "title: " . $title . "\n";
    $fileContent .= "date: $date\n";
    if (!empty($tags)) {
        $fileContent .= "tags:\n";
        foreach (explode(',', $tags) as $tag) {
            $tagTrimmed = trim($tag);
            if ($tagTrimmed !== '') {
                $fileContent .= "  - " . $tagTrimmed . "\n";
            }
        }
    }
    $fileContent .= "---\n\n" . $content;

    if (file_put_contents($postsDir . $filename, $fileContent) !== false) {
        $message = "<p style='color: var(--accent); font-weight: bold;'>$ [SUCCESS] '$filename' erfolgreich gespeichert!</p>";
        
        // ==========================================================================
        // OUTGOING WEBMENTIONS DIREKT BEIM SPEICHERN BEHANDELN
        // ==========================================================================
        if (!str_contains($_SERVER['HTTP_HOST'], 'localhost') && !str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')) {
            $cleanSlug = preg_replace('/^\d{4}-\d{2}-\d{2}-/', '', str_replace('.md', '', $filename));
            $sourceUrl = "https://janmontag.de/" . $cleanSlug;
            
            // Markdown kurz über den Compiler jagen, um Links zu extrahieren
            $converter = createMarkdownConverter();
            $htmlContent = html_entity_decode($converter->convert($content)->getContent());
            
            // Webmentions absenden
            sendOutgoingWebmentionsFromHtml($htmlContent, $sourceUrl);
        }
        // ==========================================================================
        
        $_POST = []; // Formular leeren
    } else {
        $message = "<p style='color: var(--prompt); font-weight: bold;'>$ [ERROR] Schreiben fehlgeschlagen.</p>";
    }
}

// Alle existierenden Markdown-Dateien für die Liste holen
$existingFiles = array_map('basename', glob($postsDir . '*.md'));
rsort($existingFiles); // Neueste Dateien nach oben
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Admin · JanMontag</title>
    <style>
        /* Standardmäßig: Dark Mode (Terminal klassisch) */
        :root {
            --bg: #0a0a0a;
            --bg-secondary: #1a1a1a;
            --text: #e6e6e6;
            --text-muted: #999;
            --accent: #6ee7b7;
            --prompt: #ff5c5c;
            --border: #333;
        }

        /* Light Mode: Klares, helles Entwickler-Theme */
        :root.light-mode {
            --bg: #f5f5f5;
            --bg-secondary: #ffffff;
            --text: #1a1a1a;
            --text-muted: #666;
            --accent: #059669;
            --prompt: #dc2626;
            --border: #ccc;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'SF Mono', 'Fira Code', 'Courier New', monospace;
            padding: 2rem;
            line-height: 1.5;
            transition: background 0.2s, color 0.2s;
        }
        .container { max-width: 850px; margin: 0 auto; position: relative; }
        
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        h1 { color: var(--text); font-size: 1.4rem; text-transform: uppercase; letter-spacing: 0.05em; }
        
        .theme-toggle {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
            cursor: pointer;
            border-radius: 4px;
            font-family: inherit;
        }
        .theme-toggle:hover {
            border-color: var(--accent);
            color: var(--text);
        }

        .status-msg { margin-bottom: 1.5rem; padding: 0.5rem; background: var(--bg-secondary); border-left: 3px solid var(--accent); }
        
        label { display: block; margin-bottom: 0.4rem; color: var(--text-muted); font-size: 0.9rem; }
        input[type="text"], textarea, select {
            width: 100%;
            background: var(--bg-secondary);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 0.6rem;
            margin-bottom: 1.2rem;
            font-family: inherit;
            font-size: 1rem;
            border-radius: 4px;
        }
        input[type="text"]:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent);
        }
        
        input[readonly] {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .row { display: flex; gap: 1rem; }
        .col { flex: 1; }
        
        button[type="submit"] {
            background: var(--accent);
            color: #000;
            padding: 0.7rem 1.5rem;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-family: inherit;
            font-size: 1rem;
            border-radius: 4px;
        }
        button[type="submit"]:hover { opacity: 0.9; }
        
        .upload-box {
            background: var(--bg-secondary);
            border: 2px dashed var(--border);
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }
        .upload-box.dragover { border-color: var(--accent); background: var(--bg-secondary); opacity: 0.8; }
        #imageInput { display: none; }
        .upload-label { cursor: pointer; color: var(--accent); text-decoration: underline; }
        #uploadStatus { margin-top: 0.5rem; font-size: 0.9rem; color: var(--text-muted); }
    </style>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
        })();
    </script>
</head>
<body>
<div class="container">
    <div class="header-row">
        <h1>$ Jan Montags admin.php</h1>
        <button type="button" class="theme-toggle" id="themeToggle">[ ☀️ Light ]</button>
    </div>
    
    <?php if (!empty($message)) echo '<div class="status-msg">' . $message . '</div>'; ?>

    <label>📂 Vorhandenen Beitrag bearbeiten</label>
    <select id="postSelector">
        <option value="">-- Neuen Beitrag erstellen --</option>
        <?php foreach ($existingFiles as $file): ?>
            <option value="<?= htmlspecialchars($file) ?>"><?= htmlspecialchars($file) ?></option>
        <?php endforeach; ?>
    </select>

    <div class="upload-box" id="dropZone">
        <p>📷 drag & drop bilder hierhin oder <label for="imageInput" class="upload-label">durchsuchen</label></p>
        <input type="file" id="imageInput" accept="image/*">
        <div id="uploadStatus">bereit für upload</div>
    </div>

    <form method="POST" action="admin.php">
        <input type="hidden" name="is_edit_mode" id="isEditMode" value="0">
        <div class="row">
            <div class="col">
                <label>$ filename (leer lassen für Auto-Dateiname mit Datum)</label>
                <input type="text" name="filename" id="formFilename" placeholder="auto-generiert: YYYY-MM-DD-titel.md" value="<?= htmlspecialchars($_POST['filename'] ?? '') ?>">
            </div>
            <div class="col">
                <label>$ title</label>
                <input type="text" name="title" id="formTitle" placeholder="Titel des Beitrags" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <label>$ date (optional)</label>
                <input type="text" name="date" id="formDate" placeholder="<?= date('Y-m-d H:i') ?>" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
            </div>
            <div class="col">
                <label>$ tags (kommagetrennt)</label>
                <input type="text" name="tags" id="formTags" placeholder="php, design, caddy" value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>">
            </div>
        </div>

        <label>$ cat content.md</label>
        <textarea name="content" id="contentArea" rows="18" placeholder="Schreibe deinen Markdown-Text hier..." required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>

        <button type="submit" id="submitBtn">💾 Beitrag speichern</button>
    </form>
</div>

<script>
const imageInput = document.getElementById('imageInput');
const dropZone = document.getElementById('dropZone');
const uploadStatus = document.getElementById('uploadStatus');
const contentArea = document.getElementById('contentArea');
const postSelector = document.getElementById('postSelector');

// Formular-Felder
const formFilename = document.getElementById('formFilename');
const formTitle = document.getElementById('formTitle');
const formDate = document.getElementById('formDate');
const formTags = document.getElementById('formTags');
const submitBtn = document.getElementById('submitBtn');

// ==========================================
// THEME SWITCHER HANDLER
// ==========================================
const themeToggle = document.getElementById('themeToggle');

function updateToggleButton() {
    if (document.documentElement.classList.contains('light-mode')) {
        themeToggle.textContent = '[ 🌙 Dark ]';
    } else {
        themeToggle.textContent = '[ ☀️ Light ]';
    }
}

updateToggleButton();

themeToggle.addEventListener('click', () => {
    document.documentElement.classList.toggle('light-mode');
    if (document.documentElement.classList.contains('light-mode')) {
        localStorage.setItem('theme', 'light');
    } else {
        localStorage.setItem('theme', 'dark');
    }
    updateToggleButton();
});

// ==========================================
// BEITRAGS-LOADER (EDITIEREN VIA SELECT)
// ==========================================
postSelector.addEventListener('change', function() {
    const selectedFile = this.value;
    const isEditMode = document.getElementById('isEditMode');
    
    if (!selectedFile) {
        formFilename.value = '';
        formFilename.removeAttribute('readonly');
        isEditMode.value = "0";
        formTitle.value = '';
        formDate.value = '';
        formTags.value = '';
        contentArea.value = '';
        submitBtn.textContent = '💾 Beitrag speichern';
        return;
    }
    
    submitBtn.textContent = '🔄 Lade Beitrag...';
    
    fetch('admin.php?action=load_post&file=' + encodeURIComponent(selectedFile))
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                formFilename.value = data.filename;
                formFilename.setAttribute('readonly', 'true');
                isEditMode.value = "1";
                formTitle.value = data.title;
                formDate.value = data.date;
                formTags.value = data.tags;
                contentArea.value = data.content;
                submitBtn.textContent = '💾 Änderungen speichern';
            } else {
                alert('Fehler beim Laden: ' + data.error);
            }
        });
});

// ==========================================
// DRAG & DROP & UPLOAD HANDLERS
// ==========================================
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, (e) => { e.preventDefault(); dropZone.classList.add('dragover'); }, false);
});
['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, (e) => { e.preventDefault(); dropZone.classList.remove('dragover'); }, false);
});

dropZone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].type.startsWith('image/')) {
        uploadFile(files[0]);
    }
});

imageInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        uploadFile(this.files[0]);
    }
});

function uploadFile(file) {
    uploadStatus.textContent = '⏳ Lade "' + file.name + '" hoch...';
    uploadStatus.style.color = 'var(--text-muted)';
    
    const formData = new FormData();
    formData.append('image', file);

    fetch('admin.php?action=upload_image', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            uploadStatus.textContent = '✅ "' + file.name + '" hochgeladen!';
            uploadStatus.style.color = 'var(--accent)';
            insertAtCursor(contentArea, '\n' + data.markdown + '\n');
        } else {
            uploadStatus.textContent = '❌ Fehler: ' + data.error;
            uploadStatus.style.color = 'var(--prompt)';
        }
    })
    .catch(() => {
        uploadStatus.textContent = '❌ Serverfehler beim Upload';
        uploadStatus.style.color = 'var(--prompt)';
    });
}

// Cursor Insertion Utility
function insertAtCursor(textarea, text) {
    const startPos = textarea.selectionStart;
    const endPos = textarea.selectionEnd;
    const scrollTop = textarea.scrollTop;
    textarea.value = textarea.value.substring(0, startPos) + text + textarea.value.substring(endPos, textarea.value.length);
    textarea.focus();
    textarea.selectionStart = startPos + text.length;
    textarea.selectionEnd = startPos + text.length;
    textarea.scrollTop = scrollTop;
}
</script>
</body>
</html>