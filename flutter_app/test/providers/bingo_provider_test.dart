// test/providers/bingo_provider_test.dart
import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:bingou/providers/bingo_provider.dart';

void main() {
  // ---------------------------------------------------------------------------
  // Estado inicial
  // ---------------------------------------------------------------------------
  group('BingoProvider – estado inicial', () {
    late BingoProvider provider;

    setUp(() => provider = BingoProvider());

    test('sessionId é null', () => expect(provider.sessionId, isNull));
    test('shortSessionId é null', () => expect(provider.shortSessionId, isNull));
    test('drawnNumbers está vazio', () => expect(provider.drawnNumbers, isEmpty));
    test('availableNumbers contém 75 números', () => expect(provider.availableNumbers.length, 75));
    test('isLoading é false', () => expect(provider.isLoading, isFalse));
    test('errorMessage é null', () => expect(provider.errorMessage, isNull));
    test('status é disconnected',
        () => expect(provider.connectionStatus, WebSocketStatus.disconnected));
  });

  // ---------------------------------------------------------------------------
  // drawNumber
  // ---------------------------------------------------------------------------
  group('BingoProvider – drawNumber', () {
    late BingoProvider provider;

    setUp(() => provider = BingoProvider());

    test('retorna true para número válido', () {
      expect(provider.drawNumber(1), isTrue);
    });

    test('remove número de availableNumbers', () {
      provider.drawNumber(10);
      expect(provider.availableNumbers, isNot(contains(10)));
    });

    test('adiciona número a drawnNumbers', () {
      provider.drawNumber(10);
      expect(provider.drawnNumbers, contains(10));
    });

    test('retorna false para número já sorteado', () {
      provider.drawNumber(5);
      expect(provider.drawNumber(5), isFalse);
    });

    test('retorna false para número fora do range (0)', () {
      expect(provider.drawNumber(0), isFalse);
    });

    test('retorna false para número fora do range (76)', () {
      expect(provider.drawNumber(76), isFalse);
    });

    test('sortear todos os 75 números esvazia availableNumbers', () {
      for (var i = 1; i <= 75; i++) {
        provider.drawNumber(i);
      }
      expect(provider.availableNumbers, isEmpty);
      expect(provider.drawnNumbers.length, 75);
    });
  });

  // ---------------------------------------------------------------------------
  // drawRandomNumber
  // ---------------------------------------------------------------------------
  group('BingoProvider – drawRandomNumber', () {
    late BingoProvider provider;

    setUp(() => provider = BingoProvider());

    test('retorna true quando há números disponíveis', () {
      expect(provider.drawRandomNumber(), isTrue);
    });

    test('reduz availableNumbers em 1', () {
      provider.drawRandomNumber();
      expect(provider.availableNumbers.length, 74);
    });

    test('retorna false quando não há números disponíveis', () {
      for (var i = 1; i <= 75; i++) {
        provider.drawNumber(i);
      }
      expect(provider.drawRandomNumber(), isFalse);
    });

    test('número sorteado está na lista de sorteados', () {
      provider.drawRandomNumber();
      final drawn = provider.drawnNumbers;
      expect(drawn, hasLength(1));
      expect(provider.availableNumbers, isNot(contains(drawn.first)));
    });
  });

  // ---------------------------------------------------------------------------
  // reset
  // ---------------------------------------------------------------------------
  group('BingoProvider – reset', () {
    late BingoProvider provider;

    setUp(() {
      provider = BingoProvider();
      provider.drawNumber(1);
      provider.drawNumber(2);
    });

    test('limpa drawnNumbers', () {
      provider.reset();
      expect(provider.drawnNumbers, isEmpty);
    });

    test('restaura 75 números disponíveis', () {
      provider.reset();
      expect(provider.availableNumbers.length, 75);
    });

    test('limpa sessionId', () {
      provider.reset();
      expect(provider.sessionId, isNull);
    });

    test('limpa shortSessionId', () {
      provider.reset();
      expect(provider.shortSessionId, isNull);
    });

    test('volta isLoading para false', () {
      provider.reset();
      expect(provider.isLoading, isFalse);
    });
  });

  // ---------------------------------------------------------------------------
  // reloadSession
  // ---------------------------------------------------------------------------
  group('BingoProvider – reloadSession', () {
    late BingoProvider provider;

    setUp(() => provider = BingoProvider());

    test('restaura sessionId do documento', () {
      provider.reloadSession(_fakeSessionDoc(drawn: [1, 2, 3]));
      expect(provider.sessionId, '507f1f77bcf86cd799439011');
    });

    test('restaura shortSessionId', () {
      provider.reloadSession(_fakeSessionDoc());
      expect(provider.shortSessionId, 'TST01');
    });

    test('repopula drawnNumbers do documento', () {
      provider.reloadSession(_fakeSessionDoc(drawn: [5, 10, 75]));
      expect(provider.drawnNumbers, containsAll([5, 10, 75]));
    });

    test('numeros já sorteados não estão em availableNumbers', () {
      provider.reloadSession(_fakeSessionDoc(drawn: [5, 10]));
      expect(provider.availableNumbers, isNot(contains(5)));
      expect(provider.availableNumbers, isNot(contains(10)));
    });
  });

  // ---------------------------------------------------------------------------
  // createSession (HTTP mockado)
  // ---------------------------------------------------------------------------
  group('BingoProvider – createSession com HTTP mock', () {
    test('sucesso: seta sessionId e shortSessionId', () async {
      final mockClient = MockClient((_) async => http.Response(
            json.encode({
              'success': true,
              'sessionId': 'abc123',
              'shortSessionId': 'TST01',
            }),
            200,
          ));

      final provider = BingoProvider.withHttpClient(mockClient);
      final ok = await provider.createSession(
          'Bingo Test', '1', 'Prêmio', '507f1f77bcf86cd799439011');

      expect(ok, isTrue);
      expect(provider.sessionId, 'abc123');
      expect(provider.shortSessionId, 'TST01');
    });

    test('falha: seta errorMessage e retorna false', () async {
      final mockClient = MockClient((_) async =>
          http.Response(json.encode({'error': 'Usuário não autenticado.'}), 400));

      final provider = BingoProvider.withHttpClient(mockClient);
      final ok = await provider.createSession('X', '1', 'Y', '');

      expect(ok, isFalse);
    });
  });

  // ---------------------------------------------------------------------------
  // linkToDisplay (HTTP mockado)
  // ---------------------------------------------------------------------------
  group('BingoProvider – linkToDisplay com HTTP mock', () {
    test('retorna true para resposta 200', () async {
      final mockClient = MockClient(
          (_) async => http.Response(json.encode({'success': true}), 200));

      final provider = BingoProvider.withHttpClient(mockClient);
      // shortSessionId precisa estar definido para a chamada
      provider.shortSessionId = 'TST01';

      final ok = await provider.linkToDisplay('AB1234');
      expect(ok, isTrue);
    });

    test('retorna false para shortSessionId null', () async {
      final mockClient = MockClient((_) async => http.Response('', 200));
      final provider = BingoProvider.withHttpClient(mockClient);
      // shortSessionId = null (padrão)
      final ok = await provider.linkToDisplay('AB1234');
      expect(ok, isFalse);
    });

    test('retorna false para código inválido (404)', () async {
      final mockClient = MockClient((_) async =>
          http.Response(json.encode({'error': 'Código não encontrado.'}), 404));

      final provider = BingoProvider.withHttpClient(mockClient);
      provider.shortSessionId = 'TST01';

      final ok = await provider.linkToDisplay('XXXXXX');
      expect(ok, isFalse);
    });
  });
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

Map<String, dynamic> _fakeSessionDoc({List<int> drawn = const []}) => {
      '_id': '507f1f77bcf86cd799439011',
      'shortId': 'TST01',
      'sessionName': 'Bingo Test',
      'round': '1',
      'prize': 'Prêmio',
      'userId': '607f1f77bcf86cd799439022',
      'drawnNumbers': drawn,
      'winners': <String>[],
      'status': 'active',
    };
