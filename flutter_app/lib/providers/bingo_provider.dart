// bingou/lib/providers/bingo_provider.dart
import 'dart:async'; // <-- MUDANÇA: Adicionado para o Timer
import 'dart:convert';
import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:web_socket_channel/web_socket_channel.dart';
import 'package:bingou/utils/constants.dart';

// <-- MUDANÇA: Enum para gerenciar o estado da conexão
enum WebSocketStatus { disconnected, connecting, connected, reconnecting }

class BingoProvider with ChangeNotifier {
  String? sessionId;
  String? shortSessionId;
  String? _currentUserId;

  String? sessionName;
  List<int> drawnNumbers = [];
  List<int> availableNumbers = List.generate(75, (i) => i + 1);
  bool _isProUser = false;
  bool isLoading = false;
  String? errorMessage;

  // Configurações da sessão
  bool showOnlinePlayers = false;
  bool showProbability = true;
  List<String> probabilityModes = ['horizontal'];

  // Jogadores online em tempo real (atualizados via WebSocket)
  List<String> onlineViewers = [];

  // Estado de bingo chamado
  bool bingoCalled = false;
  List<String> bingoWinners = [];

  // Cliente HTTP injetável (facilita testes unitários)
  final http.Client _httpClient;

  BingoProvider() : _httpClient = http.Client();

  /// Construtor alternativo para testes: injeta um cliente HTTP mockado.
  BingoProvider.withHttpClient(this._httpClient);

  // Variáveis para gerenciar o estado e a reconexão do WebSocket
  WebSocketChannel? _channel;
  WebSocketStatus _connectionStatus = WebSocketStatus.disconnected;
  Timer? _reconnectionTimer;
  int _reconnectAttempts = 0;

  // Getter público para a UI reagir ao status
  WebSocketStatus get connectionStatus => _connectionStatus;

  void setIsProUser(bool isPro) {
    _isProUser = isPro;
    notifyListeners();
  }

