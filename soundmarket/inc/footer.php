<?php
// inc/footer.php
declare(strict_types=1);
?>
</main> <footer class="site-footer">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
      <div>
        <strong style="color: var(--brand);"><?= APP_NAME ?></strong>
        <p style="margin: 5px 0 0; font-size: 13px; color: var(--muted);">© <?= date('Y') ?> Всички права запазени.</p>
      </div>
      
      <div class="footer-links" style="display: flex; gap: 20px;">
        <a href="<?= APP_BASE ?>/beats.php" style="text-decoration: none; color: var(--text); font-size: 14px;">Бийтове</a>
        <a href="<?= APP_BASE ?>/music.php" style="text-decoration: none; color: var(--text); font-size: 14px;">Музика</a>
        <a href="<?= APP_BASE ?>/services.php" style="text-decoration: none; color: var(--text); font-size: 14px;">Услуги</a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>