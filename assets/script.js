// Last.fm – aktueller Track
async function loadTrack() {
    try {
        const response = await fetch("/lastfm.php");
        const data = await response.json();

        if (data.error) {
            document.getElementById("track").textContent = data.error;
            return;
        }

        const prefix = data.nowplaying ? "♫ " : "zuletzt: ";
        document.getElementById("track").textContent =
            `${prefix}${data.artist} — ${data.title}`;
    } catch {
        document.getElementById("track").textContent = "offline";
    }
}

// Besucherzähler
async function loadCounter() {
    try {
        const response = await fetch("/counter.php");
        const data = await response.json();
        document.getElementById("counter").textContent = data.count;
    } catch {
        document.getElementById("counter").textContent = "?";
    }
}

// Starte die Funktionen, sobald die Seite geladen ist
document.addEventListener("DOMContentLoaded", function () {
    // Prüfe, ob die Elemente auf der Seite existieren (nur in index.php)
    if (document.getElementById("track")) {
        loadTrack();
        setInterval(loadTrack, 30000); // alle 30 Sekunden aktualisieren
    }

    if (document.getElementById("counter")) {
        loadCounter();
    }
});