  Future<bool> createSession(String name, String round, String prize, String userId, {String? oldSessionId}) async {
    isLoading = true;
    errorMessage = null;
    _currentUserId = userId; // Armazena o ID do usuário atual
    notifyListeners();

    final url = Uri.parse('${AppConstants.API_URL}/session');
    try {
      final response = await _httpClient.post(
        url,
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'userId': userId,
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
        
        // <-- MUDANÇA: Chamamos a nova função de conexão robusta
        _initiateWebSocketConnection();

        if (oldSessionId != null) {
          final newUrl = '${AppConstants.WEB_PAGE_URL}/$shortSessionId'; 
          Future.delayed(const Duration(milliseconds: 500), () {
             _sendWebSocketMessage({
                'type': 'redirect',
                'targetSessionId': oldSessionId,
                'newUrl': newUrl,
             });
          });
          print("Comando de redirect agendado.");
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

  void clearErrorMessage() {
    errorMessage = null;
    // Não notificamos listeners aqui, pois é uma limpeza interna
  }

  // <-- MUDANÇA: A antiga _connectWebSocket foi substituída por esta lógica mais complexa
  void _initiateWebSocketConnection() {
    if (sessionId == null) return;

    _reconnectionTimer?.cancel();
    _channel?.sink.close();
    _updateStatus(WebSocketStatus.connecting);

    try {
      _channel = WebSocketChannel.connect(
        Uri.parse(AppConstants.WEBSOCKET_URL),
      );

      // Autentica o operador assim que a conexão estiver pronta
      _channel!.ready.then((_) {
        print("WebSocket pronto. Autenticando operador...");
        _sendWebSocketMessage({
          'type': 'authenticate_operator',
          'sessionId': sessionId,
          'userId': _currentUserId,
        });
        // Envia as configurações iniciais após autenticação
        Future.delayed(const Duration(milliseconds: 300), _sendSettings);
      });

      // Ouve por eventos do canal
      _channel!.stream.listen(
        (message) {
          try {
            final data = json.decode(message as String) as Map<String, dynamic>;
            if (data['type'] == 'viewer_list_update') {
              onlineViewers = List<String>.from(data['viewers'] ?? []);
              notifyListeners();
            } else if (data['type'] == 'bingo_called') {
              bingoCalled = true;
              bingoWinners = List<String>.from(data['winners'] ?? []);
              notifyListeners();
            }
          } catch (_) {}
        },
        onDone: () {
          print("WebSocket: Conexão encerrada. Iniciando reconexão.");
          _handleDisconnection();
        },
        onError: (error) {
          print("WebSocket: Erro - $error. Iniciando reconexão.");
          _handleDisconnection();
        },
        cancelOnError: true,
      );

      _updateStatus(WebSocketStatus.connected);
      _reconnectAttempts = 0;
      print("WebSocket: Conectado com sucesso à sessão $sessionId");
    } catch (e) {
      print("WebSocket: Falha ao tentar conectar - $e");
      _handleDisconnection();
    }
  }

  // <-- MUDANÇA: Nova função para lidar com a desconexão
  void _handleDisconnection() {
    if (_connectionStatus == WebSocketStatus.reconnecting || _connectionStatus == WebSocketStatus.disconnected) {
      return;
    }
    _updateStatus(WebSocketStatus.reconnecting);
    _reconnectAttempts++;
    final delayInSeconds = min(pow(2, _reconnectAttempts), 30).toInt();
    
    print("WebSocket: Agendando reconexão em $delayInSeconds segundos (tentativa #$_reconnectAttempts)...");
    _reconnectionTimer = Timer(Duration(seconds: delayInSeconds), _initiateWebSocketConnection);
  }

  // <-- MUDANÇA: Nova função para centralizar a atualização de status
  void _updateStatus(WebSocketStatus status) {
    _connectionStatus = status;
    notifyListeners();
  }

  // <-- MUDANÇA: Nova função para centralizar o envio de mensagens
  bool _sendWebSocketMessage(Map<String, dynamic> message) {
    if (_connectionStatus == WebSocketStatus.connected && _channel != null) {
      _channel!.sink.add(json.encode(message));
      return true;
    } else {
      print("Aviso: Não foi possível enviar a mensagem. WebSocket não está conectado. Status: $_connectionStatus");
      errorMessage = "Não foi possível sortear. Verifique a conexão com a internet."; // Define uma mensagem de erro
      notifyListeners(); // Notifica a UI sobre o erro
      return false; // F      return false;
    }
  }

  bool drawNumber(int number) {
    if (availableNumbers.contains(number)) {
      availableNumbers.remove(number);
      drawnNumbers.add(number);
      errorMessage = null;
      notifyListeners();
      _sendWebSocketMessage({'sessionId': sessionId, 'number': number});
      return true;
    }
    return false;
  }

  bool drawRandomNumber() {
    if (availableNumbers.isNotEmpty) {
      final random = Random();
      final index = random.nextInt(availableNumbers.length);
      return drawNumber(availableNumbers[index]);
    }
    return false; // Retorna false se não houver números disponíveis
  }

  void callBingo({required List<String> winners}) {
    // Atualiza o estado local imediatamente (otimista)
    bingoCalled = true;
    bingoWinners = List.from(winners);
    notifyListeners();
    _sendWebSocketMessage({
      'type': 'bingo_called',
      'sessionId': sessionId,
      'winners': winners,
    });
  }

  void clearBingo() {
    bingoCalled = false;
    bingoWinners = [];
    notifyListeners();
  }

  /// Atualiza as configurações da sessão e as envia ao display via WebSocket.
  void updateSettings({
    bool? showOnlinePlayers,
    bool? showProbability,
    List<String>? probabilityModes,
  }) {
    if (showOnlinePlayers != null) this.showOnlinePlayers = showOnlinePlayers;
    if (showProbability != null) this.showProbability = showProbability;
    if (probabilityModes != null) this.probabilityModes = probabilityModes;
    notifyListeners();
    _sendSettings();
  }

  void _sendSettings() {
    _sendWebSocketMessage({
      'type': 'session_settings',
      'sessionId': sessionId,
      'showViewers': showOnlinePlayers,
      'showProbability': showProbability,
      'probabilityModes': probabilityModes,
    });
  }

  /// Vincula a sessão ativa a um display token gerado pelo navegador.
  /// Retorna true se o vínculo foi realizado com sucesso.
  Future<bool> linkToDisplay(String displayCode) async {
    if (shortSessionId == null) return false;
    final url = Uri.parse('${AppConstants.API_URL}/display_token');
    try {
      final response = await _httpClient.put(
        url,
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'code': displayCode.trim().toUpperCase(),
          'sessionShortId': shortSessionId,
        }),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // <-- MUDANÇA: reset agora limpa também os recursos de reconexão
  void reset() {
    _reconnectionTimer?.cancel();
    _channel?.sink.close();
    _updateStatus(WebSocketStatus.disconnected);
    _reconnectAttempts = 0;
    
    sessionId = null;
    sessionName = null;
    shortSessionId = null; 
    _currentUserId = null;
    drawnNumbers = [];
    availableNumbers = List.generate(75, (i) => i + 1);
    isLoading = false;
    errorMessage = null;
    onlineViewers = [];
    bingoCalled = false;
    bingoWinners = [];
  }

  void reloadSession(Map<String, dynamic> sessionData) {
    reset();
    sessionId = sessionData['_id'];
    shortSessionId = sessionData['shortId']; 
    sessionName = sessionData['sessionName'];
    drawnNumbers = List<int>.from(sessionData['drawnNumbers']);
    _currentUserId = sessionData['userId']; // <-- MUDANÇA: Salva o userId
    availableNumbers = List.generate(75, (i) => i + 1)..removeWhere((n) => drawnNumbers.contains(n));
    
    _initiateWebSocketConnection(); // Usa a nova função de conexão
    notifyListeners();
  }

  Future<void> finishCurrentSession() async {
    if (sessionId == null) return;
    print("finish CurrentSession $sessionId.");
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

  @override
  void dispose() {
    reset(); // O reset já faz toda a limpeza necessária
    super.dispose();
  }
}