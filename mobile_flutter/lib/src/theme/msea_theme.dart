import 'package:flutter/material.dart';

class MseaColors {
  static const green = Color(0xFF8CC63F);
  static const greenDark = Color(0xFF6BA02E);
  static const greenSoft = Color(0xFFE8F5D0);
  static const background = Color(0xFFF2FAE8);
  static const text = Color(0xFF4A4A4A);
  static const muted = Color(0xFF888888);
  static const yellow = Color(0xFFFFD54F);
  static const orange = Color(0xFFFF9800);
  static const blue = Color(0xFF42A5F5);
}

class MseaTheme {
  static ThemeData light() {
    final scheme = ColorScheme.fromSeed(
      seedColor: MseaColors.green,
      primary: MseaColors.green,
      secondary: MseaColors.yellow,
      surface: Colors.white,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: MseaColors.background,
      fontFamily: 'Nunito',
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.white,
        foregroundColor: MseaColors.text,
        elevation: 0,
        centerTitle: false,
      ),
      cardTheme: CardTheme(
        color: Colors.white,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: MseaColors.green,
          foregroundColor: Colors.white,
          minimumSize: const Size.fromHeight(48),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
          textStyle: const TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide.none,
        ),
      ),
    );
  }
}
