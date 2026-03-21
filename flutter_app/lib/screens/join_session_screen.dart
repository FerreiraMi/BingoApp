import 'package:bingou/screens/qr_scanner_screen.dart';
import 'package:bingou/screens/viewer_screen.dart';
import 'package:flutter/material.dart';


class JoinSessionScreen extends StatefulWidget {
  const JoinSessionScreen({super.key});

  @override
  _JoinSessionScreenState createState() => _JoinSessionScreenState();
}

class _JoinSessionScreenState extends State<JoinSessionScreen> {
  final _shortIdController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  void _joinSession() {
    if (_formKey.currentState!.validate()) {
      final shortId = _shortIdController.text.trim();
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => ViewerScreen(shortId: shortId)),
      );
    }
  }

  void _scanQrCode() async {
    final shortId = await Navigator.of(context).push<String>(
      MaterialPageRoute(builder: (_) => const QRScannerScreen()),
    );

    if (shortId != null && mounted) {
      // O QR Code pode conter a URL completa, então extraímos apenas o shortId
      final uri = Uri.parse(shortId);
      final id = uri.pathSegments.last;
      
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => ViewerScreen(shortId: id)),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Acompanhar Sessão')),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('Digite o código de 5 dígitos da sessão ou escaneie o QR Code para acompanhar em tempo real.', textAlign: TextAlign.center),
              const SizedBox(height: 24),
              Form(
                key: _formKey,
                child: TextFormField(
                  controller: _shortIdController,
                  decoration: const InputDecoration(
                    labelText: 'Código da Sessão',
                    border: OutlineInputBorder(),
                  ),
                  maxLength: 5,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 22, letterSpacing: 8),
                  validator: (value) => (value?.length ?? 0) != 5 ? 'O código deve ter 5 dígitos' : null,
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _joinSession,
                style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
                child: const Text('ACOMPANHAR'),
              ),
              const SizedBox(height: 24),
              OutlinedButton.icon(
                onPressed: _scanQrCode,
                icon: const Icon(Icons.qr_code_scanner),
                label: const Text('ESCANEAR QR CODE'),
                style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}