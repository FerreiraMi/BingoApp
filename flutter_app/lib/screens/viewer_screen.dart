import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:web_socket_channel/web_socket_channel.dart';
import 'package:bingou/utils/constants.dart';

class ViewerScreen extends StatefulWidget {
  final String shortId;
  const ViewerScreen({Key? key, required this.shortId}) : super(key: key);
  @override
  _ViewerScreenState createState() => _ViewerScreenState();
}

class _ViewerScreenState extends State<ViewerScreen> {
  WebSocketChannel? _channel;
  StreamSubscription? _socketSubscription;

  // Estado da sessão
  Map<String, dynamic>? _sessionData;
  List<int> _drawnNumbers = [];
  bool _isLoading = true;
  String _errorMessage = '';
  String _viewerName = 'Espectador Anônimo';

  @override
  void initState() {
    super.initState();
    // Inicia o processo de busca e conexão
    _initialize();
  }

  Future<void> _initialize() async {
    // Pede o nome do usuário primeiro
    await _askForName();
    // Depois busca os dados da sessão
    await _fetchInitialSessionData();
    // Só então conecta ao WebSocket
    if (_errorMessage.isEmpty) {
      _connectWebSocket();
    }
  }

  Future<void> _askForName() async {
    final nameController = TextEditingController();
    final name = await showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: Text('Identifique-se'),
        content: TextField(
          controller: nameController,
          decoration: InputDecoration(hintText: 'Digite seu nome...'),
        ),
        actions: [
          ElevatedButton(
            child: Text('Entrar'),
            onPressed: () => Navigator.pop(ctx, nameController.text),
          )
        ],
      ),
    );
    if (name != null && name.isNotEmpty) {
      setState(() => _viewerName = name);
    }
  }

  Future<void> _fetchInitialSessionData() async {
    try {
      final response = await http.get(
        Uri.parse('${AppConstants.API_URL}/session?shortId=${widget.shortId}'),
      );
      if (response.statusCode == 200) {
        setState(() {
          _sessionData = json.decode(response.body);
          _drawnNumbers = List<int>.from(_sessionData!['drawnNumbers'])..sort();
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = "Sessão não encontrada ou inválida.";
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = "Erro de conexão ao buscar sessão.";
        _isLoading = false;
      });
    }
  }

  void _connectWebSocket() {
    _channel = WebSocketChannel.connect(Uri.parse(AppConstants.WEBSOCKET_URL));
    _socketSubscription = _channel!.stream.listen(
      (message) {
        final data = json.decode(message);
        if (data['type'] == 'new_number') {
          final newNumber = data['number'] as int;
          if (mounted && !_drawnNumbers.contains(newNumber)) {
            setState(() { _drawnNumbers.add(newNumber); _drawnNumbers.sort(); });
          }
        }
      },
      onDone: () => setState(() => _errorMessage = "Conexão encerrada."),
      onError: (error) => setState(() => _errorMessage = "Erro de conexão."),
    );

    // Envia a mensagem de que um novo espectador entrou
    _channel!.sink.add(json.encode({
      'type': 'viewer_joined',
      'sessionId': widget.shortId,
      'viewerName': _viewerName,
    }));
  }

  @override
  void dispose() {
    _socketSubscription?.cancel();
    _channel?.sink.close();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_sessionData?['sessionName'] ?? 'Acompanhando Bingo')),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _errorMessage.isNotEmpty
              ? Center(child: Text(_errorMessage, style: TextStyle(color: Colors.red, fontSize: 18)))
              : Column(
                  children: [
                    Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Text('Rodada: ${_sessionData!['round']} | Prêmio: ${_sessionData!['prize']}', style: TextStyle(fontSize: 18)),
                    ),
                    Expanded(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8.0),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _buildBingoColumn('B', 1, 15),
                            _buildBingoColumn('I', 16, 30),
                            _buildBingoColumn('N', 31, 45),
                            _buildBingoColumn('G', 46, 60),
                            _buildBingoColumn('O', 61, 75),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildBingoColumn(String letter, int start, int end) {
    final columnNumbers = _drawnNumbers.where((n) => n >= start && n <= end).toList();
    return Expanded(
      child: Column(
        children: [
          Text(letter, style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.indigo)),
          SizedBox(height: 8),
          Expanded(
            child: ListView.builder(
              itemCount: 15, // Sempre mostra 15 posições
              itemBuilder: (context, index) {
                final number = start + index;
                final isDrawn = columnNumbers.contains(number);
                return Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 2.0),
                    child: CircleAvatar(
                      radius: 18,
                      backgroundColor: isDrawn ? Colors.green : Colors.grey[300],
                      child: Text(
                        number.toString(),
                        style: TextStyle(fontWeight: FontWeight.bold, color: isDrawn ? Colors.white : Colors.black54),
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