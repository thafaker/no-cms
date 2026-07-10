<?php
// Dies ist ein ganz einfacher Counter der bei Aufruf einfach um eins inkrementiert. 
// Kein Tracking. Kein Schwindel. Einfach nur echte Gefühle.

$file = __DIR__ . '/counter.txt';

// Versuche, die Datei zu öffnen
$fp = @fopen($file, 'c+');

if (!$fp) {
    if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Datei konnte nicht geöffnet werden']);
        exit;
    } else {
        echo "0";
        return;
    }
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

// PRÜFUNG: Wurde die Datei direkt aufgerufen oder per include geladen?
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    // Direkter Aufruf (z.B. als API) -> Sende echtes JSON
    header('Content-Type: application/json');
    echo json_encode(['count' => $count]);
} else {
    // Per include im Footer geladen -> Gib nur die reine Zahl für das HTML aus
    echo number_format($count, 0, ',', '.'); 
}