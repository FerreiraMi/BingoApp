import 'dart:async';
import 'package:flutter/material.dart';
import 'package:bingou/main.dart'; // Importamos o AuthWrapper

class SplashScreen extends StatefulWidget {
  @override
  _SplashScreenState createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    // Navega para a próxima tela após 3 segundos
    Timer(
      Duration(seconds: 3),
      () => Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (context) => AuthWrapper()),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Color.fromRGBO(245, 204, 111, 1.0), // Cor de fundo amarela
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Substitua Icon por sua logo: Image.asset('assets/logo.png')
            Image.asset('assets/logo.png'),
            //Icon(Icons.casino, size: 100, color: Colors.white),
          
          ],
        ),
      ),
    );
  }
}