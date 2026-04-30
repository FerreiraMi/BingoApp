<?php require_once __DIR__ . '/includes/i18n.php'; ?>
<footer class="bg-light py-5">
  <div class="container">
    <div class="small text-center text-muted">
      <?php echo t('footer.copyright'); ?> <?php echo date("Y"); ?> - <?php echo t('footer.by'); ?>
      <br>
      <a href="<?php echo with_lang('/terms.php'); ?>" class="text-decoration-none me-3"><?php echo t('menu.terms'); ?></a>
      <a href="<?php echo with_lang('/privacy.php'); ?>" class="text-decoration-none me-3"><?php echo t('menu.privacy'); ?></a>
      <a href="<?php echo with_lang('/instrucoes.php'); ?>" class="text-decoration-none"><?php echo t('menu.instructions'); ?></a>
    </div>
  </div>
</footer>
