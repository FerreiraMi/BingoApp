import 'dart:convert';
import 'package:bingou/utils/constants.dart';
import 'package:flutter/material.dart';
import 'package:web_socket_channel/web_socket_channel.dart';

class ViewerScreen extends StatefulWidget {
  final String shortId;

  const ViewerScreen({Key? key, required this.shortId}) : super(key: key);

  @override
  _ViewerScreenState createState() => _ViewerScreenState();
}

class _ViewerScreenState extends State<ViewerScreen> {
  WebSocketChannel? _channel;
  final List<int> _drawnNumbers = [];
  bool _isLoading = true;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    _connect();
  }

  void _connect() {
    try {
      _channel = WebSocketChannel.connect(
        Uri.parse(AppConstants.WEBSOCKET_URL),
      );

      // Listener do WebSocket
      _channel!.stream.listen(
        (message) {
          final data = json.decode(message);
          if (data['type'] == 'new_number') {
            final newNumber = data['number'] as int;
            if (mounted && !_drawnNumbers.contains(newNumber)) {
              setState(() {
                _drawnNumbers.add(newNumber);
                _drawnNumbers.sort(); // Mantém a lista ordenada
              });
            }
          }
        },
        onDone: () => setState(() => _errorMessage = "Conexão encerrada."),
        onError: (error) => setState(() => _errorMessage = "Erro de conexão."),
      );

      // Inscreve-se na sessão após conectar
      _channel!.sink.add(json.encode({
        'type': 'subscribe',
        'sessionId': widget.shortId // O backend precisa ser ajustado para aceitar shortId aqui também
      }));

      // Simula um carregamento inicial (em um app real, faríamos uma chamada HTTP aqui para pegar os números já sorteados)
      setState(() => _isLoading = false);

    } catch (e) {
      setState(() {
        _isLoading = false;
        _errorMessage = "Não foi possível conectar.";
      });
    }
  }

  @override
  void dispose() {
    _channel?.sink.close();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Acompanhando Bingo #${widget.shortId}')),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _errorMessage.isNotEmpty
              ? Center(child: Text(_errorMessage, style: TextStyle(color: Colors.red)))
              : Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: GridView.builder(
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 6, mainAxisSpacing: 8, crossAxisSpacing: 8),
                    itemCount: _drawnNumbers.length,
                    itemBuilder: (ctx, index) {
                      return CircleAvatar(
                        backgroundColor: Colors.indigo,
                        child: Text(
                          _drawnNumbers[index].toString(),
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}