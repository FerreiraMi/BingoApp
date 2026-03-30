import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:bingou/providers/bingo_provider.dart';
import 'package:bingou/screens/create_session_screen.dart';
import 'package:bingou/screens/qr_scanner_screen.dart';
import 'package:bingou/utils/constants.dart';

class BingoControlScreen extends StatefulWidget {
  const BingoControlScreen({super.key});

  @override
  State<BingoControlScreen> createState() => _BingoControlScreenState();
}

class _BingoControlScreenState extends State<BingoControlScreen> {
  final _textController = TextEditingController();
  bool _isManualMode = false;

  @override
  void dispose() {
    _textController.dispose();
    super.dispose();
  }

  Future<bool> _showFinishConfirmationDialog() async {
    return await showDialog<bool>(
          context: context,
          builder: (ctx) => AlertDialog(
            title: const Text('Encerrar sessão?'),
            content: const Text('Deseja realmente marcar esta sessão como encerrada e finalizá-la? Esta ação não pode ser desfeita.'),
            actions: [
              TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
              TextButton(
                onPressed: () => Navigator.pop(ctx, true),
                child: const Text('Encerrar', style: TextStyle(color: Colors.red)),
              ),
            ],
          ),
        ) ??
        false;
  }

  Future<void> _showBingoWinnerDialog(BingoProvider bingoProvider) async {
    final winnersController = TextEditingController();
    final formKey = GlobalKey<FormState>();

    final confirmed = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: const Text('BINGO! Registrar Ganhador(es)'),
        content: Form(
          key: formKey,
          child: TextFormField(
            controller: winnersController,
            decoration: const InputDecoration(
              labelText: 'Nome(s) do(s) Ganhador(es)',
              hintText: 'Separe por vírgula se houver mais de um',
              border: OutlineInputBorder(),
            ),
            validator: (value) => value == null || value.isEmpty ? 'Por favor, insira pelo menos um nome.' : null,
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          ElevatedButton(
            child: const Text('Confirmar Bingo'),
            onPressed: () {
              if (formKey.currentState!.validate()) Navigator.pop(ctx, true);
            },
          ),
        ],
      ),
    );

