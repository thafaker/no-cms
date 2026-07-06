<?php
// about.php of NoCMS Jan Montag 2026
// The sore in my soul
// The mark in my heart
// Her acid reign
require_once 'inc/functions.php';

$posts = getPosts();

$pageTitle = 'about · JanMontag.de';
require 'inc/header.php';
?>
<!-- lost and dumbfunded -->
<div class="status" style="display: flex; justify-content: space-between; align-items: center;">
    <span>about 404 — Gone</span>
    <span id="themeToggle" style="cursor: pointer; font-size: 0.9rem; text-transform: none; letter-spacing: normal;">$ theme --toggle</span>
</div>
<div class="command-wrapper">
    <div class="command">
        <span class="prompt">$</span> rm -rf /
    </div>
</div>

<div class="counter">
	<p>Hier beschreibe ich, was es mit dieser Seite auf sich hat.<p>
	<p>Ich hoste viele Dinge selbst, das finde ich spannend. Es ist <br />
	ein Hobby und es passt gut in den Zeitgeist. Allerdings birgt <br />
	das auch immer die Gefahr, dass etwas bricht. Und weil man für <br />
	alles selbst zuständig ist, kann man nicht alle Fälle immer gut <br />
	abdecken. Ich hatte einen Festplattencrash. Und versuchte mich an <br />
	der Wiederherstellung der Daten. Und weil einige meiner Seiten und <br />
	Dienste nicht funktionieren, unter anderem auch janmontag.de, <br />
	entschied ich eine kleine Seite dafür zu screiben, die andeutet,<br />
	das alles im Argen liegt. Deshalb heißt diese Website auch historisch <br />
	bedingt immer noch 404 - gone und rm -rf / was der allgemeine unixartige <br />
	Befehl ist, um als root seine gesamte Festplatte zu löschen. <br />
	Dann habe ich aber so viel Gefallen an der kleinen einzelnen HTML- <br />
	Datei gefunden, dass ich immer weiter gemacht habe. So habe ich <br />
	den aktuellen Song den ich gerade höre von Last.fm eingebunden. Oder <br />
	einen ganz simplen Counter. Usw. usf... und irgendwann wurde ein <br />
	kleines Faltfile CMS daraus, ich habe es NoCMS getauft. Denn es ist <br />
	nur PHP, HTML und ein Ordner /posts in dem Markdown-Dateien liegen <br />
	die dann durch eine Javascript-Klasse (ich habe den Namen vergessen) <br />
	gerendert werden. Dann habe ich ein kleines admin.php Interface <br />
	gebaut damit man Artikel über das Web schreiben und editieren kann, <br />
	und um Bilder hochladen zu können.</p>
	<p> Das wars, und nun stehen wir hier.</p>
	<p>Jan Montag</p>
</div>

<p style="margin-top:2rem;">
    <a href="/">cd ..</a>
</p>

<?php include "footer.php"; ?>
    <div class="footer">
        <span class="label">$ wc -l visitors.log</span><br>
        <span id="counter">...</span>
    </div>

<script src="/assets/script.js" defer></script>
<script defer src="https://umami.wochenstart.com/script.js" data-website-id="5e4aceef-d428-4ef3-958d-df37654a6d59"></script>

</body>
</html>
