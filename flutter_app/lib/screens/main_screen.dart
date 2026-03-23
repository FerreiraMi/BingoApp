import 'package:flutter/material.dart';
import 'package:bingou/screens/create_session_screen.dart';
import 'package:bingou/screens/history_screen.dart';
import 'package:bingou/screens/join_session_screen.dart'; // Importe a tela de "entrar na sessão"
import 'package:bingou/screens/profile_screen.dart';
// Importe para a lógica do provider
// Importe o bingo provider
// Importe a tela de controle

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  _MainScreenState createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _selectedIndex = 0;

  // Lista de telas para cada aba
  static final List<Widget> _widgetOptions = <Widget>[
    const HistoryScreen(),
    const CreateSessionScreen(),
    const JoinSessionScreen(), // Adicionamos a nova tela aqui
    const ProfileScreen(),
  ];

  void _onItemTapped(int index) {
    // A lógica de navegação para "Nova Sessão" era complexa, vamos simplificar.
    // A própria aba agora pode lidar com a navegação.
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack( // IndexedStack preserva o estado das abas
        index: _selectedIndex,
        children: _widgetOptions,
      ),
      bottomNavigationBar: BottomNavigationBar(
        // Adicionamos type: BottomNavigationBarType.fixed para garantir que todas as abas apareçam
        type: BottomNavigationBarType.fixed,
        items: const <BottomNavigationBarItem>[
          BottomNavigationBarItem(
            icon: Icon(Icons.history),
            label: 'Histórico',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.add_circle_outline),
            label: 'Nova Sessão',
          ),
          // ===============================================
          // NOVA ABA "ACOMPANHAR" ADICIONADA
          // ===============================================
          BottomNavigationBarItem(
            icon: Icon(Icons.visibility),
            label: 'Acompanhar',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person),
            label: 'Perfil',
          ),
        ],
        currentIndex: _selectedIndex,
        selectedItemColor: const Color(0xFF902A05),
        onTap: _onItemTapped,
      ),
    );
  }
}