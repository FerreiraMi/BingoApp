<?php
// Opcional: incluir variáveis globais
// require_once __DIR__ . '/config/vars.php';
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruções - Bingou!</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Nosso CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <? include "navbar.php" ?>

    <!-- Hero -->
    <header class="hero-section text-center text-white d-flex" style="min-height: 35vh;">
        <div class="container my-auto">
            <h1 class="text-uppercase"><strong>Instruções de Uso</strong></h1>
            <p class="text-faded mb-0">Aprenda a usar o Bingou! de forma rápida e fácil</p>
        </div>
    </header>

    <!-- Conteúdo -->
    <section class="page-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="h3 mb-3"><i class="bi bi-download me-2"></i>1. Instalação</h2>
                            <p>Baixe o aplicativo Bingou! na <strong>Google Play Store</strong> ou na <strong>Apple App Store</strong>. Após instalar, abra o app no seu dispositivo.</p>

                            <h2 class="h3 mt-4 mb-3"><i class="bi bi-person-plus me-2"></i>2. Criação de Conta</h2>
                            <p>Crie sua conta com e-mail e senha válidos. Você deve aceitar os <a href="/terms">Termos de Uso</a> e a <a href="/privacy">Política de Privacidade</a> para prosseguir.</p>

                            <h2 class="h3 mt-4 mb-3"><i class="bi bi-controller me-2"></i>3. Criar ou Entrar em uma Sessão</h2>
                            <ul>
                                <li><strong>Criar Sessão:</strong> escolha um nome, defina prêmios e compartilhe o código/QR Code com os jogadores.</li>
                                <li><strong>Entrar em Sessão:</strong> insira o código fornecido pelo organizador.</li>
                            </ul>

                            <h2 class="h3 mt-4 mb-3"><i class="bi bi-ticket-perforated me-2"></i>4. Jogando Bingo</h2>
                            <p>Durante a sessão, os números sorteados aparecem em tempo real no painel. Quando completar uma cartela válida, clique em <strong>"Cantar Bingo"</strong>.</p>

                            <h2 class="h3 mt-4 mb-3"><i class="bi bi-trophy me-2"></i>5. Ganhadores</h2>
                            <p>O organizador confirma os bingos e anuncia os ganhadores. O histórico fica salvo para consultas posteriores.</p>

                            <h2 class="h3 mt-4 mb-3"><i class="bi bi-shield-check me-2"></i>6. Dicas de Segurança</h2>
                            <ul>
                                <li>Não compartilhe sua senha.</li>
                                <li>Use o app apenas em redes seguras.</li>
                                <li>Respeite os demais jogadores e siga os termos da comunidade.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Índice lateral -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 position-sticky" style="top: 90px;">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Índice</h5>
                            <div class="list-group list-group-flush">
                                <a href="#instalacao" class="list-group-item list-group-item-action">1. Instalação</a>
                                <a href="#conta" class="list-group-item list-group-item-action">2. Criação de Conta</a>
                                <a href="#sessao" class="list-group-item list-group-item-action">3. Criar/Entrar em Sessão</a>
                                <a href="#jogo" class="list-group-item list-group-item-action">4. Jogando Bingo</a>
                                <a href="#ganhadores" class="list-group-item list-group-item-action">5. Ganhadores</a>
                                <a href="#seguranca" class="list-group-item list-group-item-action">6. Dicas de Segurança</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?include "footer.php"?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Nosso JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>