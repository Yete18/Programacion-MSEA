import 'package:flutter/material.dart';

import 'src/api/msea_api.dart';
import 'src/screens/dashboard_screen.dart';
import 'src/screens/login_screen.dart';
import 'src/theme/msea_theme.dart';

void main() {
  runApp(const MseaMobileApp());
}

class MseaMobileApp extends StatefulWidget {
  const MseaMobileApp({super.key});

  @override
  State<MseaMobileApp> createState() => _MseaMobileAppState();
}

class _MseaMobileAppState extends State<MseaMobileApp> {
  final MseaApi api = MseaApi();
  SessionData? session;

  void _setSession(SessionData value) {
    setState(() => session = value);
  }

  void _logout() {
    api.logout();
    setState(() => session = null);
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'MSEA Mobile',
      debugShowCheckedModeBanner: false,
      theme: MseaTheme.light(),
      home: session == null
          ? LoginScreen(api: api, onLoggedIn: _setSession)
          : DashboardScreen(api: api, session: session!, onLogout: _logout),
    );
  }
}
