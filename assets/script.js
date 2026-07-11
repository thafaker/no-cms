// Hallo, all unser Javascript hier rein geballert was wöhrend
// der Refakturierung entstand. Ich bin gespannt, wie weit wir kommen.
// Und mit wir meine ich mich.

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

// Theme-Umschalter (Light / Dark Mode)
document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("themeToggle");
    if (toggleBtn) {
        // 1. Beim Laden: Gespeichertes Theme auslesen oder Standard (dark) setzen
        const savedTheme = localStorage.getItem("theme") || "dark";
        document.documentElement.setAttribute("data-theme", savedTheme);
        
        // Button-Emoji initial anpassen
        toggleBtn.innerHTML = savedTheme === "light" ? "☀️ Mode" : "🌓 Mode";

        // 2. Klick-Event
        toggleBtn.addEventListener("click", function (e) {
            e.preventDefault();
            
            const currentTheme = document.documentElement.getAttribute("data-theme") || "dark";
            const newTheme = currentTheme === "dark" ? "light" : "dark";
            
            // Attribut auf html-Tag setzen & im Browser speichern
            document.documentElement.setAttribute("data-theme", newTheme);
            localStorage.setItem("theme", newTheme);
            
            // Emoji live wechseln
            toggleBtn.innerHTML = newTheme === "light" ? "☀️ Mode" : "🌓 Mode";
            
            // Optional: Prism.js Code-Highlighting Theme live austauschen
            const prismLink = document.getElementById("prism-css");
            if (prismLink) {
                const newPrismStyle = newTheme === "light" ? "prism" : "prism-tomorrow";
                prismLink.href = `https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/${newPrismStyle}.min.css`;
            }
        });
    }
});