    if (confirmed ?? false) {
      final winners = winnersController.text.split(',').map((n) => n.trim()).where((n) => n.isNotEmpty).toList();
      bingoProvider.callBingo(winners: winners);
    }
  }

  void _showLinkDisplayDialog(BingoProvider bingoProvider) {
    final codeController = TextEditingController();
    bool isLinking = false;
    final displayUrl = bingoProvider.shortSessionId != null
        ? '${AppConstants.webPageUrl}/${bingoProvider.shortSessionId}'
        : null;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) {
          Future<void> doLink() async {
            final code = codeController.text.trim().toUpperCase();
            if (code.length != 6) {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('O código deve ter exatamente 6 caracteres.')),
              );
              return;
            }
            setDialogState(() => isLinking = true);
            final success = await bingoProvider.linkToDisplay(code);
            if (ctx.mounted) Navigator.of(ctx).pop();
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(
                    success
                        ? 'Display vinculado! O navegador já está exibindo o painel.'
                        : 'Erro: código inválido, expirado ou já vinculado.',
                  ),
                ),
              );
            }
          }

          return AlertDialog(
            title: const Text('Vincular ao Display'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  'Abra o painel no navegador (projetor/telão) e escaneie ou digite o código exibido.',
                  style: TextStyle(fontSize: 13),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: codeController,
                  decoration: const InputDecoration(
                    labelText: 'Código do Display (6 caracteres)',
                    border: OutlineInputBorder(),
                  ),
                  maxLength: 6,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 22, letterSpacing: 6),
                  textCapitalization: TextCapitalization.characters,
                ),
                const SizedBox(height: 4),
                OutlinedButton.icon(
                  icon: const Icon(Icons.qr_code_scanner),
                  label: const Text('Escanear QR Code'),
                  onPressed: () async {
                    final scanned = await Navigator.of(ctx).push<String>(
                      MaterialPageRoute(builder: (_) => const QRScannerScreen()),
                    );
                    if (scanned != null) codeController.text = scanned.trim().toUpperCase();
                  },
                ),
                if (displayUrl != null) ...[
                  const Divider(height: 24),
                  const Align(
                    alignment: Alignment.centerLeft,
                    child: Text(
                      'Ou abra o painel diretamente:',
                      style: TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Expanded(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: Colors.grey.shade300),
                          ),
                          child: Text(
                            displayUrl,
                            style: const TextStyle(fontSize: 11, fontFamily: 'monospace'),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ),
                      const SizedBox(width: 6),
                      IconButton(
                        tooltip: 'Copiar link',
                        icon: const Icon(Icons.copy_rounded, size: 20),
                        onPressed: () {
                          Clipboard.setData(ClipboardData(text: displayUrl));
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Link copiado!'),
                              duration: Duration(seconds: 2),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ],
              ],
            ),
            actions: [
              TextButton(
                onPressed: isLinking ? null : () => Navigator.of(ctx).pop(),
                child: const Text('Cancelar'),
              ),
              ElevatedButton(
                onPressed: isLinking ? null : doLink,
                child: isLinking
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Vincular'),
              ),
            ],
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final bingoProvider = Provider.of<BingoProvider>(context, listen: false);

      return PopScope(
        canPop: false,
        onPopInvokedWithResult: (didPop, _) async {
          if (didPop) return;
          final (navigator) = Navigator.of(context);

          final confirmed = await _showFinishConfirmationDialog();

          if (!mounted) return; 
          if (confirmed) {
            await bingoProvider.finishCurrentSession();
            bingoProvider.reset();
            navigator.pop();
          }
      },
      child: Scaffold(
        appBar: AppBar(
          leading: IconButton(
            icon: const Icon(Icons.close),
            tooltip: 'Voltar para o histórico (manter sessão ativa)',
            onPressed: () => Navigator.of(context).pop(),
          ),
          title: Consumer<BingoProvider>(
            builder: (ctx, provider, _) => Text(provider.sessionName ?? 'Controle do Bingo'),
          ),
          actions: [
            Consumer<BingoProvider>(
              builder: (ctx, provider, _) => Badge.count(
                count: provider.onlineViewers.length,
                isLabelVisible: provider.onlineViewers.isNotEmpty,
                child: IconButton(
                  icon: const Icon(Icons.tune_rounded),
                  tooltip: 'Configurações da Sessão',
                  onPressed: () => _showSettingsSheet(provider),
                ),
              ),
            ),
          ],
        ),
        body: Consumer<BingoProvider>(
          builder: (ctx, provider, _) {
            if (provider.bingoCalled) {
              return _BingoCalledOverlay(
                winners: provider.bingoWinners,
                provider: provider,
                onDismiss: () => provider.clearBingo(),
                onFinish: () async {
                  provider.clearBingo();
                  final navigator = Navigator.of(context);
                  final confirmed = await _showFinishConfirmationDialog();
                  if (!mounted) return;
                  if (confirmed) {
                    await provider.finishCurrentSession();
                    provider.reset();
                    navigator.pop();
                  }
                },
                onNewSession: () async {
                  provider.clearBingo();
                  final navigator = Navigator. of(context);
                  final confirmed = await _showFinishConfirmationDialog();
                  if (!mounted) return;
                  if (confirmed) {
                    final oldId = provider.sessionId;
                    await provider.finishCurrentSession();
                    provider.reset();
                    navigator.pushReplacement(
                      MaterialPageRoute(
                        builder: (_) => CreateSessionScreen(oldSessionId: oldId,
                        ),
                      ),
                    );
                  }
                },  
              );
            } 
            return Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              // ── Toggle Aleatório / Manual ───────────────────────────
              SegmentedButton<bool>(
                segments: const [
                  ButtonSegment(value: false, label: Text('Aleatório'), icon: Icon(Icons.shuffle_rounded)),
                  ButtonSegment(value: true, label: Text('Manual'), icon: Icon(Icons.edit_rounded)),
                ],
                selected: {_isManualMode},
                onSelectionChanged: (selection) => setState(() {
                  _isManualMode = selection.first;
                  _textController.clear();
                }),
              ),

              const SizedBox(height: 12),

              // ── Ação de sorteio (condicional) ──────────────────────
              if (!_isManualMode)
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    icon: const Icon(Icons.shuffle_rounded),
                    label: const Text('SORTEAR', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 18)),
                    onPressed: bingoProvider.drawRandomNumber,
                  ),
                )
              else
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _textController,
                        textAlign: TextAlign.center,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(
                          labelText: 'Número (1 – 75)',
                          border: OutlineInputBorder(),
                        ),
                        onSubmitted: (_) => _confirmManual(bingoProvider),
                      ),
                    ),
                    const SizedBox(width: 10),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 28),
                        textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                      onPressed: () => _confirmManual(bingoProvider),
                      child: const Text('ADICIONAR'),
                    ),
                  ],
                ),

              const SizedBox(height: 20),

              // ── Último número ──────────────────────────────────────
              const Text('Último número sorteado:', style: TextStyle(fontSize: 16)),
              Selector<BingoProvider, int?>(
                selector: (ctx, provider) => provider.drawnNumbers.isNotEmpty ? provider.drawnNumbers.last : null,
                builder: (ctx, lastNumber, _) => Text('${lastNumber ?? '-'}', style: const TextStyle(fontSize: 48, fontWeight: FontWeight.bold)),
              ),

