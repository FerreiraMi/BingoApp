<?php
// Opcional: incluir variáveis globais do site
// require_once __DIR__ . '/config/vars.php';
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso - Bingou!</title>
    
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
    <? include "navbar.php"?>

    <!-- Hero compacta -->
    <header class="hero-section text-center text-white d-flex" style="min-height: 35vh;">
        <div class="container my-auto">
            <h1 class="text-uppercase"><strong>Termos de Uso</strong></h1>
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
                            <h2 id="aceite" class="h3 mb-3">1. Aceitação dos Termos</h2>
                            <p>Ao criar uma conta, acessar ou utilizar o <strong>Bingou!</strong> (o “Aplicativo”), você concorda com estes Termos de Uso (“Termos”) e com a nossa <a href="/privacy">Política de Privacidade</a>. Se não concordar, não utilize o Aplicativo.</p>
                            <div class="alert alert-warning"><i class="bi bi-info-circle me-2"></i>Se você estiver aceitando estes Termos em nome de uma organização, declara ter autoridade para vinculá-la a este contrato.</div>

                            <h2 id="elegibilidade" class="h3 mt-4 mb-3">2. Elegibilidade e Natureza do Serviço</h2>
                            <ul>
                                <li>O Aplicativo destina-se a <strong>entretenimento</strong> e organização de partidas de bingo.</li>
                                <li><strong>Não há apostas com dinheiro real</strong>. Prêmios, quando houver, são virtuais ou promocionais, definidos pelos organizadores de cada sessão.</li>
                                <li>Você declara ter <strong>pelo menos 18 anos</strong> ou ser legalmente capaz conforme sua jurisdição.</li>
                                <li>Você é responsável por usar o Aplicativo em conformidade com a lei aplicável.</li>
                            </ul>

                            <h2 id="conta" class="h3 mt-4 mb-3">3. Conta do Usuário</h2>
                            <ul>
                                <li>Mantenha a confidencialidade de suas credenciais.</li>
                                <li>Forneça informações exatas e atualizadas.</li>
                                <li>Podemos suspender/encerrar contas que violem estes Termos ou representem risco.</li>
                                <li>Para excluir conta/dados: <a href="mailto:suporte@iw7.com.br">suporte@iw7.com.br</a>.</li>
                            </ul>

                            <h2 id="licenca" class="h3 mt-4 mb-3">4. Licença de Uso</h2>
                            <p>Licença <strong>limitada, pessoal, revogável, intransferível e não exclusiva</strong> para usar o Aplicativo conforme estes Termos. É proibido engenharia reversa, burlar segurança, uso ilegal/não autorizado, entre outros.</p>

                            <h2 id="conduta" class="h3 mt-4 mb-3">5. Conduta Proibida</h2>
                            <ul>
                                <li>Uso de bots, trapaças ou automações desleais.</li>
                                <li>Assédio, discriminação, ameaças ou violação de direitos de terceiros.</li>
                                <li>Envio de conteúdo ilícito ou que infrinja propriedade intelectual.</li>
                                <li>Perturbar a infraestrutura (ex.: sobrecarga, exploração de vulnerabilidades).</li>
                            </ul>

                            <h2 id="pagamentos" class="h3 mt-4 mb-3">6. Compras, Assinaturas e Pagamentos</h2>
                            <p>Recursos pagos (ex.: moedas virtuais, planos PRO) seguem condições apresentadas no momento da compra, regidas também pela <strong>App Store</strong> e/ou <strong>Google Play</strong>. Moeda virtual não possui valor real e não é reembolsável, salvo quando exigido por lei.</p>

                            <h2 id="privacidade" class="h3 mt-4 mb-3">7. Privacidade e Dados</h2>
                            <p>O tratamento de dados pessoais segue a nossa <a href="/privacy.php">Política de Privacidade</a>, incluindo notificações push e telemetria quando aplicável.</p>

                            <h2 id="conteudo" class="h3 mt-4 mb-3">8. Conteúdo do Usuário</h2>
                            <p>Você declara possuir direitos sobre o conteúdo enviado e concede licença mundial, não exclusiva e isenta de royalties para armazenar, processar, exibir e transmitir tal conteúdo para a operação do serviço. Conteúdos que violem estes Termos podem ser removidos.</p>

                            <h2 id="terceiros" class="h3 mt-4 mb-3">9. Serviços de Terceiros</h2>
                            <p>Poderá haver links/integrações de terceiros. Não controlamos tais serviços; revise seus termos e políticas.</p>

                            <h2 id="isencao" class="h3 mt-4 mb-3">10. Isenção de Garantias</h2>
                            <p>O Aplicativo é fornecido “no estado em que se encontra” e “conforme a disponibilidade”, sem garantias de disponibilidade ininterrupta ou ausência de erros.</p>

                            <h2 id="limitacao" class="h3 mt-4 mb-3">11. Limitação de Responsabilidade</h2>
                            <p>Na máxima medida permitida por lei, não seremos responsáveis por lucros cessantes, danos indiretos, especiais ou consequenciais decorrentes do uso do Aplicativo.</p>

                            <h2 id="rescisao" class="h3 mt-4 mb-3">12. Suspensão e Rescisão</h2>
                            <p>Poderemos suspender/encerrar o acesso por violação destes Termos, razões legais ou operacionais. Você pode encerrar o uso a qualquer momento e solicitar exclusão de dados.</p>

                            <h2 id="alteracoes" class="h3 mt-4 mb-3">13. Alterações</h2>
                            <p>Estes Termos podem ser atualizados periodicamente. O uso contínuo após mudanças implica aceitação da versão vigente.</p>

                            <h2 id="lei" class="h3 mt-4 mb-3">14. Lei Aplicável e Foro</h2>
                            <p>Regidos pelas leis do Brasil. Foro da Comarca de São Paulo/SP, salvo disposição legal em contrário.</p>

                            <h2 id="contato" class="h3 mt-4 mb-0">15. Contato</h2>
                            <p class="mb-0">Dúvidas: <a href="mailto:suporte@iw7.com.br">suporte@iw7.com.br</a>.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 position-sticky" style="top: 90px;">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Índice</h5>
                            <div class="list-group list-group-flush">
                                <a href="#aceite" class="list-group-item list-group-item-action">1. Aceitação dos Termos</a>
                                <a href="#elegibilidade" class="list-group-item list-group-item-action">2. Elegibilidade e Serviço</a>
                                <a href="#conta" class="list-group-item list-group-item-action">3. Conta do Usuário</a>
                                <a href="#licenca" class="list-group-item list-group-item-action">4. Licença de Uso</a>
                                <a href="#conduta" class="list-group-item list-group-item-action">5. Conduta Proibida</a>
                                <a href="#pagamentos" class="list-group-item list-group-item-action">6. Compras e Pagamentos</a>
                                <a href="#privacidade" class="list-group-item list-group-item-action">7. Privacidade e Dados</a>
                                <a href="#conteudo" class="list-group-item list-group-item-action">8. Conteúdo do Usuário</a>
                                <a href="#terceiros" class="list-group-item list-group-item-action">9. Serviços de Terceiros</a>
                                <a href="#isencao" class="list-group-item list-group-item-action">10. Isenção de Garantias</a>
                                <a href="#limitacao" class="list-group-item list-group-item-action">11. Limitação de Responsabilidade</a>
                                <a href="#rescisao" class="list-group-item list-group-item-action">12. Suspensão e Rescisão</a>
                                <a href="#alteracoes" class="list-group-item list-group-item-action">13. Alterações</a>
                                <a href="#lei" class="list-group-item list-group-item-action">14. Lei e Foro</a>
                                <a href="#contato" class="list-group-item list-group-item-action">15. Contato</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <? include "footer.php"; ?>  

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Nosso JS customizado -->
    <script src="assets/js/script.js"></script>
</body>
</html>
