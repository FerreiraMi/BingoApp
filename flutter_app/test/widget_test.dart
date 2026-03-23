// test/widget_test.dart
// Smoke test: verifica que o app inicia e renderiza a SplashScreen
// sem erros de frame ou exceções não tratadas.

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:bingou/main.dart';

void main() {
  testWidgets('App renderiza sem erros na inicialização', (tester) async {
    // SharedPreferences não deve ter dados salvos (sem autologin)
    SharedPreferences.setMockInitialValues({});

    await tester.pumpWidget(const MyApp());

    // Aguarda o primeiro frame da SplashScreen
    await tester.pump();

    // A SplashScreen usa um CircularProgressIndicator ou logo enquanto
    // processa o autologin — o app não deve estar vazio
    expect(find.byType(MaterialApp), findsOneWidget);
  });
}

