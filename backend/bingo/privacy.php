<?php
require_once __DIR__ . '/config/vars.php';
/**
 * Política de Privacidade - App Bingo
 * Compatível com o estilo visual de bingo.php (tema escuro, tipografia forte).
 * URL sugerida: /privacy/index.php ou /privacy.php
 *
 * © <?php echo date('Y'); ?> IW7 / ABE Enterprises. Todos os direitos reservados.
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Política de Privacidade - App Bingo</title>
    <style>
        html, body {
            margin: 0; padding: 0; height: 100%; width: 100%;
            background-color: #0d1b2a; color: #e0e1dd;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-weight: 600;
        }
        a { color: #61dafb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { display: flex; flex-direction: column; min-height: 100vh; width: 100vw; }
        .header { padding: 2vh 4vw 1vh 4vw; text-align: center; border-bottom: 1px solid #415a77; }
        .header h1 { margin: 0; color: #ffffff; font-size: clamp(22px, 3.5vw, 40px); text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
        .meta { margin-top: .5rem; font-weight: 500; opacity: .9; font-size: .95rem; }
        .content { display: grid; grid-template-columns: 1fr minmax(220px, 260px); gap: 24px; padding: 3vh 4vw; flex: 1; }
        .card { background: rgba(255,255,255,0.04); border: 1px solid #415a77; border-radius: 12px; padding: min(3vh, 28px) min(3vw, 28px); box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
        .toc { position: sticky; top: 16px; height: fit-content; }
        .toc h3 { margin: 0 0 12px 0; font-size: 1rem; color: #fff; }
        .toc ul { margin: 0; padding-left: 18px; }
        .toc li { margin: 6px 0; font-weight: 500; }
        h2 { color: #fff; font-size: clamp(18px, 2.2vw, 28px); margin: 24px 0 8px 0; }
        p, li { line-height: 1.6; font-weight: 500; }
        .footer { border-top: 1px solid #415a77; padding: 16px 4vw; text-align: center; font-size: .95rem; color: #c8d3dc; opacity: .9; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #1b263b; border: 1px solid #415a77; font-size: .85rem; }
        @media (max-width: 960px) { .content { grid-template-columns: 1fr; } .toc { position: static; } }
    </style>
</head>
<body>
<div class="container">
    <header class="header">
        <h1>Política de Privacidade</h1>
        <div class="meta">
            <span class="badge">Versão 1.0</span>
            &nbsp;•&nbsp; Última atualização: <?php echo date('d/m/Y'); ?>
        </div>
    </header>

    <main class="content">
        <article class="card">
            <section id="coleta">
                <h2>1. Informações Coletadas</h2>
                <p>Coletamos informações que você fornece ao criar conta, participar de partidas ou interagir com o Aplicativo, como:</p>
                <ul>
                    <li>Dados de cadastro (nome, email, senha);</li>
                    <li>Dados de uso (números sorteados, resultados, histórico de partidas);</li>
                    <li>Dados técnicos (modelo de dispositivo, sistema operacional, IP aproximado).</li>
                </ul>
            </section>

            <section id="uso">
                <h2>2. Uso das Informações</h2>
                <p>Usamos as informações para:</p>
                <ul>
                    <li>Permitir sua participação em partidas e funcionalidades do Aplicativo;</li>
                    <li>Manter a segurança e integridade do serviço;</li>
                    <li>Enviar notificações relevantes (incluindo push, quando habilitado);</li>
                    <li>Melhorar e personalizar a experiência do usuário.</li>
                </ul>
            </section>

            <section id="compartilhamento">
                <h2>3. Compartilhamento</h2>
                <p>Não vendemos suas informações pessoais. Podemos compartilhar apenas quando:</p>
                <ul>
                    <li>Necessário para operar o Aplicativo (ex.: provedores de hospedagem e suporte);</li>
                    <li>Exigido por lei, ordem judicial ou autoridades competentes;</li>
                    <li>Com seu consentimento expresso.</li>
                </ul>
            </section>

            <section id="armazenamento">
                <h2>4. Armazenamento e Segurança</h2>
                <p>Seus dados são armazenados em servidores seguros. Adotamos medidas razoáveis de proteção, mas nenhum sistema é 100% seguro. Use senhas fortes e mantenha seu dispositivo atualizado.</p>
            </section>

            <section id="direitos">
                <h2>5. Seus Direitos</h2>
                <ul>
                    <li>Acessar, corrigir ou atualizar suas informações;</li>
                    <li>Solicitar a exclusão de sua conta e dados associados;</li>
                    <li>Revogar consentimentos previamente concedidos.</li>
                </ul>
                <p>Para exercer seus direitos, envie um e-mail para <a href="mailto:suporte@iw7.com.br">suporte@iw7.com.br</a>.</p>
            </section>

            <section id="cookies">
                <h2>6. Cookies e Tecnologias Semelhantes</h2>
                <p>Podemos usar cookies e tecnologias similares para melhorar a navegação, lembrar preferências e analisar estatísticas de uso.</p>
            </section>

            <section id="terceiros">
                <h2>7. Serviços de Terceiros</h2>
                <p>Integrações com App Store, Google Play, provedores de login ou análise podem coletar dados conforme suas próprias políticas. Recomendamos revisá-las.</p>
            </section>

            <section id="alteracoes">
                <h2>8. Alterações na Política</h2>
                <p>Podemos atualizar esta Política periodicamente. Alterações relevantes serão comunicadas no Aplicativo ou site.</p>
            </section>

            <section id="contato">
                <h2>9. Contato</h2>
                <p>Dúvidas ou solicitações: <a href="mailto:suporte@iw7.com.br">suporte@iw7.com.br</a>.</p>
            </section>
        </article>

        <aside class="card toc" aria-label="Índice">
            <h3>Índice</h3>
            <ul>
                <li><a href="#coleta">1. Informações Coletadas</a></li>
                <li><a href="#uso">2. Uso das Informações</a></li>
                <li><a href="#compartilhamento">3. Compartilhamento</a></li>
                <li><a href="#armazenamento">4. Armazenamento e Segurança</a></li>
                <li><a href="#direitos">5. Seus Direitos</a></li>
                <li><a href="#cookies">6. Cookies e Tecnologias</a></li>
                <li><a href="#terceiros">7. Serviços de Terceiros</a></li>
                <li><a href="#alteracoes">8. Alterações</a></li>
                <li><a href="#contato">9. Contato</a></li>
            </ul>
        </aside>
    </main>

    <footer class="footer">
        © <?php echo date('Y'); ?> IW7 / ABE Enterprises — App Bingo. Todos os direitos reservados.
    </footer>
</div>
</body>
</html>
