// flutter_app/lib/main.dart
import 'package:flutter/material.dart';
import 'package:flutter_app/providers/auth_provider.dart';
import 'package:flutter_app/providers/bingo_provider.dart';
import 'package:flutter_app/screens/history_screen.dart';
import 'package:flutter_app/screens/login_screen.dart';
import 'package:provider/provider.dart';
import 'package:flutter_app/screens/main_screen.dart';
import 'package:flutter_app/screens/splash_screen.dart';


void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => BingoProvider()),
      ],
      child: MaterialApp(
        title: 'Bingo App',
        theme: ThemeData(
          primarySwatch: Colors.indigo,
          visualDensity: VisualDensity.adaptivePlatformDensity,
        ),
        home: SplashScreen(),
      ),
    );
  }
}

class AuthWrapper extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    // Usamos um FutureBuilder para tentar o autologin na inicialização
    return FutureBuilder(
      future: Provider.of<AuthProvider>(context, listen: false).tryAutoLogin(),
      builder: (ctx, authResultSnapshot) {
        // Mostra um spinner enquanto o autologin está sendo verificado
        if (authResultSnapshot.connectionState == ConnectionState.waiting) {
          return Scaffold(body: Center(child: CircularProgressIndicator()));
        }
        
        // Após a verificação, o Consumer decide a tela com base no estado de login
        return Consumer<AuthProvider>(
          // Se logado, vai para MainScreen, senão, para LoginScreen
          builder: (ctx, auth, _) => auth.isLoggedIn ? MainScreen() : LoginScreen(), // <-- MUDANÇA AQUI
        );
      },
    );
  }
}