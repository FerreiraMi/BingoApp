import 'package:flutter/material.dart';
import 'package:bingou/providers/auth_provider.dart';
import 'package:bingou/providers/bingo_provider.dart';
import 'package:bingou/screens/bingo_control_screen.dart';
import 'package:bingou/screens/session_details_screen.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  _HistoryScreenState createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  late Future<void> _historyFuture;

  @override
  void initState() {
    super.initState();
    // ===============================================
    // CORREÇÃO AQUI: Inicialize o Future diretamente.
    // ===============================================
    // Esta é a forma correta. O initState é chamado uma vez, antes do primeiro build.
    // Garantimos que _historyFuture terá um valor antes do FutureBuilder tentar usá-lo.
    _historyFuture = Provider.of<AuthProvider>(context, listen: false).fetchHistory();
  }

  // Função para ser chamada pelo RefreshIndicator ou outras ações
  Future<void> _refreshHistory() async {
    // Usamos setState aqui para notificar o widget que o Future mudou e
    // o FutureBuilder precisa ser reconstruído com a nova instância do Future.
    setState(() {
      _historyFuture = Provider.of<AuthProvider>(context, listen: false).fetchHistory();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Histórico de Sessões'),
      ),
      body: RefreshIndicator(
        onRefresh: _refreshHistory,
        child: FutureBuilder(
          future: _historyFuture, // Agora ele sempre terá um valor aqui
          builder: (ctx, snapshot) {
            // A lógica do builder permanece a mesma
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snapshot.error != null) {
              return const Center(child: Text('Ocorreu um erro ao carregar o histórico.'));
            }
            
            return Consumer<AuthProvider>(
              builder: (ctx, authProvider, _) {
                if (authProvider.history.isEmpty) {
                  return Center(
                    child: Text(
                      'Nenhuma sessão encontrada.\nUse a aba "Nova Sessão" para criar uma!',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 16, color: Colors.grey[600]),
                    ),
                  );
                }

                return ListView.builder(
                  itemCount: authProvider.history.length,
                  itemBuilder: (ctx, i) {
                    final session = authProvider.history[i];
                    final date = DateFormat('dd/MM/yyyy HH:mm').format(DateTime.parse(session['createdAt']));
                    final bool isActive = session['status'] == 'active';

                    return Card(
                      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: isActive ? Colors.green : Colors.grey,
                          child: Icon(isActive ? Icons.play_arrow_rounded : Icons.check_circle_outline, color: Colors.white),
                        ),
                        title: Text(session['sessionName'], style: const TextStyle(fontWeight: FontWeight.bold)),
                        subtitle: Text('Sorteados: ${(session['drawnNumbers'] as List).length} | Criado em: $date'),
                        trailing: Chip(
                          label: Text(isActive ? 'Ativa' : 'Encerrada', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                          backgroundColor: isActive ? Colors.green.shade600 : Colors.blueGrey,
                          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          labelPadding: const EdgeInsets.only(left: 4, right: 2),
                        ),
                        onTap: () {
                          if (isActive) {
                            Provider.of<BingoProvider>(context, listen: false).reloadSession(session);
                            Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => const BingoControlScreen()),
                            ).then((_) => _refreshHistory());
                          } else {
                            Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => SessionDetailsScreen(session: session)),
                            );
                          }
                        },
                      ),
                    );
                  },
                );
              },
            );
          },
        ),
      ),
    );
  }
}