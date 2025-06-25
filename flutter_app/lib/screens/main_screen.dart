import 'package:flutter/material.dart';
import 'package:bingou/screens/create_session_screen.dart';
import 'package:bingou/screens/history_screen.dart';
import 'package:bingou/screens/profile_screen.dart';

class MainScreen extends StatefulWidget {
  @override
  _MainScreenState createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _selectedIndex = 0; // O índice da aba selecionada (começa em Home)

  // Lista das telas que serão exibidas
  static final List<Widget> _widgetOptions = <Widget>[
    HistoryScreen(),
    CreateSessionScreen(),
    ProfileScreen(),
  ];

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // O corpo do Scaffold é a tela selecionada da nossa lista
      body: _widgetOptions.elementAt(_selectedIndex),
      
      bottomNavigationBar: BottomNavigationBar(
        items: const <BottomNavigationBarItem>[
          BottomNavigationBarItem(
            icon: Icon(Icons.history),
            label: 'Histórico',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.add_circle, size: 36), // Ícone maior para destaque
            label: 'Nova Sessão',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person),
            label: 'Perfil',
          ),
        ],
        currentIndex: _selectedIndex,
        selectedItemColor: Colors.indigo,
        onTap: _onItemTapped,
      ),
    );
  }
}