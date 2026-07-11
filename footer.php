<footer class="site-footer">
    <div class="footer-meta">
        © <?= date('Y') ?> Jan Montag · <a href="https://github.com/thafaker/no-cms">NoCMS</a> · powered by flat files
        <div style="margin-top: 0.8rem;">
            <button id="themeToggle" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1rem; opacity: 0.5;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.5">
                🌓 Mode
            </button>
        </div> 
    </div>
    <nav class="footer-nav">
        <a href="/feed.xml">RSS</a> · 
        <a href="/sitemap.xml">Sitemap</a> · 
        <a href="/about">about</a>
    </nav>
    <div class="footer-counter">
        <?php include 'counter.php'; ?>
    </div>
</footer>
<script src="/assets/script.js"></script>