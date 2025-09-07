<?php require_once __DIR__ . '/includes/i18n.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo t('menu.terms'); ?> - Bingou!</title>
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
      <h1 class="text-uppercase"><strong><?php echo t('menu.terms'); ?></strong></h1>
      <p class="text-faded mb-0">v1.0 — <?php echo date('d/m/Y'); ?></p>
    </div>
  </header>

  <section class="page-section">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
              <?php
                $sections = t('terms.sections');
                foreach ($sections as $i => $sec) {
                  echo '<h2 id="sec'.$i.'" class="h3 mt-4 mb-3">'.htmlspecialchars($sec['title']).'</h2>';
                  foreach ($sec['content'] as $p) {
                    echo '<p>'. $p .'</p>';
                  }
                  if (!empty($sec['list'])) {
                    echo '<ul>';
                    foreach ($sec['list'] as $li) echo '<li>'.$li.'</li>';
                    echo '</ul>';
                  }
                }
              ?>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm border-0 position-sticky" style="top: 90px;">
            <div class="card-body p-4">
              <h5 class="mb-3"><?php echo t('common.index'); ?></h5>
              <div class="list-group list-group-flush">
                <?php
                  foreach (t('terms.sections') as $i => $sec) {
                    echo '<a href="#sec'.$i.'" class="list-group-item list-group-item-action">'.htmlspecialchars($sec['title']).'</a>';
                  }
                ?>
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
