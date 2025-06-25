import 'package:flutter/material.dart';
import 'package:flutter_app/providers/auth_provider.dart';
import 'package:flutter_app/providers/bingo_provider.dart';
import 'package:flutter_app/screens/bingo_control_screen.dart';
import 'package:provider/provider.dart';

class CreateSessionScreen extends StatefulWidget {
  final String? oldSessionId;

  CreateSessionScreen({this.oldSessionId});

  @override
  _CreateSessionScreenState createState() => _CreateSessionScreenState();
}

class _CreateSessionScreenState extends State<CreateSessionScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController(text: 'Bingo da Família');
  final _roundController = TextEditingController(text: '1');
  final _prizeController = TextEditingController(text: 'Cesta de Café');

  @override
  void dispose() {
    _nameController.dispose();
    _roundController.dispose();
    _prizeController.dispose();
    super.dispose();
  }

  void _createSession() async {
    if (!_formKey.currentState!.validate()) return;

    final bingoProvider = Provider.of<BingoProvider>(context, listen: false);
    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    final success = await bingoProvider.createSession(
      _nameController.text,
      _roundController.text,
      _prizeController.text,
      authProvider.userId!,
      oldSessionId: widget.oldSessionId,
    );
    if (success && mounted) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => BingoControlScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Criar Nova Sessão')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextFormField(
                controller: _nameController,
                decoration: InputDecoration(labelText: 'Nome do Bingo', border: OutlineInputBorder()),
                validator: (value) => value!.isEmpty ? 'Campo obrigatório' : null,
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _roundController,
                decoration: InputDecoration(labelText: 'Rodada', border: OutlineInputBorder()),
                keyboardType: TextInputType.number,
                validator: (value) => value!.isEmpty ? 'Campo obrigatório' : null,
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _prizeController,
                decoration: InputDecoration(labelText: 'Prêmio', border: OutlineInputBorder()),
                validator: (value) => value!.isEmpty ? 'Campo obrigatório' : null,
              ),
              SizedBox(height: 24),
              Consumer<BingoProvider>(
                builder: (ctx, provider, _) => ElevatedButton(
                  style: ElevatedButton.styleFrom(padding: EdgeInsets.symmetric(vertical: 16)),
                  onPressed: provider.isLoading ? null : _createSession,
                  child: provider.isLoading
                      ? CircularProgressIndicator(color: Colors.white)
                      : Text('CRIAR E INICIAR'),
                ),
              ),
              Consumer<BingoProvider>(
                builder: (ctx, provider, _) {
                  if (provider.errorMessage != null) {
                    return Padding(
                      padding: const EdgeInsets.only(top: 16.0),
                      child: Text(
                        provider.errorMessage!,
                        style: TextStyle(color: Colors.red),
                        textAlign: TextAlign.center,
                      ),
                    );
                  }
                  return SizedBox.shrink();
                },
              )
            ],
          ),
        ),
      ),
    );
  }
}