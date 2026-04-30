<?php require_once __DIR__ . '/includes/i18n.php'; ?>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand" href="<?php echo with_lang('/index.php'); ?>">
      <img src="assets/img/logo1.png" alt="Bingou! Logo" height="40">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo with_lang('/#recursos'); ?>"><?php echo t('menu.features'); ?></a></li>
        <!-- <li class="nav-item"><a class="nav-link" href="<?php echo with_lang('/#pro'); ?>"><?php echo t('menu.pro'); ?></a></li> -->
        <li class="nav-item"><a class="nav-link" href="<?php echo with_lang('/#contato'); ?>"><?php echo t('menu.contact'); ?></a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo with_lang('/instrucoes'); ?>"><?php echo t('menu.instructions'); ?></a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            🌐 <?php echo strtoupper($lang); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
            <li><a class="dropdown-item" href="?lang=pt">Português (PT)</a></li>
            <li><a class="dropdown-item" href="?lang=en">English (EN)</a></li>
            <li><a class="dropdown-item" href="?lang=es">Español (ES)</a></li>
            <li><a class="dropdown-item" href="?lang=it">Italiano (IT)</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
