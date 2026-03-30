import 'package:bingou/screens/join_session_screen.dart';
import 'package:flutter/material.dart';
import 'package:bingou/providers/auth_provider.dart';
import 'package:bingou/screens/main_screen.dart';
import 'package:bingou/screens/register_screen.dart';
import 'package:provider/provider.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  LoginScreenState createState() => LoginScreenState();
}

class LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _rememberMe = true; // Inicia marcado por padrão

  void _login() async {
    // Valida o formulário antes de prosseguir
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    
    // Chama o método de login passando o valor da flag 'rememberMe'
    final success = await authProvider.login(
      _emailController.text.trim(),
      _passwordController.text.trim(),
      rememberMe: _rememberMe,
    );

    if (success && mounted) {
      // Em caso de sucesso, substitui a tela atual pela MainScreen
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const MainScreen()),
      );
    } else if (mounted) {
      // Em caso de erro, mostra uma mensagem para o usuário
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(authProvider.errorMessage ?? 'Ocorreu um erro desconhecido.'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bingou! - Login'),
        automaticallyImplyLeading: false, // Remove o botão de voltar
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Form(
            key: _formKey,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                TextFormField(
                  controller: _emailController,
                  decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder(), prefixIcon: Icon(Icons.email)),
                  keyboardType: TextInputType.emailAddress,
                  validator: (val) => val!.isEmpty || !val.contains('@') ? 'Insira um email válido' : null,
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _passwordController,
                  decoration: const InputDecoration(labelText: 'Senha', border: OutlineInputBorder(), prefixIcon: Icon(Icons.lock)),
                  obscureText: true,
                  validator: (val) => val!.isEmpty ? 'Insira a senha' : null,
                ),
                CheckboxListTile(
                  title: const Text("Lembrar-me neste dispositivo"),
                  value: _rememberMe,
                  onChanged: (newValue) {
                    setState(() {
                      _rememberMe = newValue!;
                    });
                  },
                  controlAffinity: ListTileControlAffinity.leading, // Checkbox à esquerda
                  contentPadding: EdgeInsets.zero,
                ),
                const SizedBox(height: 16),
                Consumer<AuthProvider>(
                  builder: (ctx, auth, _) => ElevatedButton(
                    style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
                    onPressed: auth.isLoading ? null : _login,
                    child: auth.isLoading ? const CircularProgressIndicator(color: Colors.white) : const Text('ENTRAR'),
                  ),
                ),
                TextButton(
                  onPressed: () {
                    Navigator.of(context).push(MaterialPageRoute(builder: (_) => const RegisterScreen()));
                  },
                  child: const Text('Não tem uma conta? Registre-se'),
                ),
                const SizedBox(height: 16),
                const Divider(),
                TextButton.icon(
                  icon: const Icon(Icons.visibility),
                  label: const Text('Acompanhar uma sessão como convidado'),
                  onPressed: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const JoinSessionScreen()),
                    );
                  },
                )
              ],
            ),
          ),
        ),
      ),
    );
  }
}