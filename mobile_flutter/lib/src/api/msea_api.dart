import 'dart:convert';

import 'package:http/http.dart' as http;

const String defaultApiUrl = String.fromEnvironment(
  'MSEA_API_URL',
  defaultValue: 'http://127.0.0.1:8000',
);

class SessionData {
  SessionData({required this.token, required this.user});

  final String token;
  final MseaUser user;
}

class MseaUser {
  MseaUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
  });

  final int id;
  final String name;
  final String email;
  final String role;

  factory MseaUser.fromJson(Map<String, dynamic> json) {
    return MseaUser(
      id: json['id'] as int,
      name: json['nombre'] as String? ?? 'Usuario MSEA',
      email: json['correo'] as String? ?? '',
      role: json['rol'] as String? ?? 'estudiante',
    );
  }
}

class MseaApi {
  MseaApi({String baseUrl = defaultApiUrl}) : baseUrl = baseUrl.replaceAll(RegExp(r'/$'), '');

  final String baseUrl;
  String? _token;

  Future<SessionData> login({
    required String email,
    required String password,
    required String role,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/mobile/login'),
      headers: {'Accept': 'application/json'},
      body: {
        'correo': email,
        'contrasena': password,
        'rol': role,
        'device_name': 'Flutter MSEA',
      },
    );

    final data = _decode(response);

    if (response.statusCode >= 400) {
      throw ApiException(data['message']?.toString() ?? 'No se pudo iniciar sesion.');
    }

    _token = data['token'] as String;
    return SessionData(
      token: _token!,
      user: MseaUser.fromJson(data['usuario'] as Map<String, dynamic>),
    );
  }

  Future<Map<String, dynamic>> me() async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/mobile/me'),
      headers: _headers(),
    );

    final data = _decode(response);
    if (response.statusCode >= 400) {
      throw ApiException(data['message']?.toString() ?? 'No se pudo cargar el dashboard.');
    }

    return data;
  }

  Future<int> registerPractice(String type) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/mobile/practice'),
      headers: _headers(),
      body: jsonEncode({
        'tipo': type,
        'duracion_segundos': 300,
        'precision': 85,
      }),
    );

    final data = _decode(response);
    if (response.statusCode >= 400) {
      throw ApiException(data['message']?.toString() ?? 'No se pudo registrar la practica.');
    }

    return data['xp'] as int? ?? 0;
  }

  Future<void> logout() async {
    if (_token == null) return;
    await http.post(Uri.parse('$baseUrl/api/mobile/logout'), headers: _headers());
    _token = null;
  }

  Map<String, String> _headers() {
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (_token != null) 'Authorization': 'Bearer $_token',
    };
  }

  Map<String, dynamic> _decode(http.Response response) {
    if (response.body.isEmpty) return <String, dynamic>{};
    return jsonDecode(response.body) as Map<String, dynamic>;
  }
}

class ApiException implements Exception {
  ApiException(this.message);
  final String message;
}