//<-- MUDANÇA: patrocinador 
              Consumer<BingoProvider>(
  builder: (context, provider, _) {
    final sponsor = provider.currentSponsor;

    if (sponsor == null) return const SizedBox();

    return Column(
      children: [
        const SizedBox(height: 10),
        const Text(
          "Patrocinador deste número:",
          style: TextStyle(fontSize: 14, color: Colors.grey),
        ),
        const SizedBox(height: 8),
        Image.network(
  sponsor.image,
  height: 80,
  errorBuilder: (_, __, ___) => const Icon(Icons.image_not_supported),
),
        const SizedBox(height: 5),
        Text(
          sponsor.name,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.green,
          ),
        ),
      ],
    );
  },
),

              const SizedBox(height: 6),
              Selector<BingoProvider, int>(
                selector: (ctx, provider) => provider.availableNumbers.length,
                builder: (ctx, count, _) => Text('$count números restantes'),
              ),
              const Divider(height: 30),

              // ── Grade de números sorteados ─────────────────────────
              Expanded(
                child: Consumer<BingoProvider>(
                  builder: (ctx, provider, _) {
                    if (provider.drawnNumbers.isEmpty) return const Center(child: Text('Nenhum número sorteado ainda.'));
                    final reversedList = provider.drawnNumbers.reversed.toList();
                    return GridView.builder(
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 8, mainAxisSpacing: 4, crossAxisSpacing: 4),
                      itemCount: reversedList.length,
                      itemBuilder: (ctx, index) => CircleAvatar(
                        backgroundColor: Colors.indigo,
                        child: Text(reversedList[index].toString(), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                      ),
                    );
                  },
                ),
              ),

              const SizedBox(height: 10),

              // ── Botão BINGO! ───────────────────────────────────────
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(backgroundColor: Colors.amber, foregroundColor: Colors.black),
                  onPressed: () => _showBingoWinnerDialog(bingoProvider),
                  child: const Padding(padding: EdgeInsets.symmetric(vertical: 16.0), child: Text('BINGO!', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold))),
                ),
              ),

              const SizedBox(height: 10),

              // ── Encerrar / Iniciar Nova ────────────────────────────
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(foregroundColor: Colors.red, side: const BorderSide(color: Colors.red)),
                      onPressed: () async {
                        final navigator = Navigator.of(context);
                        final confirmed = await _showFinishConfirmationDialog();
                        if (!mounted) return;
                        if (confirmed) {
                          await bingoProvider.finishCurrentSession();
                          bingoProvider.reset();
                          navigator.pop();
                        }
                      },
                      child: const Text('Encerrar Sessão'),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () async {
                        final navigator = Navigator.of(context);
                        final confirmed = await _showFinishConfirmationDialog();
                        if (!mounted) return;
                        if (confirmed){
                          final oldId = bingoProvider.sessionId;
                          await bingoProvider.finishCurrentSession();
                          bingoProvider.reset();
                          navigator.pushReplacement(
                            MaterialPageRoute(
                              builder: (_) => CreateSessionScreen(oldSessionId: oldId)));
                        }
                      },
                      child: const Text('Iniciar Nova'),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
          },
        ),
      ),
    );
  }

  void _confirmManual(BingoProvider bingoProvider) {
    final number = int.tryParse(_textController.text);
    if (number != null && number >= 1 && number <= 75) {
      bingoProvider.drawNumber(number);
      _textController.clear();
      FocusScope.of(context).unfocus();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Número inválido. Digite um valor entre 1 e 75.'), duration: Duration(seconds: 2)),
      );
    }
  }

  void _showSettingsSheet(BingoProvider provider) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => _SessionSettingsSheet(
        provider: provider,
        onLinkDisplay: () => _showLinkDisplayDialog(provider),
      ),
    );
  }
}

