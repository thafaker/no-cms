---
title: Theme(n)-Wechsel
date: 2026-07-10 15:14
tags:
  - php
  - design
  - caddy
---

Hi, na? 

Ich habe meine URLs prettyfied, die waren vorher noch so wie früher, ```post.php?file.md```, und das wollte ich gern ändern. Also habe ich das Caddyfile angepasst und folgendes eingebaut:

```bash
# 5 bessere URLS für Suchmaschinen-Scheißhäuser
    @clean_urls {
        not file {path} {path}/
        not path /admin.php /assets* /inc* /posts* /feed.xml /sitemap.xml
    }
    rewrite @clean_urls /post.php?slug={path}
```

und eigentlich habe ich mal wieder ein paar Inspirationen erhalten, bei meinen Streifzügen wie dereinst nur der junge *The Strange Man* aus Red Dead Redemption, und ein anderes Theme für NoCMS umsetzen wollen. Ihr seht das hier, wenn ihr einen harten Reload des hartnäckigen Browser-Caches durchführt. Oder ihr schaut euch folgenden Screenshot an:

![Beschreibung](/images/worscht.jpg)
<small>Hyprland with new Theme pure for NoCMS</small>

Und das wars.  
*Ciao*

**PS**: Ach so, warum ich das überhaupt aufschreibe. Durch die neuen URLs kann es sein, dass auch die RSS-Feed-URLs neu werden und alle Artikel neu sind, obwohl sie das nicht sind. Dies hier ist die Erklärung und vielleicht schreibe ich die alten URLs lieber noch um.

**PPS**: [Stop The Slop](https://loggbok.de/link/2026-07-10-stop-the-slop/)!