// bingou/lib/providers/auth_provider.dart
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:bingou/utils/constants.dart';

class AuthProvider with ChangeNotifier {
  String? _userId;
  String? _userEmail;
  bool isLoading = false;
  String? errorMessage;
  List<dynamic> history = [];

  final http.Client _httpClient;

  AuthProvider() : _httpClient = http.Client();

  /// Construtor para testes: injeta cliente HTTP mockado.
  AuthProvider.withHttpClient(this._httpClient);

  bool get isLoggedIn => _userId != null;
  String? get userId => _userId;
  String? get userEmail => _userEmail;

  Future<void> tryAutoLogin() async {
    final prefs = await SharedPreferences.getInstance();
    if (!prefs.containsKey('userId')) {
      return;
    }
    _userId = prefs.getString('userId');
    _userEmail = prefs.getString('userEmail');
    notifyListeners();
  }

  Future<bool> _authenticate(String email, String password, String endpoint, {bool rememberMe = false}) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      final response = await _httpClient.post(
        Uri.parse('${AppConstants.API_URL}/$endpoint'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'email': email, 'password': password}),
      );

      final responseData = json.decode(response.body);
      
      if (response.statusCode >= 400) {
        errorMessage = responseData['error'] ?? 'Ocorreu um erro.';
        isLoading = false;
        notifyListeners();
        return false;
      }
      
      // Se for login, guarde o ID
      if (endpoint == 'login') {
        _userId = responseData['userId'];
        _userEmail = responseData['email'];
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('userId', _userId!);
        await prefs.setString('userEmail', _userEmail!);
      }

      // Lógica para salvar os dados se "Lembrar-me" estiver ativo
      if (rememberMe) { // <-- MUDANÇA AQUI
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('userId', _userId!);
        await prefs.setString('userEmail', _userEmail!);
      }

      isLoading = false;
      notifyListeners();
      return true;

    } catch (e) {
      errorMessage = "Erro de conexão. Verifique sua rede e o IP configurado.";
      isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> login(String email, String password,  {required bool rememberMe}) async {
    return _authenticate(email, password, 'login');
  }

  Future<bool> register(String email, String password) async {
    return _authenticate(email, password, 'register');
  }

  Future<void> fetchHistory() async {
    if (_userId == null) {
       history = [];
       return;
    }
    
    //isLoading = true;
    //notifyListeners();

    try {
      final response = await _httpClient.get(
        Uri.parse('${AppConstants.API_URL}/history?userId=$_userId'),
      );
      if (response.statusCode == 200) {
        history = json.decode(response.body);
      } else {
        errorMessage = "Não foi possível carregar o histórico.";
      }
    } catch (e) {
      errorMessage = "Erro de conexão ao buscar histórico.";
    }
    
    //isLoading = false;
    notifyListeners();
  }
  
  Future<void> logout() async {
    _userId = null;
    _userEmail = null;
    history = [];
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    notifyListeners();
  }
}