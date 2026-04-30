<?php require_once __DIR__ . '/includes/i18n.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo t('instructions.hero.title'); ?> - Bingou!</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <header class="hero-section text-center text-white d-flex" style="min-height: 35vh;">
    <div class="container my-auto">
      <h1 class="text-uppercase"><strong><?php echo t('instructions.hero.title'); ?></strong></h1>
      <p class="text-faded mb-0"><?php echo t('instructions.hero.subtitle'); ?></p>
    </div>
  </header>

  <section class="page-section">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
              <h2 id="instalacao" class="h3 mb-3"><i class="bi bi-download me-2"></i><?php echo t('instructions.steps.1.title'); ?></h2>
              <p><?php echo t('instructions.steps.1.desc'); ?></p>

              <h2 id="conta" class="h3 mt-4 mb-3"><i class="bi bi-person-plus me-2"></i><?php echo t('instructions.steps.2.title'); ?></h2>
              <p><?php echo t('instructions.steps.2.desc'); ?></p>

              <h2 id="sessao" class="h3 mt-4 mb-3"><i class="bi bi-controller me-2"></i><?php echo t('instructions.steps.3.title'); ?></h2>
              <ul>
                <li><?php echo t('instructions.steps.3.items.create'); ?></li>
                <li><?php echo t('instructions.steps.3.items.join'); ?></li>
              </ul>

              <h2 id="jogo" class="h3 mt-4 mb-3"><i class="bi bi-ticket-perforated me-2"></i><?php echo t('instructions.steps.4.title'); ?></h2>
              <p><?php echo t('instructions.steps.4.desc'); ?></p>

              <h2 id="ganhadores" class="h3 mt-4 mb-3"><i class="bi bi-trophy me-2"></i><?php echo t('instructions.steps.5.title'); ?></h2>
              <p><?php echo t('instructions.steps.5.desc'); ?></p>

              <h2 id="seguranca" class="h3 mt-4 mb-3"><i class="bi bi-shield-check me-2"></i><?php echo t('instructions.steps.6.title'); ?></h2>
              <ul>
                <li><?php echo t('instructions.steps.6.items.pw'); ?></li>
                <li><?php echo t('instructions.steps.6.items.network'); ?></li>
                <li><?php echo t('instructions.steps.6.items.community'); ?></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm border-0 position-sticky" style="top: 90px;">
            <div class="card-body p-4">
              <h5 class="mb-3"><?php echo t('instructions.index'); ?></h5>
              <div class="list-group list-group-flush">
                <a href="#instalacao" class="list-group-item list-group-item-action">1. <?php echo t('instructions.steps.1.short'); ?></a>
                <a href="#conta" class="list-group-item list-group-item-action">2. <?php echo t('instructions.steps.2.short'); ?></a>
                <a href="#sessao" class="list-group-item list-group-item-action">3. <?php echo t('instructions.steps.3.short'); ?></a>
                <a href="#jogo" class="list-group-item list-group-item-action">4. <?php echo t('instructions.steps.4.short'); ?></a>
                <a href="#ganhadores" class="list-group-item list-group-item-action">5. <?php echo t('instructions.steps.5.short'); ?></a>
                <a href="#seguranca" class="list-group-item list-group-item-action">6. <?php echo t('instructions.steps.6.short'); ?></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include __DIR__ . '/footer.php'; ?>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/script.js"></script>
</body>
</html>
