<?php
// Dies ist ien ganz einfacher Counter der bei Aufruf einfach um eins inkrementiert. 
// Kein Tracking. Kein Schwindel. Einfach nur echte Gefühle.
header('Content-Type: application/json');

$file = __DIR__ . '/counter.txt';

// Versuche, die Datei zu öffnen
$fp = @fopen($file, 'c+');

if (!$fp) {
    http_response_code(500);
    echo json_encode(['error' => 'Datei konnte nicht geöffnet werden']);
    exit;
}

// Sperre und Lese/Schreibe
if (flock($fp, LOCK_EX)) {
    $count = (int)trim(stream_get_contents($fp));
    $count++;
    rewind($fp);
    ftruncate($fp, 0);
    fwrite($fp, (string)$count);
    fflush($fp);
    flock($fp, LOCK_UN);
} else {
    // Falls flock nicht funktioniert, trotzdem versuchen zu schreiben (ohne Sperre)
    $count = (int)trim(stream_get_contents($fp));
    $count++;
    rewind($fp);
    ftruncate($fp, 0);
    fwrite($fp, (string)$count);
}

fclose($fp);

echo json_encode(['count' => $count]);
