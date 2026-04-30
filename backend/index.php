<?php require_once __DIR__ . '/includes/i18n.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bingou! - <?php echo t('hero.title'); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <header class="hero-section text-center text-white d-flex">
    <div class="container my-auto">
      <div class="row">
        <div class="col-lg-10 mx-auto">
          <h1 class="text-uppercase"><strong><?php echo t('hero.title'); ?></strong></h1>
          <hr class="divider">
        </div>
        <div class="col-lg-8 mx-auto">
          <p class="text-faded mb-5"><?php echo t('hero.subtitle'); ?></p>
          <a class="btn btn-primary btn-xl" href="<?php echo with_lang('/index.php#recursos'); ?>"><?php echo t('hero.button'); ?></a>
        </div>
      </div>
    </div>
  </header>

  <section class="page-section" id="recursos">
    <div class="container">
      <h2 class="text-center mt-0"><?php echo t('features.title'); ?></h2>
      <hr class="divider">
      <div class="row">
        <div class="col-lg-3 col-md-6 text-center">
          <div class="mt-5">
            <div class="mb-2"><i class="bi bi-display fs-1 text-primary"></i></div>
            <h3 class="h4 mb-2"><?php echo t('features.items.panel.title'); ?></h3>
            <p class="text-muted mb-0"><?php echo t('features.items.panel.desc'); ?></p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 text-center">
          <div class="mt-5">
            <div class="mb-2"><i class="bi bi-phone fs-1 text-primary"></i></div>
            <h3 class="h4 mb-2"><?php echo t('features.items.control.title'); ?></h3>
            <p class="text-muted mb-0"><?php echo t('features.items.control.desc'); ?></p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 text-center">
          <div class="mt-5">
            <div class="mb-2"><i class="bi bi-person-check fs-1 text-primary"></i></div>
            <h3 class="h4 mb-2"><?php echo t('features.items.call.title'); ?></h3>
            <p class="text-muted mb-0"><?php echo t('features.items.call.desc'); ?></p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 text-center">
          <div class="mt-5">
            <div class="mb-2"><i class="bi bi-archive fs-1 text-primary"></i></div>
            <h3 class="h4 mb-2"><?php echo t('features.items.history.title'); ?></h3>
            <p class="text-muted mb-0"><?php echo t('features.items.history.desc'); ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- section class="page-section bg-dark text-white" id="pro">
    <div class="container text-center">
      <h2 class="mb-4"><?php echo t('pro.title'); ?></h2>
      <hr class="divider divider-light">
      <div class="price-tag my-4">
        <h3><?php echo t('pro.price_label'); ?></h3>
        <span><?php echo t('pro.per_year'); ?></span>
      </div>
      <p class="mb-4"><?php echo t('pro.no_ads'); ?></p>
      <a class="btn btn-light btn-xl" href="#"><?php echo t('pro.cta'); ?></a>
    </div>
  </section -->

  <section class="page-section" id="contato">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
          <h2 class="mt-0"><?php echo t('contact.title'); ?></h2>
          <hr class="divider">
          <p class="text-muted mb-5"><?php echo t('contact.intro'); ?></p>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-4 text-center mb-5 mb-lg-0">
          <i class="bi-envelope fs-2 mb-3 text-muted"></i>
          <div><a class="d-block" href="mailto:contato@abeenterprises.com.br">contato@abeenterprises.com.br</a></div>
        </div>
        <div class="col-lg-8">
          <form id="contactForm" action="contact.php" method="post">
            <div id="form-messages" class="mb-3"></div>
            <div class="form-floating mb-3">
              <input class="form-control" id="name" name="name" type="text" placeholder="Seu nome..." required>
              <label for="name"><?php echo t('contact.name_label'); ?></label>
            </div>
            <div class="form-floating mb-3">
              <input class="form-control" id="email" name="email" type="email" placeholder="seu@email.com" required>
              <label for="email"><?php echo t('contact.email_label'); ?></label>
            </div>
            <div class="form-floating mb-3">
              <textarea class="form-control" id="message" name="message" placeholder="Sua mensagem..." style="height: 10rem" required></textarea>
              <label for="message"><?php echo t('contact.message_label'); ?></label>
            </div>
            <div class="d-grid"><button class="btn btn-primary btn-xl" id="submitButton" type="submit"><?php echo t('contact.submit'); ?></button></div>
          </form>
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
