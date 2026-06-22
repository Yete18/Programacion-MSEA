import 'package:flutter/material.dart';

import '../api/msea_api.dart';
import '../theme/msea_theme.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({
    super.key,
    required this.api,
    required this.session,
    required this.onLogout,
  });

  final MseaApi api;
  final SessionData session;
  final VoidCallback onLogout;

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  late Future<Map<String, dynamic>> dashboardFuture;

  @override
  void initState() {
    super.initState();
    dashboardFuture = widget.api.me();
  }

  Future<void> _practice(String type) async {
    final xp = await widget.api.registerPractice(type);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Practica registrada: +$xp XP')));
    setState(() => dashboardFuture = widget.api.me());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Hola, ${widget.session.user.name}'),
        actions: [
          IconButton(onPressed: widget.onLogout, icon: const Icon(Icons.logout)),
        ],
      ),
      body: FutureBuilder<Map<String, dynamic>>(
        future: dashboardFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(child: Text('Error: ${snapshot.error}'));
          }

          final data = snapshot.data ?? {};
          final dashboard = data['dashboard'];
          return RefreshIndicator(
            onRefresh: () async => setState(() => dashboardFuture = widget.api.me()),
            child: ListView(
              padding: const EdgeInsets.all(18),
              children: [
                _HeroCard(user: widget.session.user),
                const SizedBox(height: 16),
                if (widget.session.user.role == 'estudiante') ...[
                  _StudentStats(dashboard: dashboard as Map<String, dynamic>?),
                  const SizedBox(height: 16),
                  _PracticeTools(onPractice: _practice),
                ] else ...[
                  _GenericDashboard(role: widget.session.user.role, dashboard: dashboard),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}

class _HeroCard extends StatelessWidget {
  const _HeroCard({required this.user});
  final MseaUser user;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [MseaColors.greenDark, MseaColors.green]),
        borderRadius: BorderRadius.circular(22),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(user.role.toUpperCase(), style: const TextStyle(color: Colors.white70, fontWeight: FontWeight.w900)),
          const SizedBox(height: 8),
          Text(user.name, style: const TextStyle(color: Colors.white, fontSize: 26, fontWeight: FontWeight.w900)),
          const SizedBox(height: 6),
          Text(user.email, style: const TextStyle(color: Colors.white)),
        ],
      ),
    );
  }
}

class _StudentStats extends StatelessWidget {
  const _StudentStats({required this.dashboard});
  final Map<String, dynamic>? dashboard;

  @override
  Widget build(BuildContext context) {
    final student = dashboard?['dashboardData'] as Map<String, dynamic>? ?? {};
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      childAspectRatio: 1.45,
      children: [
        _StatTile(label: 'XP', value: '${student['puntos'] ?? 0}'),
        _StatTile(label: 'Nivel', value: '${student['nivel'] ?? 1}'),
        _StatTile(label: 'Racha', value: '${student['racha'] ?? 0} dias'),
        _StatTile(label: 'Ranking', value: '#${student['rankingPos'] ?? '-'}'),
      ],
    );
  }
}

class _StatTile extends StatelessWidget {
  const _StatTile({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(value, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: MseaColors.greenDark)),
            const SizedBox(height: 4),
            Text(label, style: const TextStyle(color: MseaColors.muted, fontWeight: FontWeight.w800)),
          ],
        ),
      ),
    );
  }
}

class _PracticeTools extends StatelessWidget {
  const _PracticeTools({required this.onPractice});
  final Future<void> Function(String type) onPractice;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Practica autonoma', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            const SizedBox(height: 12),
            _PracticeButton(label: 'Registrar afinacion', icon: Icons.graphic_eq, onTap: () => onPractice('afinacion')),
            _PracticeButton(label: 'Registrar ritmo', icon: Icons.timer, onTap: () => onPractice('ritmo')),
            _PracticeButton(label: 'Registrar teoria', icon: Icons.menu_book, onTap: () => onPractice('teoria')),
          ],
        ),
      ),
    );
  }
}

class _PracticeButton extends StatelessWidget {
  const _PracticeButton({required this.label, required this.icon, required this.onTap});
  final String label;
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: CircleAvatar(
        backgroundColor: MseaColors.greenSoft,
        foregroundColor: MseaColors.greenDark,
        child: Icon(icon),
      ),
      title: Text(label, style: const TextStyle(fontWeight: FontWeight.w800)),
      trailing: const Icon(Icons.chevron_right),
      onTap: onTap,
    );
  }
}

class _GenericDashboard extends StatelessWidget {
  const _GenericDashboard({required this.role, required this.dashboard});
  final String role;
  final Object? dashboard;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Panel $role', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            const SizedBox(height: 10),
            Text(dashboard?.toString() ?? 'Sin datos todavia'),
          ],
        ),
      ),
    );
  }
}
