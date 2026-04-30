<?php
require_once __DIR__ . '/config/vars.php';
// =========================================================================
// ETAPA 1: O PHP BUSCA OS DADOS DA SESSÃO NO BANCO DE DADOS
// =========================================================================
$sessionId = $_GET['session'] ?? null;
$shortId = $_GET['i'] ?? null;

if (!$shortId) {
    // Modo de espera: o navegador gera um código de display e aguarda o APP vincular
    ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BinGou - Aguardando Conexão</title>
    <script src="/assets/js/qrcode.min.js"></script>
    <style>
        html, body {
            margin: 0; padding: 0; min-height: 100vh;
            background-color: #0d1b2a; color: #e0e1dd;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex; justify-content: center; align-items: center;
        }
        .waiting-container { text-align: center; padding: 40px; }
        .logo { font-size: 3.5em; font-weight: bold; color: #ffd700; letter-spacing: 3px; margin-bottom: 4px; }
        .subtitle { font-size: 1.1em; color: #778da9; margin: 0 0 36px; }
        #qrcode {
            margin: 0 auto 20px; display: inline-block;
            background: white; padding: 14px; border-radius: 14px;
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.25);
        }
        .code-label { font-size: 0.85em; color: #778da9; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 8px; }
        .code-display {
            font-size: clamp(2.5em, 7vw, 5.5em);
            font-weight: bold; letter-spacing: 0.25em;
            color: #ffd700; font-family: 'Courier New', monospace;
            text-shadow: 0 0 24px rgba(255, 215, 0, 0.45);
            margin: 0 0 28px;
        }
        .instruction {
            font-size: 1em; color: #778da9; max-width: 400px;
            margin: 0 auto 30px; line-height: 1.7;
        }
        .waiting-indicator { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .spinner {
            width: 20px; height: 20px;
            border: 3px solid rgba(255, 215, 0, 0.25);
            border-top-color: #ffd700; border-radius: 50%;
            animation: spin 0.9s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        #status-text { color: #778da9; font-size: 0.95em; }
        .linked-text { color: #4caf50; font-weight: bold; }
    </style>
</head>
<body>
    <div class="waiting-container">
        <div class="logo">BinGou</div>
        <div class="subtitle">Painel do Bingo</div>
        <div id="qrcode"></div>
        <div class="code-label">Código do Display</div>
        <div class="code-display" id="display-code">- - - - - -</div>
        <p class="instruction">
            Abra o aplicativo BinGou, crie uma sessão e toque no ícone
            <strong>Vincular ao Display</strong>.<br>
            Escaneie o QR Code acima ou digite o código para conectar.
        </p>
        <div class="waiting-indicator">
            <div class="spinner" id="spinner"></div>
            <span id="status-text">Aguardando conexão com o aplicativo...</span>
        </div>
    </div>
    <script>
        let pollingInterval = null;

        async function init() {
            try {
                const res = await fetch('/api/display_token', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                if (!res.ok) throw new Error('Resposta inesperada do servidor.');
                const data = await res.json();
                if (!data.code) throw new Error('Código não retornado.');

                document.getElementById('display-code').textContent = data.code;

                new QRCode(document.getElementById('qrcode'), {
                    text: data.code,
                    width: 210, height: 210,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });

                startPolling(data.code);
            } catch (e) {
                document.getElementById('spinner').style.display = 'none';
                document.getElementById('status-text').textContent =
                    'Erro ao gerar código. Recarregue a página.';
            }
        }

        function startPolling(code) {
            pollingInterval = setInterval(async () => {
                try {
                    const res = await fetch('/api/display_token?code=' + encodeURIComponent(code));
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.status === 'linked' && data.sessionShortId) {
                        clearInterval(pollingInterval);
                        document.getElementById('spinner').style.display = 'none';
                        document.getElementById('status-text').innerHTML =
                            '<span class="linked-text">✓ Conectado! Redirecionando...</span>';
                        setTimeout(() => {
                            window.location.href = '/bingo/' + data.sessionShortId;
                        }, 800);
                    }
                } catch (e) { /* Ignora erros transitórios de rede */ }
            }, 2000);
        }

        init();
    </script>
</body>
</html>
    <?php
    exit();
}

// Consulta o MongoDB diretamente (sem roundtrip HTTP)
require_once __DIR__ . '/config/bootstrap.php';

try {
    $session = $client->bingo_db->sessions->findOne(['shortId' => $shortId]);
} catch (\Exception $e) {
    die("Erro ao acessar o banco de dados: " . $e->getMessage());
}

if (!$session) {
    die("Sessão não encontrada. ShortId: $shortId");
}

$sessionData = [
    '_id'         => (string)$session['_id'],
    'sessionName' => (string)($session['sessionName'] ?? ''),
    'round'       => (string)($session['round'] ?? ''),
    'prize'       => (string)($session['prize'] ?? ''),
    'isProUser'   => (bool)($session['isProUser'] ?? false),
    'drawnNumbers' => [],
];
foreach ($session['drawnNumbers'] ?? [] as $num) {
    $sessionData['drawnNumbers'][] = (int)$num;
}

$isProUser = $sessionData['isProUser'];
$sessionId = $sessionData['_id'];

// Pegamos a lista de números já sorteados
$drawnNumbers = $sessionData['drawnNumbers'];
sort($drawnNumbers, SORT_NUMERIC); // Ordenados para consistência no carregamento inicial

// Função auxiliar para determinar a letra de um número
function getBingoLetter($number) {
    if ($number >= 1 && $number <= 15) return 'B';
    if ($number >= 16 && $number <= 30) return 'I';
    if ($number >= 31 && $number <= 45) return 'N';
    if ($number >= 46 && $number <= 60) return 'G';
    if ($number >= 61 && $number <= 75) return 'O';
    return '';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Bingo - <?php echo htmlspecialchars($sessionData['sessionName'] ?? ''); ?></title>
    <!-- Incluindo a biblioteca de confetes para a animação de BINGO! -->
    <script src="/assets/js/confetti.browser.min.js"></script>
    <style>
        /* Reset e Configurações Globais */
        html, body {
            margin: 0; padding: 0; height: 100%; width: 100%; overflow: hidden;
            background-color: #0d1b2a; color: #e0e1dd;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-weight: bold;
        }
        .container { display: flex; flex-direction: column; height: 100vh; width: 100vw; padding: 2vw; box-sizing: border-box; }
        .header { flex-shrink: 0; text-align: center; padding-bottom: 2vh; }
        .header h1 { font-size: 3.5vw; margin: 0; color: #ffffff; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
        .header h2 { font-size: 2vw; margin: 0; }
        .last-number-panel { text-align: center; padding: 1vh 0; }
        .last-number-panel .number-display { font-size: 10vw; line-height: 1; color: #ffd700; text-shadow: 4px 4px 8px rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; }
        .last-number-panel .letter-display { font-size: 8vw; margin-right: 2vw; opacity: 0.7; color: #e0e1dd; }
        .bingo-board { flex-grow: 1; display: flex; justify-content: space-around; gap: 1vw; padding-top: 2vh; border-top: 4px solid #415a77; min-height: 0; }
        .bingo-column { flex: 1; display: flex; flex-direction: column; background-color: #1b263b; border-radius: 15px; padding: 1vw; box-shadow: 0 0 20px rgba(0,0,0,0.3); min-width: 0; }
        .column-header { text-align: center; font-size: 5vw; color: #ffd700; padding-bottom: 1vh; border-bottom: 2px solid #415a77; margin-bottom: 1vh; }
        .numbers-container { flex-grow: 1; display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.8vw; overflow-y: auto; align-content: start; }
        .ball { display: flex; justify-content: center; align-items: center; background-color: #415a77; border-radius: 50%; aspect-ratio: 1 / 1; font-size: 2vw; color: white; line-height: 1; text-align: center; }
        .new-number-animation { animation: newNumberPop 0.6s ease-out; }
        @keyframes newNumberPop { 0% { transform: scale(0.5); opacity: 0; } 70% { transform: scale(1.2); } 100% { transform: scale(1); opacity: 1; } }
        
        /* CSS PARA O POP-UP DE BINGO! */
        #bingo-alert-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.75); display: none;
            justify-content: center; align-items: center; z-index: 9999;
            animation: fadeIn 0.5s ease-in-out;
        }
        #bingo-alert-box {
            font-size: 25vw; color: #ffd700;
            text-shadow: 0 0 15px #fff, 0 0 25px #ffd700, 0 0 40px #ff8c00;
            animation: zoomInAndShake 1s ease-in-out;
        }

        #bingo-alert-box .winners {
            font-size: 4vw; /* Tamanho menor para os nomes */
            margin-top: 2vh;
            color: #fff;
            font-weight: normal;
            text-shadow: 2px 2px 4px #000;
        }

        .viewers-panel {
            position: fixed;
            bottom: 10px;
            right: 10px;
            display: none; /* controlado por session_settings */
            background-color: rgba(27, 38, 59, 0.8);
            padding: 10px 15px;
            border-radius: 10px;
            max-width: 250px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #415a77;
        }
        .viewers-panel h4 { margin: 0 0 10px 0; color: #fff; }
        .viewers-panel ul { margin: 0; padding: 0 0 0 20px; }
        .viewers-panel li { font-size: 1em; padding: 2px 0; }

        /* Painel de probabilidade */
        .stats-panel {
            position: fixed; bottom: 10px; left: 10px;
            display: none;
            background-color: rgba(27, 38, 59, 0.92);
            padding: 12px 16px; border-radius: 10px;
            min-width: 200px; border: 1px solid #415a77; z-index: 100;
        }
        .stats-title { font-size: 0.8em; color: #ffd700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }
        .stat-modes { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 8px; }
        .stat-mode-tag { font-size: 0.7em; background: #415a77; color: #e0e1dd; padding: 2px 8px; border-radius: 10px; }
        .stat-row { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; }
        .stat-label { font-size: 1.1em; color: #ffd700; width: 16px; text-align: center; font-weight: bold; }
        .stat-bar-wrap { flex: 1; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; }
        .stat-bar { height: 100%; background: linear-gradient(90deg, #415a77, #60a5fa); border-radius: 4px; transition: width 0.5s ease; width: 0%; }
        .stat-count { font-size: 0.75em; color: #aaa; width: 32px; text-align: right; }
        .stat-total { margin-top: 8px; padding-top: 6px; border-top: 1px solid #415a77; font-size: 0.8em; color: #aaa; text-align: center; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes zoomInAndShake { 0% { transform: scale(0.1); } 50% { transform: scale(1.1); } 70% { transform: scale(0.9) rotate(-3deg); } 80% { transform: scale(1.05) rotate(3deg); } 100% { transform: scale(1) rotate(0); } }
        
        .ad-footer { position: fixed; bottom: 0; left: 0; width: 100%; background-color: rgba(0,0,0,0.7); padding: 10px; text-align: center; font-size: 1.5vw; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header"><h1><?php echo htmlspecialchars($sessionData['sessionName'] ?? ''); ?></h1><h2>Rodada: <?php echo htmlspecialchars((string)($sessionData['round'] ?? '')); ?> | Prêmio: <?php echo htmlspecialchars($sessionData['prize'] ?? ''); ?></h2></header>
        <div class="last-number-panel"><div id="last-number-display" class="number-display"><span id="last-letter-display" class="letter-display"><?php echo getBingoLetter(end($drawnNumbers)); ?></span><span id="last-number-content"><?php echo end($drawnNumbers) ?: '-'; ?></span></div></div>
        <main class="bingo-board"><div class="bingo-column" id="col-B"><div class="column-header">B</div><div class="numbers-container"></div></div><div class="bingo-column" id="col-I"><div class="column-header">I</div><div class="numbers-container"></div></div><div class="bingo-column" id="col-N"><div class="column-header">N</div><div class="numbers-container"></div></div><div class="bingo-column" id="col-G"><div class="column-header">G</div><div class="numbers-container"></div></div><div class="bingo-column" id="col-O"><div class="column-header">O</div><div class="numbers-container"></div></div></main>
    </div>

    <!-- Painel de Espectadores (oculto por padrão, controlado via session_settings) -->
    <div class="viewers-panel" id="viewers-panel">
        <h4>Espectadores Online</h4>
        <ul id="viewers-list">
            <li>Ninguém online...</li>
        </ul>
    </div>

    <!-- Painel de Probabilidade (oculto por padrão, controlado via session_settings) -->
    <div class="stats-panel" id="stats-panel">
        <div class="stats-title">Probabilidade</div>
        <div id="stats-modes" class="stat-modes"></div>
        <div class="stat-row"><span class="stat-label">B</span><div class="stat-bar-wrap"><div class="stat-bar" id="bar-B"></div></div><span class="stat-count" id="count-B">0/15</span></div>
        <div class="stat-row"><span class="stat-label">I</span><div class="stat-bar-wrap"><div class="stat-bar" id="bar-I"></div></div><span class="stat-count" id="count-I">0/15</span></div>
        <div class="stat-row"><span class="stat-label">N</span><div class="stat-bar-wrap"><div class="stat-bar" id="bar-N"></div></div><span class="stat-count" id="count-N">0/15</span></div>
        <div class="stat-row"><span class="stat-label">G</span><div class="stat-bar-wrap"><div class="stat-bar" id="bar-G"></div></div><span class="stat-count" id="count-G">0/15</span></div>
        <div class="stat-row"><span class="stat-label">O</span><div class="stat-bar-wrap"><div class="stat-bar" id="bar-O"></div></div><span class="stat-count" id="count-O">0/15</span></div>
        <div id="stat-total-row" class="stat-total" style="display:none;">Total: <span id="count-total">0/75</span></div>
    </div>

    <div id="bingo-alert-overlay">
        <div id="bingo-alert-box">
            <span>BINGO!</span>
            <div id="bingo-winners-list" class="winners"></div>
        </div>
    </div>

    <!-- 
    <?php if (!$isProUser): ?><footer class="ad-footer">Este painel é oferecido por SeuAppDeBingo.com - Remova os anúncios com a versão PRO!</footer><?php endif; ?>
    -->
    <script>
        const sessionId = "<?php echo $sessionId; ?>";
        const shortId   = "<?php echo $shortId; ?>";
        let drawnNumbers = <?php echo json_encode($drawnNumbers); ?>;

        let statsVisible = false;

        function getBingoLetter(number) { if (number >= 1 && number <= 15) return 'B'; if (number >= 16 && number <= 30) return 'I'; if (number >= 31 && number <= 45) return 'N'; if (number >= 46 && number <= 60) return 'G'; if (number >= 61 && number <= 75) return 'O'; return ''; }

        // Redimensiona o texto das bolas para 80% do diâmetro da bola
        function resizeBallText() {
            document.querySelectorAll('.ball').forEach(ball => {
                const w = ball.getBoundingClientRect().width;
                if (w > 0) ball.style.fontSize = (w * 0.75) + 'px';
            });
        }
        window.addEventListener('resize', () => requestAnimationFrame(resizeBallText));

        function addNumberToBoard(number, withAnimation = false) {
            const letter = getBingoLetter(number); if (!letter) return;
            const columnContainer = document.querySelector(`#col-${letter} .numbers-container`); if (!columnContainer) return;
            const newBall = document.createElement('div'); newBall.className = 'ball'; if (withAnimation) { newBall.classList.add('new-number-animation'); }
            newBall.textContent = number; const newNumberValue = parseInt(number);
            let referenceNode = null;
            for (const existingBall of columnContainer.children) { if (parseInt(existingBall.textContent) > newNumberValue) { referenceNode = existingBall; break; } }
            columnContainer.insertBefore(newBall, referenceNode);
            requestAnimationFrame(resizeBallText);
        }

        drawnNumbers.forEach(num => addNumberToBoard(num, false));
        requestAnimationFrame(resizeBallText);

        // Atualiza as barras de probabilidade por coluna
        function updateStats() {
            if (!statsVisible) return;
            const counts = { B: 0, I: 0, N: 0, G: 0, O: 0 };
            drawnNumbers.forEach(n => { const l = getBingoLetter(n); if (l) counts[l]++; });
            ['B', 'I', 'N', 'G', 'O'].forEach(l => {
                const bar = document.getElementById('bar-' + l);
                const count = document.getElementById('count-' + l);
                if (bar) bar.style.width = (counts[l] / 15 * 100) + '%';
                if (count) count.textContent = counts[l] + '/15';
            });
            const totalEl = document.getElementById('count-total');
            if (totalEl) totalEl.textContent = drawnNumbers.length + '/75';
        }

        function connect() {
            const conn = new WebSocket(`<?=$WSHOST_NAME?>`);
            conn.onopen = () => conn.send(JSON.stringify({ type: 'subscribe', sessionId: sessionId, shortId: shortId }));
            conn.onclose = () => setTimeout(connect, 1000);
            conn.onerror = () => conn.close();
            conn.onmessage = function(e) {
                const data = JSON.parse(e.data);
                console.log('Mensagem recebida:', data);

                if (data.type === 'redirect' && data.targetSessionId === sessionId) {
                    window.location.href = data.newUrl;

                } else if (data.type === 'new_number') {
                    const newNumber = data.number;
                    if (!drawnNumbers.includes(newNumber)) {
                        document.getElementById('last-number-content').textContent = newNumber;
                        document.getElementById('last-letter-display').textContent = getBingoLetter(newNumber);
                        const displayPanel = document.getElementById('last-number-display');
                        displayPanel.classList.remove('new-number-animation'); void displayPanel.offsetWidth; displayPanel.classList.add('new-number-animation');
                        drawnNumbers.push(newNumber);
                        addNumberToBoard(newNumber, true);
                        updateStats();
                        const bingoOverlay = document.getElementById('bingo-alert-overlay');
                        if (bingoOverlay && bingoOverlay.style.display === 'flex') bingoOverlay.style.display = 'none';
                    }

                } else if (data.type === 'bingo_called') {
                    const overlay = document.getElementById('bingo-alert-overlay');
                    const winnersList = document.getElementById('bingo-winners-list');
                    winnersList.innerHTML = '';
                    if (data.winners && data.winners.length > 0) {
                        winnersList.textContent = 'Ganhador(es): ' + data.winners.join(', ');
                    }
                    overlay.style.display = 'flex';
                    const duration = 35 * 1000; const end = Date.now() + duration;
                    (function frame() {
                        confetti({ particleCount: 2, angle: 60, spread: 55, origin: { x: 0 } });
                        confetti({ particleCount: 2, angle: 120, spread: 55, origin: { x: 1 } });
                        if (Date.now() < end) requestAnimationFrame(frame);
                    }());
                    setTimeout(() => { overlay.style.display = 'none'; }, duration);

                } else if (data.type === 'viewer_list_update') {
                    const viewersList = document.getElementById('viewers-list');
                    const viewersPanel = document.getElementById('viewers-panel');
                    // Mostra o painel automaticamente ao receber a lista (independente de session_settings)
                    if (viewersPanel) viewersPanel.style.display = 'block';
                    // Atualiza o título com a contagem
                    const viewersTitle = viewersPanel ? viewersPanel.querySelector('h4') : null;
                    if (viewersTitle) viewersTitle.textContent = 'Espectadores Online (' + (data.viewers ? data.viewers.length : 0) + ')';
                    viewersList.innerHTML = '';
                    if (data.viewers && data.viewers.length > 0) {
                        data.viewers.forEach(name => {
                            const li = document.createElement('li');
                            li.textContent = name;
                            viewersList.appendChild(li);
                        });
                    } else {
                        const li = document.createElement('li');
                        li.textContent = 'Ninguém online...';
                        viewersList.appendChild(li);
                    }

                } else if (data.type === 'session_settings') {
                    // Controla visibilidade do painel de espectadores
                    const viewersPanel = document.getElementById('viewers-panel');
                    if (viewersPanel) viewersPanel.style.display = data.showViewers ? 'block' : 'none';

                    // Controla painel de probabilidade
                    statsVisible = data.showProbability || false;
                    const statsPanel = document.getElementById('stats-panel');
                    if (statsPanel) {
                        statsPanel.style.display = statsVisible ? '' : 'none';
                        if (statsVisible && data.probabilityModes) {
                            const modeNames = { vertical: 'Vertical', horizontal: 'Horizontal', diagonal: 'Diagonal', fullCard: 'Cartela Cheia' };
                            const modesEl = document.getElementById('stats-modes');
                            if (modesEl) modesEl.innerHTML = data.probabilityModes.map(m => `<span class="stat-mode-tag">${modeNames[m] || m}</span>`).join('');
                            const totalRow = document.getElementById('stat-total-row');
                            if (totalRow) totalRow.style.display = data.probabilityModes.includes('fullCard') ? '' : 'none';
                        }
                        updateStats();
                    }
                }
            };
        }
        connect();
    </script>
</body>
</html>