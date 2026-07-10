---
title: Flat-File-Admin-Panel für PHP-Blogs
date: 2026-07-05 16:50
tags:
  - php
  - webdev
  - markdown
  - flatfile
---

Klassische Content-Management-Systeme (CMS) setzen meist auf eine relationale Datenbank (wie MySQL oder PostgreSQL), um Inhalte, Metadaten und Konfigurationen strukturiert zu speichern. Für minimalistische, textbasierte Weblogs ist dieser Overhead jedoch oft nicht notwendig. Ein Flat-File-Setup, bei dem Beiträge direkt als Plain-Text-Dateien (z. B. im Markdown-Format mit YAML-Frontmatter) auf dem Server liegen, bietet signifikante Vorteile bei der Performance, Portabilität und Versionierung.

Der Nachteil reiner Flat-File-Systeme liegt meist im Workflow: Das Verfassen von Beiträgen erfordert ohne grafische Oberfläche zwingend den Zugriff via SSH, SFTP oder Git. Um diesen Prozess zu vereinfachen, lässt sich eine maßgeschneiderte, schlanke Administrationsoberfläche in einer einzigen PHP-Datei realisieren, die direkt auf das bestehende Dateisystem aufsetzt.

![Beschreibung](/images/admin.php.png)
<small>Picture of Admin Interface admin.php</small>

### Architektur und Funktionsweise

Die Kernarchitektur eines solchen nativen PHP-Panels basiert auf zwei wesentlichen Säulen: asynchroner Datenübertragung (AJAX) und direktem Dateizugriff über POSIX-Dateirechte.

#### 1. Zustandserhaltung und asynchroner Bild-Upload
Ein primäres Problem traditioneller HTML-Formulare ist der Verlust von ungespeicherten Textfelddaten bei einem Page-Reload. Um dieses Problem zu eliminieren, nutzt das Admin-Panel die JavaScript `Fetch API`. 

Wird ein Bild per Drag & Drop oder über den Dateidialog ausgewählt, fängt JavaScript das Event ab und sendet die Binärdaten isoliert als `FormData`-Objekt via HTTP-POST an ein API-Endpunkt-Skript auf dem Server. PHP verschiebt das Bild mittels `move_uploaded_file()` in das Zielverzeichnis (z. B. `/images`) und gibt die relative URL als JSON-Response zurück. JavaScript verarbeitet diese Antwort und fügt die korrekte Markdown-Syntax (`![Beschreibung](/images/dateiname.jpg)`) über die Manipulation der Eigenschaften `selectionStart` und `selectionEnd` präzise an der aktuellen Cursorposition des Textareals ein. Die Seite wird zu keinem Zeitpunkt neu geladen; der geschriebene Text bleibt unberührt.

#### 2. Dateisystem-Interaktion und Case-Sensitivity
Das Skript interagiert über native PHP-Funktionen wie `glob()`, `file_get_contents()` und `file_put_contents()` mit dem Festplattenspeicher. Beim Erstellen eines neuen Beitrags sorgt eine Bereinigungsfunktion (`slugify`) dafür, dass aus dem Freitext-Titel ein valider, URL-konformer Dateiname im Format `YYYY-MM-DD-titel.md` generiert wird.

Hierbei gilt es, die Case-Sensitivity (Unterscheidung zwischen Groß- und Kleinschreibung) von unixoiden Dateisystemen (wie ext4 unter Linux) zu beachten. Würde das Skript bei der Editierung eines bestehenden Beitrags den Dateinamen standardmäßig in Kleinbuchstaben umwandeln, könnte eine Datei wie `2026-07-04-NoCMS.md` nicht überschrieben werden. Stattdessen würde das System eine Dublette namens `2026-07-04-nocms.md` anlegen. Durch die Implementierung eines expliziten Editier-Modus, der die String-Transformation beim Speichern im Bearbeitungsmodus blockiert, wird die exakte Dateistruktur auf Serverebene beibehalten.

### Absicherung auf Server-Ebene (Caddy)

Da eine native PHP-Lösung ohne komplexes Framework bewusst auf ein integriertes Benutzermanagement verzichtet, muss die Zugriffskontrolle auf Webserver-Ebene delegiert werden. Unter dem modernen Caddy-Webserver lässt sich das Admin-Verzeichnis mittels HTTP Basic Authentication effizient absichern. 

Ein entsprechender Block in der `Caddyfile`-Konfiguration kapselt das Verzeichnis ab:

```caddy
route /admin* {
    basic_auth {
        janni <gehashtes_passwort>
    }
    file_server
}
```

Vielen Dank fürs Lesen,  
Jan