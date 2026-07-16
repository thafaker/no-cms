<?php
header('Content-Type: application/json');
header('Cache-Control: public, max-age=30');

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$apikey = getenv('LASTFM_API_KEY');
if (!$apikey) {
    http_response_code(500);
    echo json_encode(['error' => 'API-Key nicht konfiguriert']);
    exit;
}

// Serverseitiger Cache (60 Sekunden TTL)
$cacheFile = __DIR__ . '/cache/lastfm_cache.json';
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 60) {
    echo file_get_contents($cacheFile);
    exit;
}

$user = 'thafaker_de';
$url = sprintf(
    'https://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=%s&api_key=%s&format=json&limit=1',
    urlencode($user),
    $apikey
);

$context = stream_context_create(['http' => ['timeout' => 3]]);
$json = @file_get_contents($url, false, $context);

if ($json === false) {
    // Fallback auf alten Cache bei API-Ausfall
    if (file_exists($cacheFile)) {
        echo file_get_contents($cacheFile);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Last.fm nicht erreichbar']);
    }
    exit;
}

$data = json_decode($json, true);
if (!isset($data['recenttracks']['track'][0])) {
    echo json_encode(['error' => 'Kein Titel gefunden']);
    exit;
}

$track = $data['recenttracks']['track'][0];
$responsePayload = json_encode([
    'artist' => $track['artist']['#text'] ?? '',
    'title' => $track['name'] ?? '',
    'album' => $track['album']['#text'] ?? '',
    'nowplaying' => isset($track['@attr']['nowplaying'])
]);

// Cache schreiben
file_put_contents($cacheFile, $responsePayload);
echo $responsePayload;