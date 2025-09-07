import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:bingou/providers/bingo_provider.dart';
import 'package:bingou/screens/create_session_screen.dart';
import 'package:bingou/utils/constants.dart';
import 'package:qr_flutter/qr_flutter.dart';

class BingoControlScreen extends StatelessWidget {
  const BingoControlScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final bingoProvider = Provider.of<BingoProvider>(context, listen: false);
    final textController = TextEditingController();

    // Função auxiliar para o diálogo de confirmação de ENCERRAMENTO
    Future<bool> showFinishConfirmationDialog() async {
      return await showDialog<bool>(
            context: context,
            builder: (context) => AlertDialog(
              title: const Text('Encerrar sessão?'),
              content: const Text('Deseja realmente marcar esta sessão como encerrada e finalizá-la? Esta ação não pode ser desfeita.'),
              actions: [
                TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancelar')),
                TextButton(
                  onPressed: () => Navigator.pop(context, true),
                  child: const Text('Encerrar', style: TextStyle(color: Colors.red)),
                ),
              ],
            ),
          ) ?? false;
    }

    // Função auxiliar para o diálogo de REGISTRO DE GANHADORES
    Future<void> showBingoWinnerDialog() async {
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
                if (formKey.currentState!.validate()) {
                  Navigator.pop(ctx, true);
                }
              },
            ),
          ],
        ),
      );

      if (confirmed ?? false) {
        final winners = winnersController.text.split(',').map((name) => name.trim()).where((name) => name.isNotEmpty).toList();
        bingoProvider.callBingo(winners: winners);
      }
    }

    return WillPopScope(
      onWillPop: () async {
        final confirmed = await showFinishConfirmationDialog();
        if (confirmed) {
          await bingoProvider.finishCurrentSession();
          bingoProvider.reset();
        }
        return confirmed;
      },
      child: Scaffold(
        appBar: AppBar(
          leading: IconButton(
            icon: const Icon(Icons.close),
            tooltip: 'Voltar para o histórico (manter sessão ativa)',
            onPressed: () {
              Navigator.of(context).pop();
            },
          ),
          title: Consumer<BingoProvider>(
            builder: (ctx, provider, _) => Text(provider.sessionName ?? 'Controle do Bingo'),
          ),
        ),
        body: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              GestureDetector(
                onTap: () {
                  final shareUrl = '${AppConstants.WEB_PAGE_URL}/${bingoProvider.shortSessionId ?? ''}';
                  showDialog(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      content: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Text('Aponte a câmera para o QR Code', textAlign: TextAlign.center, style: TextStyle(fontSize: 16)),
                          const SizedBox(height: 20),
                          SizedBox(
                            width: 200,
                            height: 200,
                            // ===============================================
                            // CORREÇÃO FINAL E DEFINITIVA AQUI
                            // ===============================================
                            child: QrImageView(
                              data: shareUrl,
                              version: QrVersions.auto,
                            ),
                          ),
                          const SizedBox(height: 20),
                          SelectableText(shareUrl, style: const TextStyle(fontSize: 12)),
                          const SizedBox(height: 20),

                          // Botão para copiar o link
                          ElevatedButton.icon(
                            icon: const Icon(Icons.copy, size: 18),
                            label: const Text('Copiar Link'),
                            onPressed: () {
                              Clipboard.setData(ClipboardData(text: shareUrl));
                              Navigator.of(ctx).pop(); // Fecha o diálogo
                              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Link copiado para a área de transferência!')));
                            },
                          )
                          //end
                        ],
                      ),
                    ),
                  );
                },
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(color: Colors.indigo.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Código da Sessão:', style: TextStyle(fontSize: 12, color: Colors.grey[700])),
                            Consumer<BingoProvider>(
                              builder: (ctx, provider, _) => Text(
                                provider.shortSessionId ?? '...',
                                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, letterSpacing: 2),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.qr_code_2_rounded, size: 48, color: Colors.indigo),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),
              // O resto do código permanece exatamente o mesmo
              // ...
              Row(
                children: [
                  Expanded(child: ElevatedButton(onPressed: bingoProvider.drawRandomNumber, style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)), child: Text('SORTEAR'))),
                  const SizedBox(width: 10),
                  SizedBox(width: 80, child: TextField(controller: textController, textAlign: TextAlign.center, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Manual', border: OutlineInputBorder()))),
                  IconButton(
                    icon: const Icon(Icons.add),
                    onPressed: () {
                      final number = int.tryParse(textController.text);
                      if (number != null && number > 0 && number <= 75) {
                        bingoProvider.drawNumber(number); textController.clear(); FocusScope.of(context).unfocus();
                      } else { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Número inválido.'), duration: Duration(seconds: 1))); }
                    },
                  )
                ],
              ),
              const SizedBox(height: 20),
              const Text('Último número sorteado:', style: TextStyle(fontSize: 16)),
              Selector<BingoProvider, int?>(selector: (ctx, provider) => provider.drawnNumbers.isNotEmpty ? provider.drawnNumbers.last : null, builder: (ctx, lastNumber, _) => Text('${lastNumber ?? '-'}', style: const TextStyle(fontSize: 48, fontWeight: FontWeight.bold))),
              const SizedBox(height: 10),
              Selector<BingoProvider, int>(selector: (ctx, provider) => provider.availableNumbers.length, builder: (ctx, count, _) => Text('$count números restantes')),
              const Divider(height: 30),
              Expanded(
                child: Consumer<BingoProvider>(
                  builder: (ctx, provider, _) {
                    if (provider.drawnNumbers.isEmpty) { return const Center(child: Text('Nenhum número sorteado ainda.')); }
                    final reversedList = provider.drawnNumbers.reversed.toList();
                    return GridView.builder(
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 8, mainAxisSpacing: 4, crossAxisSpacing: 4),
                      itemCount: reversedList.length,
                      itemBuilder: (ctx, index) => CircleAvatar(backgroundColor: Colors.indigo, child: Text(reversedList[index].toString(), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
                    );
                  },
                ),
              ),
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(backgroundColor: Colors.amber, foregroundColor: Colors.black),
                  onPressed: showBingoWinnerDialog,
                  child: Padding(padding: const EdgeInsets.symmetric(vertical: 16.0), child: Text('BINGO!', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold))),
                ),
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(foregroundColor: Colors.red, side: const BorderSide(color: Colors.red)),
                      onPressed: () async {
                        final confirmed = await showFinishConfirmationDialog();
                        if (confirmed && context.mounted) {
                          await bingoProvider.finishCurrentSession();
                          bingoProvider.reset();
                          Navigator.of(context).pop();
                        }
                      },
                      child: Text('Encerrar Sessão'),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton(
                      child: const Text('Iniciar Nova'),
                      onPressed: () async {
                        final confirmed = await showFinishConfirmationDialog();
                        if (confirmed && context.mounted) {
                          final oldId = bingoProvider.sessionId;
                          await bingoProvider.finishCurrentSession();
                          bingoProvider.reset();
                          Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => CreateSessionScreen(oldSessionId: oldId)));
                        }
                      },
                    ),
                  ),
                ],
              )
            ],
          ),
        ),
      ),
    );
  }
}