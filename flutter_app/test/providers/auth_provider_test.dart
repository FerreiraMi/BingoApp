// test/providers/auth_provider_test.dart
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:bingou/providers/auth_provider.dart';

void main() {
  setUp(() {
    // Garante que SharedPreferences não persiste entre testes
    SharedPreferences.setMockInitialValues({});
  });

  // ---------------------------------------------------------------------------
  // Estado inicial
  // ---------------------------------------------------------------------------
  group('AuthProvider – estado inicial', () {
    late AuthProvider provider;
    setUp(() => provider = AuthProvider.withHttpClient(_mockClient({})));

    test('não está logado', () => expect(provider.isLoggedIn, isFalse));
    test('userId é null', () => expect(provider.userId, isNull));
    test('userEmail é null', () => expect(provider.userEmail, isNull));
    test('isLoading é false', () => expect(provider.isLoading, isFalse));
    test('histórico vazio', () => expect(provider.history, isEmpty));
  });

  // ---------------------------------------------------------------------------
  // tryAutoLogin
  // ---------------------------------------------------------------------------
  group('AuthProvider – tryAutoLogin', () {
    test('não loga quando não há dados salvos', () async {
      SharedPreferences.setMockInitialValues({});
      final provider = AuthProvider.withHttpClient(_mockClient({}));
      await provider.tryAutoLogin();
      expect(provider.isLoggedIn, isFalse);
    });

    test('loga automaticamente quando há userId salvo', () async {
      SharedPreferences.setMockInitialValues({
        'userId': 'saved_user_id',
        'userEmail': 'user@test.com',
      });
      final provider = AuthProvider.withHttpClient(_mockClient({}));
      await provider.tryAutoLogin();
      expect(provider.isLoggedIn, isTrue);
      expect(provider.userId, 'saved_user_id');
    });
  });

  // ---------------------------------------------------------------------------
  // login
  // ---------------------------------------------------------------------------
  group('AuthProvider – login', () {
    test('sucesso: seta userId e retorna true', () async {
      final provider = AuthProvider.withHttpClient(_mockClient(
        {'userId': 'uid_123', 'email': 'user@test.com'},
        statusCode: 200,
      ));

      final ok = await provider.login('user@test.com', 'senha123',
          rememberMe: false);

      expect(ok, isTrue);
      expect(provider.isLoggedIn, isTrue);
      expect(provider.userId, 'uid_123');
      expect(provider.errorMessage, isNull);
    });

    test('falha: seta errorMessage e retorna false', () async {
      final provider = AuthProvider.withHttpClient(_mockClient(
        {'error': 'Email ou senha inválidos.'},
        statusCode: 401,
      ));

      final ok =
          await provider.login('wrong@test.com', 'errado', rememberMe: false);

      expect(ok, isFalse);
      expect(provider.isLoggedIn, isFalse);
      expect(provider.errorMessage, isNotNull);
    });

    test('rememberMe salva dados no SharedPreferences', () async {
      final provider = AuthProvider.withHttpClient(_mockClient(
        {'userId': 'uid_rem', 'email': 'rem@test.com'},
        statusCode: 200,
      ));

      await provider.login('rem@test.com', 'senha123', rememberMe: true);

      final prefs = await SharedPreferences.getInstance();
      expect(prefs.getString('userId'), 'uid_rem');
    });
  });

  // ---------------------------------------------------------------------------
  // register
  // ---------------------------------------------------------------------------
  group('AuthProvider – register', () {
    test('sucesso retorna true', () async {
      final provider = AuthProvider.withHttpClient(_mockClient(
        {'success': true},
        statusCode: 200,
      ));

      final ok = await provider.register('new@test.com', 'senha123');
      expect(ok, isTrue);
    });

    test('email duplicado retorna false com errorMessage', () async {
      final provider = AuthProvider.withHttpClient(_mockClient(
        {'error': 'Este email já está cadastrado.'},
        statusCode: 409,
      ));

      final ok = await provider.register('dup@test.com', 'senha123');

      expect(ok, isFalse);
      expect(provider.errorMessage, isNotNull);
    });
  });

  // ---------------------------------------------------------------------------
  // fetchHistory
  // ---------------------------------------------------------------------------
  group('AuthProvider – fetchHistory', () {
    test('retorna lista vazia quando não está logado', () async {
      final provider = AuthProvider.withHttpClient(_mockClient({}));
      await provider.fetchHistory();
      expect(provider.history, isEmpty);
    });

    test('popula history com lista retornada pela API', () async {
      final sessions = [
        {'_id': 's1', 'sessionName': 'Bingo 1'},
        {'_id': 's2', 'sessionName': 'Bingo 2'},
      ];

      // Faz login para setar _userId
      final provider = AuthProvider.withHttpClient(MockClient((req) async {
        if (req.url.path.contains('login')) {
          return http.Response(
              json.encode({'userId': 'uid_h', 'email': 'h@test.com'}), 200);
        }
        return http.Response(json.encode(sessions), 200);
      }));

      await provider.login('h@test.com', 'senha123', rememberMe: false);
      await provider.fetchHistory();

      expect(provider.history, hasLength(2));
    });
  });

  // ---------------------------------------------------------------------------
  // logout
  // ---------------------------------------------------------------------------
  group('AuthProvider – logout', () {
    test('limpa userId e histórico', () async {
      SharedPreferences.setMockInitialValues({
        'userId': 'uid_x',
        'userEmail': 'x@test.com',
      });
      final provider = AuthProvider.withHttpClient(_mockClient({}));
      await provider.tryAutoLogin();

      await provider.logout();

      expect(provider.isLoggedIn, isFalse);
      expect(provider.userId, isNull);
      expect(provider.history, isEmpty);
    });

    test('limpa SharedPreferences após logout', () async {
      SharedPreferences.setMockInitialValues({'userId': 'abc'});
      final provider = AuthProvider.withHttpClient(_mockClient({}));
      await provider.logout();

      final prefs = await SharedPreferences.getInstance();
      expect(prefs.getString('userId'), isNull);
    });
  });
}

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

http.Client _mockClient(Map<String, dynamic> body, {int statusCode = 200}) {
  return MockClient((_) async => http.Response(json.encode(body), statusCode));
}
