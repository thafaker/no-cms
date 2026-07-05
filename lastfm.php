<?php

header('Content-Type: application/json');
header('Cache-Control: max-age=30');

$user = 'thafaker_de';
$apikey = '5daac31eaadb5fe008c5e1ce3348b0a7';

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

if (
    !isset($data['recenttracks']['track'][0])
) {
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
