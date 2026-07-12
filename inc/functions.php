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
        if (in_array($filename, ['links.md', 'about.md', 'not-found.md'])) {
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

/**
 * Webmentions Toilette Empfangung.
 */
function getWebmentions($pageUrl) {
    // Falls du lokal testest, webmention.io braucht die echte Live-Domain
    if (str_contains($pageUrl, 'localhost') || str_contains($pageUrl, '127.0.0.1')) {
        $pageUrl = str_replace(['http://localhost', 'http://127.0.0.1'], 'https://janmontag.de', $pageUrl);
    }

    $apiUrl = "https://webmention.io/api/mentions.jf2?target=" . urlencode($pageUrl);
    
    // 5 Sekunden Timeout, damit deine Seite nicht blockiert, falls die API lahmt
    $context = stream_context_create([
        'http' => ['timeout' => 5, 'user_agent' => 'NoCMS-Webmention-Fetcher/1.0']
    ]);
    
    $response = @file_get_contents($apiUrl, false, $context);
    if (!$response) return [];
    
    $data = json_decode($response, true);
    return $data['children'] ?? [];
}

/**
 * Durchsucht ein Ziel nach Webmention-Endpoints (In Headers oder HTML)
 */
function discoverWebmentionEndpoint($targetUrl) {
    $context = stream_context_create([
        'http' => ['timeout' => 6, 'user_agent' => 'NoCMS-Webmention-Sender/1.0']
    ]);
    
    // Seite abrufen
    $html = @file_get_contents($targetUrl, false, $context);
    if (!$html) return null;

    // 1. Check in den HTTP-Response-Headers (falls übergeben)
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/^Link:\s*<([^>]+)>;\s*rel="([^"]+)"/i', $header, $matches)) {
                if (str_contains($matches[2], 'webmention')) {
                    return $matches[1];
                }
            }
        }
    }

    // 2. Check im HTML via Regex (Sucht nach rel="webmention" oder rel="http://webmention.org/")
    if (preg_match('#<link\s+[^>]*href="([^"]+)"\s+[^>]*rel="(http://webmention\.org/|webmention)"#i', $html, $matches) ||
        preg_match('#<link\s+[^>]*rel="(http://webmention\.org/|webmention)"\s+[^>]*href="([^"]+)"#i', $html, $matches)) {
        return end($matches);
    }

    return null;
}

/**
 * Sendet eine Webmention von deiner Source-URL zur Target-URL
 */
function sendWebmention($sourceUrl, $targetUrl) {
    $endpoint = discoverWebmentionEndpoint($targetUrl);
    if (!$endpoint) return false;

    $data = http_build_query(['source' => $sourceUrl, 'target' => $targetUrl]);
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                         "User-Agent: NoCMS-Webmention-Sender/1.0\r\n",
            'content' => $data,
            'timeout' => 6
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($endpoint, false, $context);
    
    return $response !== false;
}

/**
 * Automatisch alle externen Links aus dem Post extrahieren und anpingen
 */
function sendOutgoingWebmentionsFromHtml($htmlContent, $sourceUrl) {
    // Alle href-Attribute aus dem HTML fischen
    preg_match_all('/<a[^>]+href=["\']([^"\']+)["\']/i', $htmlContent, $matches);
    if (empty($matches[1])) return;

    foreach ($matches[1] as $url) {
        // Nur echte externe HTTP/HTTPS Links anpingen (nicht deine eigenen, keine Anker)
        if (str_starts_with($url, 'http') && !str_contains($url, 'janmontag.de')) {
            // Sende im Hintergrund (Fehler ignorieren wir geräuschlos)
            sendWebmention($sourceUrl, $url);
        }
    }
}