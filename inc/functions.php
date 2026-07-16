<?php
// Functions.php von Jan Montag 2026
require_once __DIR__ . '/../vendor/autoload.php';

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\MarkdownConverter;

// Cache-Verzeichnis anlegen
$cacheDir = __DIR__ . '/../cache/';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0775, true);
}

/**
 * Hilfsfunktion zum Schreiben/Lesen von schnellem JSON-Cache
 */
function getCachedData(string $cacheKey, int $ttlSeconds, callable $fallback) {
    $cacheFile = __DIR__ . '/../cache/' . md5($cacheKey) . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttlSeconds) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    $data = $fallback();
    file_put_contents($cacheFile, json_encode($data));
    return $data;
}

/**
 * Löscht einen spezifischen Cache-Key (Nützlich nach dem Speichern im Admin)
 */
function invalidateCache(string $cacheKey) {
    $cacheFile = __DIR__ . '/../cache/' . md5($cacheKey) . '.json';
    if (file_exists($cacheFile)) {
        @unlink($cacheFile);
    }
}

/**
 * Lädt alle Posts aus dem posts/-Verzeichnis.
 * Nutzt intelligenten Cache: Prüft den mtime des posts-Ordners.
 */
function getPosts(): array {
    $postsDir = __DIR__ . '/../posts/';
    $cacheFile = __DIR__ . '/../cache/posts_cache_index.json';
    
    $postsDirMtime = file_exists($postsDir) ? filemtime($postsDir) : 0;
    
    if (file_exists($cacheFile) && filemtime($cacheFile) >= $postsDirMtime) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached)) {
            return $cached;
        }
    }
    
    $files = glob($postsDir . '*.md');
    $posts = [];
    
    foreach ($files as $file) {
        $filename = basename($file);
        if (in_array($filename, ['links.md', 'about.md', 'not-found.md'])) {
            continue; 
        }

        $content = file_get_contents($file);
        $parsed = parseFrontmatter($content);
        $meta = $parsed['meta'];
        
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
    
    usort($posts, function ($a, $b) {
        return $b['date'] <=> $a['date'];
    });
    
    $result = array_column($posts, 'file');
    file_put_contents($cacheFile, json_encode($result));
    return $result;
}

/**
 * Parst Frontmatter (YAML-ähnlich) aus dem Markdown-Inhalt.
 * Interner statischer Laufzeitcache, damit mehrfaches Parsen im selben Request gratis ist.
 */
function parseFrontmatter(string $content): array {
    static $runtimeCache = [];
    $hash = md5($content);
    if (isset($runtimeCache[$hash])) {
        return $runtimeCache[$hash];
    }

    $meta = [];
    $body = $content;

    if (preg_match('/^---\s*\R(.*?)\R---\s*\R(.*)$/s', $content, $matches)) {
        $rawMeta = trim($matches[1]);
        $body = trim($matches[2]);
        $lines = preg_split('/\R/', $rawMeta);
        $currentKey = null;

        foreach ($lines as $line) {
            if (preg_match('/^\s*-\s*(.+)$/', $line, $m) && $currentKey) {
                $meta[$currentKey][] = trim($m[1]);
                continue;
            }
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $currentKey = $key;
                $meta[$key] = $value === '' ? [] : $value;
            }
        }
    }

    $result = ['meta' => $meta, 'content' => $body];
    $runtimeCache[$hash] = $result;
    return $result;
}

/**
 * Erstellt einen konfigurierten CommonMark-Converter.
 */
function createMarkdownConverter(): MarkdownConverter {
    static $converter = null;
    if ($converter === null) {
        $environment = new Environment([
            'html_input' => 'allow',
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new FootnoteExtension());
        $converter = new MarkdownConverter($environment);
    }
    return $converter;
}

/**
 * Holt Webmentions mit aggressivem Caching (15 Minuten / 900 Sek).
 */
function getWebmentions($pageUrl) {
    if (str_contains($pageUrl, 'localhost') || str_contains($pageUrl, '127.0.0.1')) {
        $pageUrl = str_replace(['http://localhost', 'http://127.0.0.1'], 'https://janmontag.de', $pageUrl);
    }

    return getCachedData('webmentions_' . $pageUrl, 900, function() use ($pageUrl) {
        $apiUrl = "https://webmention.io/api/mentions.jf2?target=" . urlencode($pageUrl);
        $context = stream_context_create([
            'http' => ['timeout' => 3, 'user_agent' => 'NoCMS-Webmention-Fetcher/1.0']
        ]);
        $response = @file_get_contents($apiUrl, false, $context);
        if (!$response) return [];
        $data = json_decode($response, true);
        return $data['children'] ?? [];
    });
}

/**
 * Durchsucht ein Ziel nach Webmention-Endpoints
 */
function discoverWebmentionEndpoint($targetUrl) {
    $context = stream_context_create([
        'http' => ['timeout' => 4, 'user_agent' => 'NoCMS-Webmention-Sender/1.0']
    ]);
    
    $html = @file_get_contents($targetUrl, false, $context);
    if (!$html) return null;

    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/^Link:\s*<([^>]+)>;\s*rel="([^"]+)"/i', $header, $matches)) {
                if (str_contains($matches[2], 'webmention')) {
                    return $matches[1];
                }
            }
        }
    }

    if (preg_match('#<link\s+[^>]*href="([^"]+)"\s+[^>]*rel="(http://webmention\.org/|webmention)"#i', $html, $matches) ||
        preg_match('#<link\s+[^>]*rel="(http://webmention\.org/|webmention)"\s+[^>]*href="([^"]+)"#i', $html, $matches)) {
        return end($matches);
    }

    return null;
}

/**
 * Sendet eine Webmention
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
            'timeout' => 4
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
    preg_match_all('/<a[^>]+href=["\']([^"\']+)["\']/i', $htmlContent, $matches);
    if (empty($matches[1])) return;

    foreach ($matches[1] as $url) {
        if (str_starts_with($url, 'http') && !str_contains($url, 'janmontag.de')) {
            sendWebmention($sourceUrl, $url);
        }
    }
}