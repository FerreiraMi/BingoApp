// bingou/lib/main.dart
import 'package:flutter/material.dart';
import 'package:bingou/providers/auth_provider.dart';
import 'package:bingou/providers/bingo_provider.dart';
import 'package:bingou/screens/history_screen.dart';
import 'package:bingou/screens/login_screen.dart';
import 'package:provider/provider.dart';
import 'package:bingou/screens/main_screen.dart';
import 'package:bingou/screens/splash_screen.dart';


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
          primarySwatch: Colors.amber,
          scaffoldBackgroundColor: const Color(0xFFF5CC6F),
          textButtonTheme: TextButtonThemeData(
            style: TextButton.styleFrom(
              foregroundColor: Color(0xFF902A05), // cor dos textos tipo link
            ),
          ),
          inputDecorationTheme: const InputDecorationTheme(
            labelStyle: TextStyle(color: Color(0xFF902A05)),       // cor da label
            enabledBorder: OutlineInputBorder(
              borderSide: BorderSide(color: Color(0xFF902A05)),    // borda normal
            ),
            focusedBorder: OutlineInputBorder(
              borderSide: BorderSide(color: Color(0xFF902A05), width: 2), // borda ao focar
            ),
            hintStyle: TextStyle(color: Color(0xFF902A05)),         // dica (placeholder)
          ),
          elevatedButtonTheme: ElevatedButtonThemeData(
            style: ElevatedButton.styleFrom(
              foregroundColor: Colors.white,          // cor do texto
              backgroundColor: Color(0xFF902A05),     // cor de fundo do botão
            ),
          ),
          outlinedButtonTheme: OutlinedButtonThemeData(
            style: OutlinedButton.styleFrom(
              foregroundColor: Color(0xFF902A05),     // texto e borda
            ),
          ),
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