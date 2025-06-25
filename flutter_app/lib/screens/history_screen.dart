import 'package:flutter/material.dart';
import 'package:flutter_app/providers/auth_provider.dart';
import 'package:flutter_app/providers/bingo_provider.dart';
import 'package:flutter_app/screens/bingo_control_screen.dart';
import 'package:flutter_app/screens/session_details_screen.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

class HistoryScreen extends StatefulWidget {
  @override
  _HistoryScreenState createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  late Future<void> _historyFuture;

  @override
  void initState() {
    super.initState();
    // Inicia o fetch do histórico assim que a tela é construída
    _historyFuture = Provider.of<AuthProvider>(context, listen: false).fetchHistory();
  }

  // Função para ser chamada pelo RefreshIndicator ou outras ações
  Future<void> _refreshHistory() async {
    setState(() {
      _historyFuture = Provider.of<AuthProvider>(context, listen: false).fetchHistory();
    });
  }

  @override
  Widget build(BuildContext context) {
    // Esta tela agora tem seu próprio Scaffold para funcionar como uma aba independente
    return Scaffold(
      appBar: AppBar(
        title: Text('Histórico de Sessões'),
      ),
      body: RefreshIndicator(
        onRefresh: _refreshHistory,
        child: FutureBuilder(
          future: _historyFuture,
          builder: (ctx, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return Center(child: CircularProgressIndicator());
            }
            if (snapshot.error != null) {
              return Center(child: Text('Ocorreu um erro ao carregar o histórico.'));
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

                // A lista já vem ordenada do backend (mais recente primeiro)
                return ListView.builder(
                  itemCount: authProvider.history.length,
                  itemBuilder: (ctx, i) {
                    final session = authProvider.history[i];
                    final date = DateFormat('dd/MM/yyyy HH:mm').format(DateTime.parse(session['createdAt']));
                    final bool isActive = session['status'] == 'active';

                    return Card(
                      margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: isActive ? Colors.green : Colors.grey,
                          child: Icon(isActive ? Icons.play_arrow_rounded : Icons.check_circle_outline, color: Colors.white),
                        ),
                        title: Text(session['sessionName'], style: TextStyle(fontWeight: FontWeight.bold)),
                        subtitle: Text('Sorteados: ${(session['drawnNumbers'] as List).length} | Criado em: $date'),
                        trailing: Chip(
                          label: Text(isActive ? 'Ativa' : 'Encerrada', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                          backgroundColor: isActive ? Colors.green.shade600 : Colors.blueGrey,
                          padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          labelPadding: EdgeInsets.only(left: 4, right: 2), // Ajuste fino
                        ),
                        onTap: () {
                          if (isActive) {
                            // Se está ATIVA, recarrega o estado e vai para a tela de controle
                            Provider.of<BingoProvider>(context, listen: false).reloadSession(session);
                            Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => BingoControlScreen()),
                            ).then((_) => _refreshHistory()); // Atualiza quando voltar
                          } else {
                            // Se está ENCERRADA, vai para a tela de detalhes (somente leitura)
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