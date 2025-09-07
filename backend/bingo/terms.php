<?php
require_once __DIR__ . '../config/vars.php';
/**
 * Termos de Uso - App Bingo
 * Compatível com o estilo visual de bingo.php (tema escuro, tipografia forte).
 * URL sugerida: /terms/index.php ou /terms.php
 *
 * © <?php echo date('Y'); ?> IW7 / ABE Enterprises. Todos os direitos reservados.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Termos de Uso - App Bingo</title>
    <style>
        /* Base visual seguindo bingo.php (dark, tipografia bold) */
        html, body {
            margin: 0; padding: 0; height: 100%; width: 100%;
            background-color: #0d1b2a; color: #e0e1dd;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-weight: 600;
        }
        a { color: #61dafb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container {
            display: flex; flex-direction: column;
            min-height: 100vh; width: 100vw; box-sizing: border-box;
        }
        .header {
            padding: 2vh 4vw 1vh 4vw; text-align: center;
            border-bottom: 1px solid #415a77;
        }
        .header h1 {
            margin: 0; color: #ffffff; font-size: clamp(22px, 3.5vw, 40px);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .meta {
            margin-top: .5rem; font-weight: 500; opacity: .9; font-size: .95rem;
        }
        .content {
            display: grid; grid-template-columns: 1fr minmax(220px, 260px);
            gap: 24px; padding: 3vh 4vw; flex: 1;
        }
        .card {
            background: rgba(255,255,255,0.04);
            border: 1px solid #415a77; border-radius: 12px;
            padding: min(3vh, 28px) min(3vw, 28px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }
        .toc {
            position: sticky; top: 16px;
            height: fit-content;
        }
        .toc h3 { margin: 0 0 12px 0; font-size: 1rem; color: #fff; }
        .toc ul { margin: 0; padding-left: 18px; }
        .toc li { margin: 6px 0; font-weight: 500; }
        h2 {
            color: #fff; font-size: clamp(18px, 2.2vw, 28px);
            margin: 24px 0 8px 0;
        }
        p, li { line-height: 1.6; font-weight: 500; }
        .footer {
            border-top: 1px solid #415a77; padding: 16px 4vw; text-align: center; font-size: .95rem;
            color: #c8d3dc; opacity: .9;
        }
        .badge {
            display: inline-block; padding: 4px 10px; border-radius: 999px;
            background: #1b263b; border: 1px solid #415a77; font-size: .85rem;
        }
        @media (max-width: 960px) {
            .content { grid-template-columns: 1fr; }
            .toc { position: static; }
        }
        /* Pequeno destaque para caixas de aviso */
        .note {
            background: rgba(255, 193, 7, 0.12);
            border: 1px solid rgba(255, 193, 7, 0.35);
            color: #ffe08a;
            padding: 12px 14px; border-radius: 8px; margin: 12px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <header class="header">
        <h1>Termos de Uso</h1>
        <div class="meta">
            <span class="badge">Versão 1.0</span>
            &nbsp;•&nbsp; Última atualização: <?php echo date('d/m/Y'); ?>
        </div>
    </header>

    <main class="content">
        <article class="card" aria-labelledby="termos">
            <section id="aceitacao">
                <h2>1. Aceitação dos Termos</h2>
                <p>
                    Bem-vindo ao <strong>App Bingo</strong> (o “Aplicativo”). Ao criar uma conta,
                    acessar ou utilizar o Aplicativo e serviços relacionados, você concorda com estes Termos de Uso
                    (“Termos”) e com a nossa <a href="/privacy.php">Política de Privacidade</a>.
                    Se você não concordar com estes Termos, não utilize o Aplicativo.
                </p>
                <div class="note">
                    Se você estiver aceitando estes Termos em nome de uma organização, declara ter autoridade
                    para vincular essa organização a este contrato.
                </div>
            </section>

            <section id="elegibilidade">
                <h2>2. Elegibilidade e Natureza do Serviço</h2>
                <ul>
                    <li>O Aplicativo destina-se a <strong>entretenimento</strong> e organização de partidas de bingo.</li>
                    <li><strong>Não há apostas com dinheiro real</strong> no Aplicativo. Prêmios, quando existirem,
                        são virtuais ou promocionais, conforme definido pelos organizadores de cada sessão.</li>
                    <li>Você declara ter <strong>pelo menos 18 (dezoito) anos</strong> ou ser legalmente capaz conforme as leis do seu país/região.</li>
                    <li>O uso do Aplicativo pode estar sujeito a leis locais. Você é responsável por utilizá-lo em conformidade com a legislação aplicável.</li>
                </ul>
            </section>

            <section id="contas">
                <h2>3. Conta do Usuário</h2>
                <ul>
                    <li>Você é responsável por manter a confidencialidade das suas credenciais de acesso.</li>
                    <li>As informações fornecidas devem ser exatas, completas e atualizadas.</li>
                    <li>Podemos suspender ou encerrar contas que violem estes Termos ou representem risco à segurança/serviço.</li>
                    <li>Para solicitar exclusão da conta e/ou dados, envie um e-mail para <a href="mailto:suporte@iw7.com.br">suporte@iw7.com.br</a>.</li>
                </ul>
            </section>

            <section id="licenca">
                <h2>4. Licença de Uso</h2>
                <p>
                    Concedemos a você uma licença <strong>limitada, pessoal, revogável, intransferível e não exclusiva</strong>
                    para usar o Aplicativo conforme estes Termos. É proibido: (i) engenharia reversa; (ii) acessar códigos-fonte
                    não disponibilizados publicamente; (iii) contornar mecanismos de segurança; (iv) usar o Aplicativo para fins
                    ilegais ou não autorizados.
                </p>
            </section>

            <section id="conduta">
                <h2>5. Conduta Proibida</h2>
                <ul>
                    <li>Utilizar bots, trapaças, automações ou qualquer meio que distorça a competição.</li>
                    <li>Assediar, discriminar, ameaçar ou violar direitos de terceiros.</li>
                    <li>Enviar conteúdo ilícito, difamatório, pornográfico, violento ou que infrinja propriedade intelectual.</li>
                    <li>Perturbar a infraestrutura (ex.: sobrecarga, DDoS, exploração de vulnerabilidades).</li>
                </ul>
            </section>

            <section id="compras">
                <h2>6. Compras, Assinaturas e Pagamentos</h2>
                <p>
                    O Aplicativo pode oferecer recursos pagos (ex.: moedas virtuais, planos premium).
                    Quando aplicável, as condições (preço, ciclo de cobrança, benefícios, cancelamento e reembolso)
                    serão apresentadas no momento da contratação e regidas também pelas regras da
                    <strong>App Store</strong> e/ou <strong>Google Play</strong>.
                </p>
                <p>
                    <em>Observação:</em> a moeda virtual não tem valor de dinheiro real e não é reembolsável,
                    salvo quando exigido pela legislação aplicável.
                </p>
            </section>

            <section id="privacidade">
                <h2>7. Privacidade e Dados</h2>
                <p>
                    O tratamento de dados pessoais segue a nossa <a href="/bingo/privacy">Política de Privacidade</a>.
                    Ao usar o Aplicativo, você concorda com a coleta e o uso de dados conforme descrito ali,
                    incluindo, quando aplicável, notificações push e telemetria para melhoria do serviço.
                </p>
            </section>

            <section id="conteudo">
                <h2>8. Conteúdo do Usuário</h2>
                <p>
                    Você pode enviar conteúdo (ex.: nome de exibição, mensagens de chat). Você declara possuir os direitos
                    necessários e concede ao operador do Aplicativo uma licença mundial, não exclusiva, isenta de royalties
                    para <strong>armazenar, processar, exibir e transmitir</strong> esse conteúdo com a finalidade de
                    operação do serviço. Poderemos remover conteúdo que viole estes Termos.
                </p>
            </section>

            <section id="terceiros">
                <h2>9. Serviços de Terceiros</h2>
                <p>
                    O Aplicativo pode conter links ou integrações de terceiros (ex.: provedores de login, análise, anúncios).
                    Não controlamos esses serviços e não nos responsabilizamos por suas práticas. Recomendamos revisar os
                    termos e políticas de cada terceiro antes de utilizá-los.
                </p>
            </section>

            <section id="isencao">
                <h2>10. Isenção de Garantias</h2>
                <p>
                    O Aplicativo é fornecido “<strong>no estado em que se encontra</strong>” e “<strong>conforme a disponibilidade</strong>”.
                    Não garantimos disponibilidade ininterrupta, ausência de erros ou compatibilidade com todos os dispositivos.
                    Na máxima extensão permitida pela lei, excluímos garantias legais e implícitas.
                </p>
            </section>

            <section id="limitacao">
                <h2>11. Limitação de Responsabilidade</h2>
                <p>
                    Na máxima medida permitida pela legislação aplicável, não seremos responsáveis por lucros cessantes,
                    danos indiretos, incidentais, especiais, punitivos ou consequenciais, decorrentes do uso ou impossibilidade
                    de uso do Aplicativo.
                </p>
            </section>

            <section id="rescisao">
                <h2>12. Suspensão e Rescisão</h2>
                <p>
                    Podemos suspender ou encerrar o acesso ao Aplicativo, total ou parcialmente, em caso de violação destes Termos,
                    cumprimento de obrigações legais ou razões operacionais. Você pode encerrar seu uso a qualquer momento
                    e solicitar exclusão de conta/dados conforme a seção 3.
                </p>
            </section>

            <section id="alteracoes">
                <h2>13. Alterações nos Termos</h2>
                <p>
                    Poderemos atualizar estes Termos periodicamente. Quando mudanças relevantes ocorrerem,
                    indicaremos a nova data de atualização acima e, quando apropriado, notificaremos pelos canais disponíveis.
                    O uso continuado após as alterações implica aceitação dos novos Termos.
                </p>
            </section>

            <section id="lei-foro">
                <h2>14. Lei Aplicável e Foro</h2>
                <p>
                    Estes Termos são regidos pelas leis do Brasil. Fica eleito o foro da Comarca de São Paulo/SP,
                    com renúncia a qualquer outro, por mais privilegiado que seja, para dirimir eventuais controvérsias,
                    salvo disposições legais em contrário.
                </p>
            </section>

            <section id="contato">
                <h2>15. Contato</h2>
                <p>
                    Dúvidas, solicitações ou reclamações: <a href="mailto:suporte@iw7.com.br">suporte@iw7.com.br</a>.
                    Endereço corporativo poderá constar na Política de Privacidade ou no site institucional.
                </p>
            </section>
        </article>

        <aside class="card toc" aria-label="Índice">
            <h3>Índice</h3>
            <ul>
                <li><a href="#aceitacao">1. Aceitação dos Termos</a></li>
                <li><a href="#elegibilidade">2. Elegibilidade e Natureza</a></li>
                <li><a href="#contas">3. Conta do Usuário</a></li>
                <li><a href="#licenca">4. Licença de Uso</a></li>
                <li><a href="#conduta">5. Conduta Proibida</a></li>
                <li><a href="#compras">6. Compras e Pagamentos</a></li>
                <li><a href="#privacidade">7. Privacidade e Dados</a></li>
                <li><a href="#conteudo">8. Conteúdo do Usuário</a></li>
                <li><a href="#terceiros">9. Serviços de Terceiros</a></li>
                <li><a href="#isencao">10. Isenção de Garantias</a></li>
                <li><a href="#limitacao">11. Limitação de Responsabilidade</a></li>
                <li><a href="#rescisao">12. Suspensão e Rescisão</a></li>
                <li><a href="#alteracoes">13. Alterações nos Termos</a></li>
                <li><a href="#lei-foro">14. Lei Aplicável e Foro</a></li>
                <li><a href="#contato">15. Contato</a></li>
            </ul>
        </aside>
    </main>

    <footer class="footer">
        © <?php echo date('Y'); ?> IW7 / ABE Enterprises — App Bingo. Todos os direitos reservados.
    </footer>
</div>
</body>
</html>
