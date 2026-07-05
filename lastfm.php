<?php

header('Content-Type: application/json');
header('Cache-Control: max-age=30');

// ─── .env laden ──────────────────────────────────────────
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Kommentare und leere Zeilen überspringen
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// ─── API-Key aus Umgebung holen ────────────────────────
$apikey = getenv('LASTFM_API_KEY');

if (!$apikey) {
    http_response_code(500);
    echo json_encode([
        'error' => 'API-Key nicht konfiguriert (LASTFM_API_KEY in .env fehlt)'
    ]);
    exit;
}

// ─── Last.fm abfragen ──────────────────────────────────
$user = 'thafaker_de';

$url = sprintf(
    'https://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=%s&api_key=%s&format=json&limit=1',
    urlencode($user),
    $apikey
);

$json = @file_get_contents($url);

if ($json === false) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Last.fm nicht erreichbar'
    ]);
    exit;
}

$data = json_decode($json, true);

if (!isset($data['recenttracks']['track'][0])) {
    echo json_encode([
        'error' => 'Kein Titel gefunden'
    ]);
    exit;
}

$track = $data['recenttracks']['track'][0];

echo json_encode([
    'artist' => $track['artist']['#text'] ?? '',
    'title' => $track['name'] ?? '',
    'album' => $track['album']['#text'] ?? '',
    'nowplaying' => isset($track['@attr']['nowplaying'])
]);