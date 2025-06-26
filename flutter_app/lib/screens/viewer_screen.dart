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

  Map<String, dynamic>? _sessionData;
  List<int> _drawnNumbers = [];
  bool _isLoading = true;
  String _errorMessage = '';
  String _viewerName = 'Espectador Anônimo';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initialize();
    });
  }

  Future<void> _initialize() async {
    await _askForName();
    await _fetchInitialSessionData();
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
          autofocus: true,
          decoration: InputDecoration(hintText: 'Digite seu nome...'),
        ),
        actions: [
          ElevatedButton(
            child: Text('Entrar'),
            onPressed: () {
              if (nameController.text.trim().isNotEmpty) {
                Navigator.pop(ctx, nameController.text.trim());
              }
            },
          )
        ],
      ),
    );
    if (name != null) {
      setState(() => _viewerName = name);
    }
  }

  Future<void> _fetchInitialSessionData() async {
    setState(() => _isLoading = true);
    try {
      final response = await http.get(
        Uri.parse('${AppConstants.API_URL}/session?shortId=${widget.shortId}'),
      );
      if (response.statusCode == 200) {
        if (mounted) {
          setState(() {
            _sessionData = json.decode(response.body);
            _drawnNumbers = List<int>.from(_sessionData!['drawnNumbers'])..sort();
            _isLoading = false;
          });
        }
      } else {
        if (mounted) setState(() { _errorMessage = "Sessão não encontrada ou inválida."; _isLoading = false; });
      }
    } catch (e) {
      if (mounted) setState(() { _errorMessage = "Erro de conexão ao buscar sessão."; _isLoading = false; });
    }
  }

  void _connectWebSocket() {
    if (_sessionData == null) return;
    try {
      _channel = WebSocketChannel.connect(Uri.parse(AppConstants.WEBSOCKET_URL));
      _socketSubscription = _channel!.stream.listen(
        (message) {
          final data = json.decode(message);
          if (data['type'] == 'new_number') {
            final newNumber = data['number'] as int;
            if (mounted && !_drawnNumbers.contains(newNumber)) {
              setState(() { _drawnNumbers.add(newNumber); });
            }
          }
          // Opcional: ouvir por outros eventos, como 'bingo_called' para mostrar um alerta aqui também
        },
        onDone: () { if(mounted) setState(() => _errorMessage = "Conexão encerrada."); },
        onError: (error) { if(mounted) setState(() => _errorMessage = "Erro de conexão."); },
      );

      _channel!.sink.add(json.encode({
        'type': 'viewer_joined',
        'sessionId': widget.shortId,
        'viewerName': _viewerName,
      }));
    } catch (e) {
      if(mounted) setState(() => _errorMessage = "Não foi possível conectar ao servidor de tempo real.");
    }
  }

  // ===============================================
  // NOVO MÉTODO PARA CHAMAR O BINGO
  // ===============================================
  void _callBingo() {
    if (_channel != null) {
      // O backend espera o ID longo para salvar os ganhadores.
      // Precisamos enviar o ID longo da sessão.
      final longSessionId = _sessionData?['_id'];
      if (longSessionId == null) {
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Erro: ID da sessão não encontrado.')));
          return;
      }
      
      _channel!.sink.add(json.encode({
        'type': 'bingo_called',
        'sessionId': longSessionId,
        'winners': [_viewerName], // Envia o nome do espectador como um ganhador
      }));
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('BINGO! Seu chamado foi enviado.'),
          backgroundColor: Colors.green,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Não foi possível enviar. Verifique sua conexão.')));
    }
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
      // ===============================================
      // BOTÃO FLUTUANTE DE BINGO ADICIONADO
      // ===============================================
      floatingActionButton: _isLoading || _errorMessage.isNotEmpty
        ? null // Não mostra o botão se estiver carregando ou com erro
        : FloatingActionButton.extended(
            onPressed: _callBingo,
            label: Text('BINGO!', style: TextStyle(fontWeight: FontWeight.bold)),
            icon: Icon(Icons.celebration),
            backgroundColor: Colors.amber,
          ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerFloat,
      
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
                    SizedBox(height: 70), // Espaço para o FloatingActionButton não cobrir o conteúdo
                  ],
                ),
    );
  }

  Widget _buildBingoColumn(String letter, int start, int end) {
    final drawnNumbersInColumn = _drawnNumbers.where((n) => n >= start && n <= end).toList()..sort();
    return Expanded(
      child: Column(
        children: [
          Text(letter, style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.indigo)),
          SizedBox(height: 8),
          Expanded(
            child: ListView.builder(
              padding: EdgeInsets.zero,
              itemCount: drawnNumbersInColumn.length,
              itemBuilder: (context, index) {
                final number = drawnNumbersInColumn[index];
                return Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4.0),
                    child: CircleAvatar(
                      radius: 18,
                      backgroundColor: Colors.green.shade600,
                      child: Text(
                        number.toString(),
                        style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 14),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}