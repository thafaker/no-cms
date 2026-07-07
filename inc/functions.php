<?php
// Functions.php von Jan Montag 2026
// composer autoloader - hoffentlich klappt das
// In November
require_once __DIR__ . '/../vendor/autoload.php';

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Wurstgesicht des Todes
 * Lädt alle Posts aus dem posts/-Verzeichnis
 * und sortiert sie nach dem Datum aus dem Frontmatter (neueste zuerst).
 * Fallback: falls kein Datum im Frontmatter, wird filemtime verwendet.
 */

function getPosts(): array {
    $files = glob(__DIR__ . '/../posts/*.md');
    
    // Array für Posts mit Datum
    $posts = [];
    
    foreach ($files as $file) {
        $filename = basename($file);
        
        // NEU: Statische Seiten und Systemdateien aus den Blogposts filtern
        if (in_array($filename, ['about.md', 'not-found.md'])) {
            continue; // Überspringt diese Datei, sie wird nicht ins Array aufgenommen
        }

        $content = file_get_contents($file);
        $parsed = parseFrontmatter($content);
        $meta = $parsed['meta'];
        
        // Datum aus Frontmatter oder Fallback auf filemtime
        if (!empty($meta['date'])) {
            $date = strtotime($meta['date']);
        } else {
            $date = filemtime($file);
        }
        
        $posts[] = [
            'file' => $file,
            'date' => $date
        ];
    }
    
    // Nach Datum sortieren (neueste zuerst)
    usort($posts, function ($a, $b) {
        return $b['date'] <=> $a['date'];
    });
    
    // Nur die Dateipfade zurückgeben (für Kompatibilität)
    return array_column($posts, 'file');
}

/**
 * Parst Frontmatter (YAML-ähnlich) aus dem Markdown-Inhalt.
 * Gibt ein Array zurück: ['meta' => [...], 'content' => '...']
 */
function parseFrontmatter(string $content): array {
    $meta = [];
    $body = $content;

    if (preg_match('/^---\s*\R(.*?)\R---\s*\R(.*)$/s', $content, $matches)) {
        $rawMeta = trim($matches[1]);
        $body = trim($matches[2]);
        $lines = preg_split('/\R/', $rawMeta);
        $currentKey = null;

        foreach ($lines as $line) {
            // Listeneinträge (z.B. tags: - php - css)
            if (preg_match('/^\s*-\s*(.+)$/', $line, $m) && $currentKey) {
                $meta[$currentKey][] = trim($m[1]);
                continue;
            }
            // Schlüssel-Wert-Paare
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $currentKey = $key;
                $meta[$key] = $value === '' ? [] : $value;
            }
        }
    }

    return ['meta' => $meta, 'content' => $body];
}

/**
 * Erstellt einen konfigurierten CommonMark-Converter.
 */
function createMarkdownConverter(): MarkdownConverter {
    $environment = new Environment([
        'html_input' => 'allow',  // ← HTML erlauben!
    ]);
    $environment->addExtension(new CommonMarkCoreExtension());
    $environment->addExtension(new GithubFlavoredMarkdownExtension());
    $environment->addExtension(new FootnoteExtension());
    return new MarkdownConverter($environment);
}
