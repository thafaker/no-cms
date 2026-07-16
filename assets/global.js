// assets/global.js

async function loadTrack() {
    const trackSpan = document.getElementById("lastfm-track");
    const widget = document.getElementById("lastfm-box");
    if (!trackSpan || !widget) return;

    try {
        const response = await fetch("/lastfm.php");
        const data = await response.json();

        if (data.artist && data.title) {
            let text = `${data.artist} — ${data.title}`;
            const icon = widget.querySelector('.now-playing-icon');
            
            if (data.nowplaying) {
                if (icon) icon.style.opacity = "1";
            } else {
                if (icon) icon.style.opacity = "0.5";
                text += " (zuletzt gehört)";
            }
            
            trackSpan.textContent = text;
            widget.style.display = 'block';
        }
    } catch (err) {
        console.log("Last.fm konnte nicht geladen werden.");
    }
}

async function loadCounter() {
    const counterEl = document.getElementById("counter");
    if (!counterEl) return;
    try {
        const response = await fetch("/counter.php");
        const data = await response.json();
        counterEl.textContent = data.count;
    } catch {
        counterEl.textContent = "?";
    }
}

// Wartet, bis der Browser im Leerlauf ist (schont den kritischen Pfad komplett)
window.addEventListener("load", function () {
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => {
            loadTrack();
            loadCounter();
        });
    } else {
        setTimeout(() => {
            loadTrack();
            loadCounter();
        }, 200);
    }
});