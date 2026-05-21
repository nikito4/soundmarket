<?php
// inc/footer.php
declare(strict_types=1);
?>
</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <div>
      <strong style="color:var(--brand); font-size:15px;"><?= APP_NAME ?></strong>
      <p style="margin:4px 0 0; font-size:13px; color:var(--muted);">© 2026 SoundMarket. All rights reserved.</p>
    </div>

    <nav class="footer-links">
      <a href="<?= APP_BASE ?>/beats.php">Бийтове</a>
      <a href="<?= APP_BASE ?>/music.php">Музика</a>
      <a href="<?= APP_BASE ?>/services.php">Услуги</a>
    </nav>
  </div>
</footer>

<script src="<?= APP_BASE ?>/assets/js/ui.js"></script>
<script src="<?= APP_BASE ?>/assets/js/player.js"></script>
</body>
</html>
