<?php
// =========================================================================
// ETAPA 1: O PHP BUSCA OS DADOS DA SESSÃO NO SERVIDOR
// =========================================================================
$sessionId = $_GET['session'] ?? null;
$shortId = $_GET['shortId'] ?? null;

if (!$shortId) {
    die("ID da sessão não fornecido.");
}

$apiUrl = "http://webserver/api/session.php?shortId=" . $shortId;
$response = @file_get_contents($apiUrl);
if ($response === FALSE) die("Sessão não encontrada ou erro na API. $shortId");

$sessionData = json_decode($response, true);
$isProUser = isset($sessionData['isProUser']) ? $sessionData['isProUser'] : true;
$sessionId = isset($sessionData['_id']) ? $sessionData['_id'] : null;

// Pegamos a lista de números já sorteados
$drawnNumbers = isset($sessionData['drawnNumbers']) ? $sessionData['drawnNumbers'] : [];
sort($drawnNumbers, SORT_NUMERIC); // Ordenamos para garantir a consistência no carregamento inicial

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
    <title>Painel do Bingo - <?php echo htmlspecialchars($sessionData['sessionName']); ?></title>
    <!-- Incluindo a biblioteca de confetes para a animação de BINGO! -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
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
        .last-number-panel .number-display { font-size: 15vw; line-height: 1; color: #ffd700; text-shadow: 4px 4px 8px rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; }
        .last-number-panel .letter-display { font-size: 13vw; margin-right: 2vw; opacity: 0.7; color: #e0e1dd; }
        .bingo-board { flex-grow: 1; display: flex; justify-content: space-around; gap: 1vw; padding-top: 2vh; border-top: 4px solid #415a77; min-height: 0; }
        .bingo-column { flex: 1; display: flex; flex-direction: column; background-color: #1b263b; border-radius: 15px; padding: 1vw; box-shadow: 0 0 20px rgba(0,0,0,0.3); min-width: 0; }
        .column-header { text-align: center; font-size: 5vw; color: #ffd700; padding-bottom: 1vh; border-bottom: 2px solid #415a77; margin-bottom: 1vh; }
        .numbers-container { flex-grow: 1; display: grid; grid-template-columns: repeat(auto-fit, minmax(4vw, 1fr)); gap: 0.8vw; overflow-y: auto; }
        .ball { display: flex; justify-content: center; align-items: center; background-color: #415a77; border-radius: 50%; aspect-ratio: 1 / 1; font-size: 2vw; color: white; }
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

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes zoomInAndShake { 0% { transform: scale(0.1); } 50% { transform: scale(1.1); } 70% { transform: scale(0.9) rotate(-3deg); } 80% { transform: scale(1.05) rotate(3deg); } 100% { transform: scale(1) rotate(0); } }
        
        .ad-footer { position: fixed; bottom: 0; left: 0; width: 100%; background-color: rgba(0,0,0,0.7); padding: 10px; text-align: center; font-size: 1.5vw; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header"><h1><?php echo htmlspecialchars($sessionData['sessionName']); ?></h1><h2>Rodada: <?php echo htmlspecialchars($sessionData['round']); ?> | Prêmio: <?php echo htmlspecialchars($sessionData['prize']); ?></h2></header>
        <div class="last-number-panel"><div id="last-number-display" class="number-display"><span id="last-letter-display" class="letter-display"><?php echo getBingoLetter(end($drawnNumbers)); ?></span><span id="last-number-content"><?php echo end($drawnNumbers) ?: '-'; ?></span></div></div>
        <main class="bingo-board"><div class="bingo-column" id="col-B"><div class="column-header">B</div><div class="numbers-container"></div></div><div class="bingo-column" id="col-I"><div class="column-header">I</div><div class="numbers-container"></div></div><div class="bingo-column" id="col-N"><div class="column-header">N</div><div class="numbers-container"></div></div><div class="bingo-column" id="col-G"><div class="column-header">G</div><div class="numbers-container"></div></div><div class="bingo-column" id="col-O"><div class="column-header">O</div><div class="numbers-container"></div></div></main>
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
        let drawnNumbers = <?php echo json_encode($drawnNumbers); ?>;
        
        function getBingoLetter(number) { if (number >= 1 && number <= 15) return 'B'; if (number >= 16 && number <= 30) return 'I'; if (number >= 31 && number <= 45) return 'N'; if (number >= 46 && number <= 60) return 'G'; if (number >= 61 && number <= 75) return 'O'; return ''; }

        function addNumberToBoard(number, withAnimation = false) {
            const letter = getBingoLetter(number); if (!letter) return;
            const columnContainer = document.querySelector(`#col-${letter} .numbers-container`); if (!columnContainer) return;
            const newBall = document.createElement('div'); newBall.className = 'ball'; if (withAnimation) { newBall.classList.add('new-number-animation'); }
            newBall.textContent = number; const newNumberValue = parseInt(number);
            let referenceNode = null;
            for (const existingBall of columnContainer.children) { if (parseInt(existingBall.textContent) > newNumberValue) { referenceNode = existingBall; break; } }
            columnContainer.insertBefore(newBall, referenceNode);
        }

        drawnNumbers.forEach(num => addNumberToBoard(num, false));

        function connect() {
            const conn = new WebSocket(`ws://${window.location.hostname}:8080`);
            conn.onopen = () => conn.send(JSON.stringify({ type: 'subscribe', sessionId: sessionId }));
            conn.onclose = () => setTimeout(connect, 1000);
            conn.onerror = () => conn.close();
            conn.onmessage = function(e) {
                const data = JSON.parse(e.data);
                if (data.type === 'redirect' && data.targetSessionId === sessionId) { window.location.href = data.newUrl; }
                else if (data.type === 'new_number') {
                    const newNumber = data.number;
                    if (!drawnNumbers.includes(newNumber)) {
                        document.getElementById('last-number-content').textContent = newNumber;
                        document.getElementById('last-letter-display').textContent = getBingoLetter(newNumber);
                        const displayPanel = document.getElementById('last-number-display');
                        displayPanel.classList.remove('new-number-animation'); void displayPanel.offsetWidth; displayPanel.classList.add('new-number-animation');
                        drawnNumbers.push(newNumber);
                        addNumberToBoard(newNumber, true);
                    }
                } else if (data.type === 'bingo_called') {
                    const overlay = document.getElementById('bingo-alert-overlay');
                    const winnersList = document.getElementById('bingo-winners-list');
    
                    // Limpa a lista de ganhadores anterior e preenche com a nova
                    winnersList.innerHTML = ''; 
                    if (data.winners && data.winners.length > 0) {
                        winnersList.textContent = 'Ganhador(es): ' + data.winners.join(', ');
                    }

                    overlay.style.display = 'flex';
                    const duration = 30 * 1000; const end = Date.now() + duration;
                    (function frame() {
                        confetti({ particleCount: 2, angle: 60, spread: 55, origin: { x: 0 } });
                        confetti({ particleCount: 2, angle: 120, spread: 55, origin: { x: 1 } });
                        if (Date.now() < end) requestAnimationFrame(frame);
                    }());
                    setTimeout(() => { overlay.style.display = 'none'; }, duration);
                }
            };
        }
        connect();
    </script>
</body>
</html>