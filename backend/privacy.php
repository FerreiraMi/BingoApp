<?php
// Opcional: incluir variáveis globais do site
// require_once __DIR__ . '/config/vars.php';
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - Bingou!</title>
    
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
     <!-- Navbar -->
    <? include "navbar.php"?>

    <!-- Hero compacta -->
    <header class="hero-section text-center text-white d-flex" style="min-height: 35vh;">
        <div class="container my-auto">
            <h1 class="text-uppercase"><strong>Política de Privacidade</strong></h1>
            <p class="text-faded mb-0">Versão 1.0 &middot; Atualizado em <?php echo date('d/m/Y'); ?></p>
        </div>
    </header>

    <!-- Conteúdo -->
    <section class="page-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 p-md-5">
                            <h2 id="coleta" class="h3 mb-3">1. Informações Coletadas</h2>
                            <p>Coletamos informações que você fornece ao criar conta ou interagir com o Aplicativo:</p>
                            <ul>
                                <li>Dados de cadastro (nome, e-mail, senha);</li>
                                <li>Dados de uso (partidas, números, resultados);</li>
                                <li>Dados técnicos (modelo do dispositivo, sistema operacional, IP aproximado).</li>
                            </ul>

                            <h2 id="uso" class="h3 mt-4 mb-3">2. Uso das Informações</h2>
                            <ul>
                                <li>Operar funcionalidades e partidas;</li>
                                <li>Garantir segurança e integridade do serviço;</li>
                                <li>Enviar notificações relevantes (incluindo push, quando habilitado);</li>
                                <li>Melhorar a experiência do usuário e métricas do produto.</li>
                            </ul>

                            <h2 id="compartilhamento" class="h3 mt-4 mb-3">3. Compartilhamento</h2>
                            <p>Não vendemos seus dados pessoais. Compartilhamos apenas quando necessário para operar o serviço, por obrigações legais, ou com seu consentimento.</p>

                            <h2 id="armazenamento" class="h3 mt-4 mb-3">4. Armazenamento e Segurança</h2>
                            <p>Armazenamos seus dados em servidores seguros e adotamos controles razoáveis de proteção. Nenhum sistema é 100% seguro; utilize senhas fortes e mantenha seu dispositivo atualizado.</p>

                            <h2 id="direitos" class="h3 mt-4 mb-3">5. Seus Direitos</h2>
                            <ul>
                                <li>Acessar, corrigir e atualizar suas informações;</li>
                                <li>Solicitar exclusão de conta e dados associados;</li>
                                <li>Revogar consentimentos previamente concedidos.</li>
                            </ul>
                            <p>Contato: <a href="mailto:suporte@iw7.com.br">suporte@iw7.com.br</a>.</p>

                            <h2 id="cookies" class="h3 mt-4 mb-3">6. Cookies e Tecnologias</h2>
                            <p>Usamos cookies e tecnologias semelhantes para lembrar preferências, manter sessão e analisar o uso do Aplicativo.</p>

                            <h2 id="terceiros" class="h3 mt-4 mb-3">7. Serviços de Terceiros</h2>
                            <p>Integrações com App Store, Google Play, provedores de login e análise seguem seus próprios termos e políticas.</p>

                            <h2 id="retencao" class="h3 mt-4 mb-3">8. Retenção de Dados</h2>
                            <p>Guardamos dados pelo tempo necessário às finalidades descritas ou conforme exigido por lei. Após esse período, dados são removidos ou anonimizados.</p>

                            <h2 id="alteracoes" class="h3 mt-4 mb-3">9. Alterações</h2>
                            <p>Esta Política pode ser atualizada periodicamente. Mudanças relevantes podem ser comunicadas no Aplicativo ou site.</p>

                            <h2 id="contato" class="h3 mt-4 mb-0">10. Contato</h2>
                            <p class="mb-0">Dúvidas ou solicitações: <a href="mailto:suporte@iw7.com.br">suporte@iw7.com.br</a>.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 position-sticky" style="top: 90px;">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Índice</h5>
                            <div class="list-group list-group-flush">
                                <a href="#coleta" class="list-group-item list-group-item-action">1. Informações Coletadas</a>
                                <a href="#uso" class="list-group-item list-group-item-action">2. Uso das Informações</a>
                                <a href="#compartilhamento" class="list-group-item list-group-item-action">3. Compartilhamento</a>
                                <a href="#armazenamento" class="list-group-item list-group-item-action">4. Armazenamento e Segurança</a>
                                <a href="#direitos" class="list-group-item list-group-item-action">5. Seus Direitos</a>
                                <a href="#cookies" class="list-group-item list-group-item-action">6. Cookies e Tecnologias</a>
                                <a href="#terceiros" class="list-group-item list-group-item-action">7. Serviços de Terceiros</a>
                                <a href="#retencao" class="list-group-item list-group-item-action">8. Retenção de Dados</a>
                                <a href="#alteracoes" class="list-group-item list-group-item-action">9. Alterações</a>
                                <a href="#contato" class="list-group-item list-group-item-action">10. Contato</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <? include "footer.php"; ?>  

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Nosso JS customizado -->
    <script src="assets/js/script.js"></script>
</body>
</html>