// ── Overlay de BINGO chamado ───────────────────────────────────────────────
class _BingoCalledOverlay extends StatefulWidget {
  final List<String> winners;
  final VoidCallback onDismiss;
  final VoidCallback onFinish;
  final VoidCallback onNewSession;
  final BingoProvider provider;

  const _BingoCalledOverlay({
    required this.winners,
    required this.onDismiss,
    required this.onFinish,
    required this.onNewSession,
    required this.provider,
  });

  @override
  State<_BingoCalledOverlay> createState() => _BingoCalledOverlayState();
}

class _BingoCalledOverlayState extends State<_BingoCalledOverlay> {
  Timer? _timer;
  late int _initialDrawnCount;

  @override
  void initState() {
    super.initState();
    _initialDrawnCount = widget.provider.drawnNumbers.length;
    _timer = Timer(const Duration(seconds: 35), _dismiss);
    widget.provider.addListener(_onProviderChanged);
  }

  void _onProviderChanged() {
    if (widget.provider.drawnNumbers.length > _initialDrawnCount) {
      _dismiss();
    }
  }

  void _dismiss() {
    _timer?.cancel();
    _timer = null;
    widget.provider.removeListener(_onProviderChanged);
    widget.onDismiss();
  }

  @override
  void dispose() {
    _timer?.cancel();
    widget.provider.removeListener(_onProviderChanged);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFF0d1b2a),
      child: SafeArea(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('🎉', style: TextStyle(fontSize: 72)),
            const SizedBox(height: 12),
            const Text(
              'BINGO!',
              style: TextStyle(
                fontSize: 72,
                fontWeight: FontWeight.bold,
                color: Color(0xFFFFD700),
                shadows: [Shadow(blurRadius: 20, color: Colors.orangeAccent)],
              ),
            ),
            const SizedBox(height: 24),
            if (widget.winners.isNotEmpty) ...[
              const Text('Ganhador(es):', style: TextStyle(fontSize: 16, color: Colors.white70)),
              const SizedBox(height: 8),
              Text(
                widget.winners.join(', '),
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white),
                textAlign: TextAlign.center,
              ),
            ],
            const SizedBox(height: 48),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 32),
              child: Column(
                children: [
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      icon: const Icon(Icons.add_circle_outline_rounded),
                      label: const Text('Iniciar Nova Rodada', style: TextStyle(fontSize: 16)),
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        backgroundColor: Colors.indigo,
                        foregroundColor: Colors.white,
                      ),
                      onPressed: widget.onNewSession,
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      icon: const Icon(Icons.stop_circle_outlined),
                      label: const Text('Encerrar Sessão', style: TextStyle(fontSize: 16)),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        foregroundColor: Colors.redAccent,
                        side: const BorderSide(color: Colors.redAccent),
                      ),
                      onPressed: widget.onFinish,
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextButton(
                    onPressed: _dismiss,
                    child: const Text('Continuar Jogo', style: TextStyle(color: Colors.white54)),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Painel de Configurações da Sessão ──────────────────────────────────────
class _SessionSettingsSheet extends StatefulWidget {
  final BingoProvider provider;
  final VoidCallback onLinkDisplay;
  const _SessionSettingsSheet({required this.provider, required this.onLinkDisplay});

  @override
  State<_SessionSettingsSheet> createState() => _SessionSettingsSheetState();
}

class _SessionSettingsSheetState extends State<_SessionSettingsSheet> {
  late bool _showPlayers;
  late bool _showProb;
  late List<String> _modes;

  static const _modeOptions = [
    ('vertical',   'Vertical'),
    ('horizontal', 'Horizontal'),
    ('diagonal',   'Diagonal'),
    ('fullCard',   'Cartela Cheia'),
  ];

  static const _columnRanges = [
    ('B', 1,  15),
    ('I', 16, 30),
    ('N', 31, 45),
    ('G', 46, 60),
    ('O', 61, 75),
  ];

  @override
  void initState() {
    super.initState();
    _showPlayers = widget.provider.showOnlinePlayers;
    _showProb    = widget.provider.showProbability;
    _modes       = List.from(widget.provider.probabilityModes);
  }

  void _apply() {
    widget.provider.updateSettings(
      showOnlinePlayers: _showPlayers,
      showProbability: _showProb,
      probabilityModes: List.from(_modes),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: widget.provider,
      builder: (ctx, _) {
        final provider = widget.provider;

        return Padding(
            padding: EdgeInsets.only(
              left: 24, right: 24, top: 24,
              bottom: MediaQuery.of(context).viewInsets.bottom + 24,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Título ─────────────────────────────────────────
                  Row(
                    children: [
                      const Icon(Icons.tune_rounded, color: Colors.indigo),
                      const SizedBox(width: 8),
                      const Text('Configurações da Sessão', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      const Spacer(),
                      IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.of(context).pop()),
                    ],
                  ),
                  const Divider(),

                  // ── Card: Código da Sessão / Vincular Display ──────
                  InkWell(
                    onTap: () {
                      Navigator.of(context).pop(); // fecha o sheet primeiro
                      widget.onLinkDisplay();
                    },
                    borderRadius: BorderRadius.circular(10),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        color: Colors.indigo.withValues(alpha:0.7),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.indigo.withValues(alpha:0.2)),
                      ),
                      child: Row(
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Código da Sessão', style: TextStyle(fontSize: 11, color: Colors.grey[600])),
                              Text(
                                provider.shortSessionId ?? '...',
                                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, letterSpacing: 4, color: Colors.indigo),
                              ),
                            ],
                          ),
                          const Spacer(),
                          const Icon(Icons.cast_rounded, color: Colors.indigo),
                          const SizedBox(width: 6),
                          const Text('Vincular Display', style: TextStyle(color: Colors.indigo, fontSize: 13, fontWeight: FontWeight.w500)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Divider(),

                  // ── Exibir jogadores online ────────────────────────
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Exibir Jogadores Online'),
                    subtitle: const Text('Mostra a lista de espectadores no painel do navegador'),
                    value: _showPlayers,
                    onChanged: (v) { setState(() => _showPlayers = v); _apply(); },
                  ),

                  // Lista em realtime (sempre visível para o operador)
                  _OnlinePlayersList(viewers: provider.onlineViewers),

                  const SizedBox(height: 4),

                  // ── Mostrar probabilidade ──────────────────────────
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Mostrar Probabilidade'),
                    subtitle: const Text('Exibe estatísticas de sorteio no painel do navegador'),
                    value: _showProb,
                    onChanged: (v) { setState(() => _showProb = v); _apply(); },
                  ),

                  // ── Modos ─────────────────────────────────────────
                  AnimatedCrossFade(
                    duration: const Duration(milliseconds: 200),
                    crossFadeState: _showProb ? CrossFadeState.showFirst : CrossFadeState.showSecond,
                    firstChild: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Padding(
                          padding: EdgeInsets.only(left: 4, top: 4, bottom: 4),
                          child: Text('Tipo de vitória monitorado:', style: TextStyle(fontSize: 13, color: Colors.grey)),
                        ),
                        Wrap(
                          spacing: 8,
                          runSpacing: 0,
                          children: _modeOptions.map((opt) {
                            final key   = opt.$1;
                            final label = opt.$2;
                            final active = _modes.contains(key);
                            return FilterChip(
                              label: Text(label),
                              selected: active,
                              onSelected: (on) {
                                setState(() {
                                  on ? _modes.add(key) : _modes.remove(key);
                                  if (_modes.isEmpty) _modes.add('horizontal');
                                });
                                _apply();
                              },
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 8),
                      ],
                    ),
                    secondChild: const SizedBox.shrink(),
                  ),

                  const Divider(),

                  // ── Estatísticas de possíveis ganhadores ──────────
                  const Padding(
                    padding: EdgeInsets.only(bottom: 8.0),
                    child: Text('Estatísticas por Coluna', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                  ),
                  ..._columnRanges.map((col) {
                    final letter = col.$1;
                    final from   = col.$2;
                    final to     = col.$3;
                    final total  = to - from + 1;
                    final drawn  = provider.drawnNumbers.where((n) => n >= from && n <= to).length;
                    final pct    = drawn / total;
                    final color  = pct >= 0.8
                        ? Colors.red
                        : pct >= 0.6
                            ? Colors.orange
                            : Colors.indigo;
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 8.0),
                      child: Row(
                        children: [
                          SizedBox(
                            width: 20,
                            child: Text(letter, style: TextStyle(fontWeight: FontWeight.bold, color: color)),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(4),
                              child: LinearProgressIndicator(
                                value: pct,
                                minHeight: 10,
                                backgroundColor: Colors.grey.shade200,
                                valueColor: AlwaysStoppedAnimation<Color>(color),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text('$drawn/$total', style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.bold)),
                          if (pct >= 0.8)
                            const Padding(
                              padding: EdgeInsets.only(left: 4),
                              child: Icon(Icons.warning_amber_rounded, size: 14, color: Colors.red),
                            ),
                        ],
                      ),
                    );
                  }),

                  Padding(
                    padding: const EdgeInsets.only(top: 4, bottom: 12),
                    child: Text(
                      'Total: ${provider.drawnNumbers.length}/75 (${(provider.drawnNumbers.length / 75 * 100).toStringAsFixed(0)}%)',
                      style: const TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
    );
  }
}

// Widget isolado para a lista de jogadores online (evita rebuild do sheet inteiro)
class _OnlinePlayersList extends StatelessWidget {
  final List<String> viewers;
  const _OnlinePlayersList({required this.viewers});

  @override
  Widget build(BuildContext context) {
    if (viewers.isEmpty) {
      return const Padding(
        padding: EdgeInsets.only(left: 4, bottom: 4),
        child: Text('Nenhum espectador online no momento.', style: TextStyle(fontSize: 12, color: Colors.grey)),
      );
    }
    return Container(
      margin: const EdgeInsets.only(bottom: 4),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.indigo.withValues(alpha:0.06),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.indigo.withValues(alpha:0.15)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.people_rounded, size: 14, color: Colors.indigo),
              const SizedBox(width: 4),
              Text('${viewers.length} online agora', style: const TextStyle(fontSize: 12, color: Colors.indigo, fontWeight: FontWeight.bold)),
            ],
          ),
          const SizedBox(height: 4),
          Wrap(
            spacing: 6,
            runSpacing: 4,
            children: viewers.map((name) => Chip(
              label: Text(name, style: const TextStyle(fontSize: 11)),
              visualDensity: VisualDensity.compact,
              padding: EdgeInsets.zero,
              materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
            )).toList(),
          ),
        ],
      ),
    );
  }
}