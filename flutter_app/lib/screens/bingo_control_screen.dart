import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:flutter_app/providers/bingo_provider.dart';
import 'package:flutter_app/screens/create_session_screen.dart';
import 'package:flutter_app/utils/constants.dart';

class BingoControlScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final bingoProvider = Provider.of<BingoProvider>(context, listen: false);
    final textController = TextEditingController();

    // Função auxiliar para o diálogo de confirmação de ENCERRAMENTO
    Future<bool> _showFinishConfirmationDialog() async {
      return await showDialog<bool>(
            context: context,
            builder: (context) => AlertDialog(
              title: Text('Encerrar sessão?'),
              content: Text('Deseja realmente marcar esta sessão como encerrada e finalizá-la? Esta ação não pode ser desfeita.'),
              actions: [
                TextButton(onPressed: () => Navigator.pop(context, false), child: Text('Cancelar')),
                TextButton(
                  onPressed: () => Navigator.pop(context, true),
                  child: Text('Encerrar', style: TextStyle(color: Colors.red)),
                ),
              ],
            ),
          ) ?? false;
    }

    // Função auxiliar para o diálogo de REGISTRO DE GANHADORES
    Future<void> _showBingoWinnerDialog() async {
      final winnersController = TextEditingController();
      final formKey = GlobalKey<FormState>();

      final confirmed = await showDialog<bool>(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          title: Text('BINGO! Registrar Ganhador(es)'),
          content: Form(
            key: formKey,
            child: TextFormField(
              controller: winnersController,
              decoration: InputDecoration(
                labelText: 'Nome(s) do(s) Ganhador(es)',
                hintText: 'Separe por vírgula se houver mais de um',
                border: OutlineInputBorder(),
              ),
              validator: (value) => value == null || value.isEmpty ? 'Por favor, insira pelo menos um nome.' : null,
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: Text('Cancelar')),
            ElevatedButton(
              child: Text('Confirmar Bingo'),
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
        final confirmed = await _showFinishConfirmationDialog();
        if (confirmed) {
          await bingoProvider.finishCurrentSession();
          bingoProvider.reset();
        }
        return confirmed;
      },
      child: Scaffold(
        appBar: AppBar(
          // CORRIGIDO: Este botão agora apenas volta, sem encerrar a sessão
          leading: IconButton(
            icon: Icon(Icons.close),
            tooltip: 'Voltar para o histórico (manter sessão ativa)',
            onPressed: () {
              Navigator.of(context).pop(true);
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
              // Painel de Compartilhamento da URL
              Container(
                padding: EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.indigo.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
                child: Row(
                  children: [
                    Expanded(
                      child: Consumer<BingoProvider>(
                        builder: (ctx, provider, _) => Text('URL para compartilhar:\n${AppConstants.WEB_PAGE_URL}/${provider.shortSessionId ?? ''}', style: TextStyle(fontSize: 12)),
                      ),
                    ),
                    IconButton(
                      icon: Icon(Icons.copy),
                      onPressed: () {
                        final shareUrl = '${AppConstants.WEB_PAGE_URL}/${bingoProvider.shortSessionId ?? ''}';
                        Clipboard.setData(ClipboardData(text: shareUrl));
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('URL copiada!'), duration: Duration(seconds: 1)));
                      },
                    )
                  ],
                ),
              ),
              SizedBox(height: 20),

              // Controles de Sorteio (Aleatório e Manual)
              Row(
                children: [
                  Expanded(child: ElevatedButton(onPressed: bingoProvider.drawRandomNumber, child: Text('SORTEAR'), style: ElevatedButton.styleFrom(padding: EdgeInsets.symmetric(vertical: 16)))),
                  SizedBox(width: 10),
                  SizedBox(width: 80, child: TextField(controller: textController, textAlign: TextAlign.center, keyboardType: TextInputType.number, decoration: InputDecoration(labelText: 'Manual', border: OutlineInputBorder()))),
                  IconButton(
                    icon: Icon(Icons.add),
                    onPressed: () {
                      final number = int.tryParse(textController.text);
                      if (number != null && number > 0 && number <= 75) {
                        bingoProvider.drawNumber(number); textController.clear(); FocusScope.of(context).unfocus();
                      } else { ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Número inválido.'), duration: Duration(seconds: 1))); }
                    },
                  )
                ],
              ),
              SizedBox(height: 20),

              // Painel de Status (Último número e contagem)
              Text('Último número sorteado:', style: TextStyle(fontSize: 16)),
              Selector<BingoProvider, int?>(selector: (ctx, provider) => provider.drawnNumbers.isNotEmpty ? provider.drawnNumbers.last : null, builder: (ctx, lastNumber, _) => Text('${lastNumber ?? '-'}', style: TextStyle(fontSize: 48, fontWeight: FontWeight.bold))),
              SizedBox(height: 10),
              Selector<BingoProvider, int>(selector: (ctx, provider) => provider.availableNumbers.length, builder: (ctx, count, _) => Text('$count números restantes')),
              Divider(height: 30),

              // Grid de Números Sorteados
              Expanded(
                child: Consumer<BingoProvider>(
                  builder: (ctx, provider, _) {
                    if (provider.drawnNumbers.isEmpty) { return Center(child: Text('Nenhum número sorteado ainda.')); }
                    final reversedList = provider.drawnNumbers.reversed.toList();
                    return GridView.builder(
                      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 8, mainAxisSpacing: 4, crossAxisSpacing: 4),
                      itemCount: reversedList.length,
                      itemBuilder: (ctx, index) => CircleAvatar(backgroundColor: Colors.indigo, child: Text(reversedList[index].toString(), style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold))),
                    );
                  },
                ),
              ),
              SizedBox(height: 10),
              
              // Botão BINGO! agora chama o novo diálogo
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  child: Padding(padding: const EdgeInsets.symmetric(vertical: 16.0), child: Text('BINGO!', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold))),
                  style: ElevatedButton.styleFrom(backgroundColor: Colors.amber, foregroundColor: Colors.black),
                  onPressed: _showBingoWinnerDialog, // Atualizado
                ),
              ),
              SizedBox(height: 10),
              
              // Botões de Ação na parte inferior (com o botão de encerrar permanente)
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      child: Text('Encerrar Sessão'),
                      style: OutlinedButton.styleFrom(foregroundColor: Colors.red, side: BorderSide(color: Colors.red)),
                      onPressed: () async {
                        final confirmed = await _showFinishConfirmationDialog();
                        if (confirmed && context.mounted) {
                          await bingoProvider.finishCurrentSession();
                          bingoProvider.reset();
                          Navigator.of(context).pop();
                        }
                      },
                    ),
                  ),
                  SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton(
                      child: Text('Iniciar Nova'),
                      onPressed: () async {
                        final confirmed = await _showFinishConfirmationDialog();
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