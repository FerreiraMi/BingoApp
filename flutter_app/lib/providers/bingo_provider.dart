// bingou/lib/providers/bingo_provider.dart
import 'dart:convert';
import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:web_socket_channel/web_socket_channel.dart';
import 'package:bingou/utils/constants.dart';

class BingoProvider with ChangeNotifier {
  String? sessionId;
  String? shortSessionId; 
  String? sessionName;
  List<int> drawnNumbers = [];
  List<int> availableNumbers = List.generate(75, (i) => i + 1);
  WebSocketChannel? _channel;
  bool _isProUser = false; // Mantenha para o caso de ter planos pagos no futuro
  bool isLoading = false;
  String? errorMessage;

  void setIsProUser(bool isPro) {
    _isProUser = isPro;
    notifyListeners();
  }

  Future<bool> createSession(String name, String round, String prize, String userId, {String? oldSessionId}) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    final url = Uri.parse('${AppConstants.API_URL}/session');
    try {
      final response = await http.post(
        url,
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'userId': userId, // Vincula a sessão ao usuário
          'sessionName': name,
          'round': round,
          'prize': prize,
          'isProUser': _isProUser,
        }),
      );
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        sessionId = data['sessionId'];
        shortSessionId = data['shortSessionId']; 
        sessionName = name;
        _connectWebSocket();

        // Se uma sessão antiga foi fornecida, envia o comando de redirect
        if (oldSessionId != null && _channel != null) {
          final newUrl = '${AppConstants.WEB_PAGE_URL}/${shortSessionId}'; 
          _channel!.sink.add(json.encode({
            'type': 'redirect',
            'targetSessionId': oldSessionId,
            'newUrl': newUrl,
          }));
          print("Comando de redirect enviado para a sessão $oldSessionId.");
        }

        isLoading = false;
        notifyListeners();
        return true;
      } else {
        errorMessage = "Erro no servidor: ${json.decode(response.body)['error']}";
      }
    } catch (e) {
      errorMessage = "Erro de conexão: $e";
    }
    
    isLoading = false;
    notifyListeners();
    return false;
  }

  void _connectWebSocket() {
    if (sessionId != null) {
      _channel = WebSocketChannel.connect(
        Uri.parse(AppConstants.WEBSOCKET_URL),
      );
      print("Conectado ao WebSocket para a sessão $sessionId");
    }
  }

  void drawNumber(int number) {
    if (availableNumbers.contains(number) && _channel != null) {
      availableNumbers.remove(number);
      drawnNumbers.add(number);
      
      _channel!.sink.add(json.encode({
        'sessionId': sessionId,
        'number': number,
      }));
      
      notifyListeners();
    }
  }

  void drawRandomNumber() {
    if (availableNumbers.isNotEmpty) {
      final random = Random();
      final index = random.nextInt(availableNumbers.length);
      final number = availableNumbers[index];
      drawNumber(number);
    }
  }

  void reset() {
    sessionId = null;
    sessionName = null;
    shortSessionId = null; 
    drawnNumbers = [];
    availableNumbers = List.generate(75, (i) => i + 1);
    _channel?.sink.close();
    _channel = null;
    isLoading = false;
    errorMessage = null;
    // Não notificamos aqui, pois a UI que chama o reset decide quando reconstruir
  }

  void callBingo({required List<String> winners}) {
    if (_channel != null && sessionId != null) {
      _channel!.sink.add(json.encode({
        'type': 'bingo_called', // O novo tipo de mensagem
        'sessionId': sessionId,
        'winners': winners,
      }));
      print('Comando BINGO enviado para a sessão $sessionId!');
    }
  }

  Future<void> finishCurrentSession() async {
      if (sessionId == null) return;
      print("finish CurrentSession  $sessionId.");
      try {
        await http.post(
          Uri.parse('${AppConstants.API_URL}/finish_session'),
          headers: {'Content-Type': 'application/json'},
          body: json.encode({'sessionId': sessionId}),
        );
      } catch (e, stackTrace) {
        debugPrint('Erro: $e');
        debugPrint('StackTrace: $stackTrace');

        if (e is http.ClientException) {
          debugPrint('ClientException: ${e.message}');
          debugPrint('URI: ${e.uri}');

        }
      }
    }

    void reloadSession(Map<String, dynamic> sessionData) {
      reset(); // Limpa qualquer estado anterior
      
      sessionId = sessionData['_id'];
      shortSessionId = sessionData['shortId']; 
      sessionName = sessionData['sessionName'];
      drawnNumbers = List<int>.from(sessionData['drawnNumbers']);

      // Recalcula os números disponíveis
      availableNumbers = List.generate(75, (i) => i + 1);
      for (var number in drawnNumbers) {
        availableNumbers.remove(number);
      }
      
      // Reconecta ao WebSocket
      _connectWebSocket();
      notifyListeners();
    }

  @override
  void dispose() {
    _channel?.sink.close();
    super.dispose();
  }
}