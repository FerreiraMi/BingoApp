<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bingou! - O seu aplicativo para controlar bingos</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Nosso CSS Customizado -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/img/logo1.png" alt="Bingou! Logo" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#recursos">Recursos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pro">Versão PRO</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contato">Contato</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Seção Hero -->
    <header class="hero-section text-center text-white d-flex">
        <div class="container my-auto">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <h1 class="text-uppercase"><strong>A maneira mais fácil e divertida de organizar seu bingo</strong></h1>
                    <hr class="divider">
                </div>
                <div class="col-lg-8 mx-auto">
                    <p class="text-faded mb-5">Controle suas rodadas, compartilhe com amigos e acompanhe em tempo real, tudo na palma da sua mão!</p>
                    <a class="btn btn-primary btn-xl" href="#recursos">Descubra Mais</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Seção de Recursos -->
    <section class="page-section" id="recursos">
        <div class="container">
            <h2 class="text-center mt-0">Recursos Incríveis</h2>
            <hr class="divider">
            <div class="row">
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="mt-5">
                        <div class="mb-2"><i class="bi bi-display fs-1 text-primary"></i></div>
                        <h3 class="h4 mb-2">Painel em Tempo Real</h3>
                        <p class="text-muted mb-0">Compartilhe um link e exiba os números sorteados em qualquer TV ou projetor.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="mt-5">
                        <div class="mb-2"><i class="bi bi-phone fs-1 text-primary"></i></div>
                        <h3 class="h4 mb-2">Controle Total</h3>
                        <p class="text-muted mb-0">Sorteie números automaticamente ou insira manualmente com total flexibilidade.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="mt-5">
                        <div class="mb-2"><i class="bi bi-person-check fs-1 text-primary"></i></div>
                        <h3 class="h4 mb-2">Chamada de Bingo</h3>
                        <p class="text-muted mb-0">Jogadores podem "cantar" o bingo pelo app, alertando o operador instantaneamente.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 text-center">
                    <div class="mt-5">
                        <div class="mb-2"><i class="bi bi-archive fs-1 text-primary"></i></div>
                        <h3 class="h4 mb-2">Histórico Completo</h3>
                        <p class="text-muted mb-0">Salve suas sessões, veja os números sorteados e os ganhadores de cada rodada.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção Versão PRO -->
    <section class="page-section bg-dark text-white" id="pro">
        <div class="container text-center">
            <h2 class="mb-4">Seja PRO por um preço imbatível!</h2>
            <hr class="divider divider-light">
            <div class="price-tag my-4">
                <h3>R$ 15,00</h3>
                <span>por ano</span>
            </div>
            <p class="mb-4">Com a versão PRO, você e seus espectadores aproveitam uma experiência totalmente **livre de anúncios**!</p>
            <a class="btn btn-light btn-xl" href="#">Quero ser PRO!</a>
        </div>
    </section>

    <!-- Seção de Contato -->
    <section class="page-section" id="contato">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mt-0">Fale Conosco</h2>
                    <hr class="divider">
                    <p class="text-muted mb-5">Tem alguma dúvida, sugestão ou precisa de suporte? Entre em contato conosco!</p>
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
                            <label for="name">Nome completo</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="email" name="email" type="email" placeholder="seu@email.com" required>
                            <label for="email">Endereço de email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="message" name="message" type="text" placeholder="Sua mensagem..." style="height: 10rem" required></textarea>
                            <label for="message">Mensagem</label>
                        </div>
                        <div class="d-grid"><button class="btn btn-primary btn-xl" id="submitButton" type="submit">Enviar Mensagem</button></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-light py-5">
        <div class="container"><div class="small text-center text-muted">Copyright © <?php echo date("Y"); ?> - Bingou! by ABE Enterprises</div></div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Nosso JS customizado -->
    <script src="assets/js/script.js"></script>
</body>
</html>