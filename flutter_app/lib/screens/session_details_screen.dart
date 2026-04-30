import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class SessionDetailsScreen extends StatelessWidget {
  // A tela recebe os dados completos da sessão através do construtor
  final Map<String, dynamic> session;

  const SessionDetailsScreen({Key? key, required this.session}) : super(key: key);

  // Função auxiliar para obter a letra de um número (reutilizada aqui)
  String getBingoLetter(int number) {
    if (number >= 1 && number <= 15) return 'B';
    if (number >= 16 && number <= 30) return 'I';
    if (number >= 31 && number <= 45) return 'N';
    if (number >= 46 && number <= 60) return 'G';
    if (number >= 61 && number <= 75) return 'O';
    return '';
  }

  @override
  Widget build(BuildContext context) {
    // Extrai a lista de números sorteados da sessão
    final List<int> drawnNumbers = List<int>.from(session['drawnNumbers'])..sort();
    final date = DateFormat('dd/MM/yyyy \'às\' HH:mm').format(DateTime.parse(session['createdAt']));

    return Scaffold(
      appBar: AppBar(
        title: Text(session['sessionName']),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            // Informações do Cabeçalho
            Text('Rodada: ${session['round']} | Prêmio: ${session['prize']}', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w500)),
            const SizedBox(height: 8),
            Text('Realizada em: $date', style: TextStyle(fontSize: 14, color: Colors.grey[600])),
            const Divider(height: 30, thickness: 1),

            // Painel B-I-N-G-O
            Text('Números Sorteados (${drawnNumbers.length})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            Expanded(
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildBingoColumn('B', 1, 15, drawnNumbers),
                  _buildBingoColumn('I', 16, 30, drawnNumbers),
                  _buildBingoColumn('N', 31, 45, drawnNumbers),
                  _buildBingoColumn('G', 46, 60, drawnNumbers),
                  _buildBingoColumn('O', 61, 75, drawnNumbers),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // Widget auxiliar para construir cada coluna B-I-N-G-O
  Widget _buildBingoColumn(String letter, int start, int end, List<int> allDrawnNumbers) {
    // Filtra apenas os números que pertencem a esta coluna
    final columnNumbers = allDrawnNumbers.where((n) => n >= start && n <= end).toList();

    return Expanded(
      child: Column(
        children: [
          // Cabeçalho da Letra
          Text(letter, style: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.indigo)),
          const SizedBox(height: 8),
          // Grid com os números
          Expanded(
            child: ListView.builder(
              itemCount: columnNumbers.length,
              itemBuilder: (context, index) {
                return Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4.0),
                    child: CircleAvatar(
                      backgroundColor: Colors.grey[300],
                      child: Text(
                        columnNumbers[index].toString(),
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.black87),
                      ),
                    ),
                  ),
                );
              },
            ),
          )
        ],
      ),
    );
  }
}