import 'package:flutter/material.dart';

import '../api/msea_api.dart';
import '../theme/msea_theme.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key, required this.api, required this.onLoggedIn});

  final MseaApi api;
  final ValueChanged<SessionData> onLoggedIn;

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final emailController = TextEditingController(text: 'estudiante@msea.test');
  final passwordController = TextEditingController(text: 'estudiante123');
  String role = 'estudiante';
  bool loading = false;
  String? error;

  @override
  void dispose() {
    emailController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    setState(() {
      loading = true;
      error = null;
    });

    try {
      final session = await widget.api.login(
        email: emailController.text.trim(),
        password: passwordController.text,
        role: role,
      );
      widget.onLoggedIn(session);
    } on ApiException catch (exception) {
      setState(() => error = exception.message);
    } catch (_) {
      setState(() => error = 'No se pudo conectar con Laravel.');
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const SizedBox(height: 18),
            Container(
              padding: const EdgeInsets.all(22),
              decoration: BoxDecoration(
                color: MseaColors.green,
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  CircleAvatar(
                    backgroundColor: Colors.white,
                    foregroundColor: MseaColors.greenDark,
                    child: Text('M'),
                  ),
                  SizedBox(height: 18),
                  Text(
                    'MSEA',
                    style: TextStyle(color: Colors.white, fontSize: 34, fontWeight: FontWeight.w900),
                  ),
                  Text(
                    'Movimiento Sinfonico de El Alto',
                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),
            Text('Iniciar sesion', style: Theme.of(context).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.w900)),
            const SizedBox(height: 8),
            const Text('Conectado al backend Laravel y PostgreSQL.', style: TextStyle(color: MseaColors.muted)),
            const SizedBox(height: 22),
            SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'estudiante', label: Text('Estudiante')),
                ButtonSegment(value: 'profesor', label: Text('Docente')),
                ButtonSegment(value: 'padre', label: Text('Padre')),
                ButtonSegment(value: 'admin', label: Text('Admin')),
              ],
              selected: {role},
              onSelectionChanged: (value) => setState(() => role = value.first),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: emailController,
              keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(labelText: 'Correo'),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: passwordController,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Contrasena'),
            ),
            if (error != null) ...[
              const SizedBox(height: 12),
              Text(error!, style: const TextStyle(color: Colors.red, fontWeight: FontWeight.w700)),
            ],
            const SizedBox(height: 22),
            ElevatedButton(
              onPressed: loading ? null : _login,
              child: loading ? const CircularProgressIndicator() : const Text('Entrar'),
            ),
          ],
        ),
      ),
    );
  }
}
