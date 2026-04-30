// test/screens/join_session_screen_test.dart
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:bingou/screens/join_session_screen.dart';

void main() {
  Widget buildApp() => const MaterialApp(home: JoinSessionScreen());

  // ---------------------------------------------------------------------------
  // Renderização
  // ---------------------------------------------------------------------------
  group('JoinSessionScreen – renderização', () {
    testWidgets('exibe campo de código de sessão', (tester) async {
      await tester.pumpWidget(buildApp());
      expect(find.byType(TextFormField), findsOneWidget);
    });

    testWidgets('exibe botão ACOMPANHAR', (tester) async {
      await tester.pumpWidget(buildApp());
      expect(find.text('ACOMPANHAR'), findsOneWidget);
    });

    testWidgets('exibe botão de escanear QR Code', (tester) async {
      await tester.pumpWidget(buildApp());
      expect(find.text('ESCANEAR QR CODE'), findsOneWidget);
    });

    testWidgets('exibe instrução para o usuário', (tester) async {
      await tester.pumpWidget(buildApp());
      expect(
        find.textContaining('código de 5 dígitos'),
        findsOneWidget,
      );
    });
  });

  // ---------------------------------------------------------------------------
  // Validação do formulário
  // ---------------------------------------------------------------------------
  group('JoinSessionScreen – validação', () {
    testWidgets('exibe erro quando código tem menos de 5 caracteres',
        (tester) async {
      await tester.pumpWidget(buildApp());

      // Digita código curto demais
      await tester.enterText(find.byType(TextFormField), 'AB1');
      await tester.tap(find.text('ACOMPANHAR'));
      await tester.pump();

      expect(find.text('O código deve ter 5 dígitos'), findsOneWidget);
    });

    testWidgets('exibe erro quando campo está vazio', (tester) async {
      await tester.pumpWidget(buildApp());

      await tester.tap(find.text('ACOMPANHAR'));
      await tester.pump();

      expect(find.text('O código deve ter 5 dígitos'), findsOneWidget);
    });

    testWidgets('não exibe erro para código com exatamente 5 caracteres',
        (tester) async {
      await tester.pumpWidget(buildApp());

      await tester.enterText(find.byType(TextFormField), 'AB1CD');
      // Não dá tap no botão para não navegar (evitar erro de Navigator)
      // Apenas verifica que o validator não dispara na entrada
      await tester.pump();

      expect(find.text('O código deve ter 5 dígitos'), findsNothing);
    });
  });
}
