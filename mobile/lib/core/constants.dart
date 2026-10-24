import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

class AppColors {
  static const Color primary = Color(0xFF10223D);       // Azul institucional profundo
  static const Color primaryLight = Color(0xFF1E3A63);  // Azul medio
  static const Color accent = Color(0xFFE5A838);        // Dorado gremial
  static const Color accentLight = Color(0xFFF7C822);   // Amarillo trigo
  static const Color background = Color(0xFFF4F6F9);    // Gris claro fondo
  static const Color surface = Colors.white;
  static const Color textMain = Color(0xFF1C2430);      // Texto principal
  static const Color textMuted = Color(0xFF6B7280);     // Texto secundario
  static const Color success = Color(0xFF1E8E3E);       // Verde activo
  static const Color error = Color(0xFFD93025);         // Rojo alerta
  static const Color border = Color(0xFFE5E7EB);
}

class ApiConfig {
  /// Detecta automáticamente el host:
  /// - En Chrome (Web): http://localhost/panaderos/api
  /// - En Emulador Android: http://10.0.2.2/panaderos/api
  static String get baseUrl =>
      kIsWeb ? 'http://localhost/panaderos/api' : 'http://10.0.2.2/panaderos/api';

  static String get validarPadron => '$baseUrl/auth/validar-padron.php';
  static String get register => '$baseUrl/auth/register.php';
  static String get login => '$baseUrl/auth/login.php';
  static String get carnetDigital => '$baseUrl/carnet/digital.php';
  static String get beneficios => '$baseUrl/beneficios/index.php';
  static String get eventos => '$baseUrl/eventos/index.php';
  static String get inscribirseEvento => '$baseUrl/eventos/inscribirse.php';
  static String get logout => '$baseUrl/auth/logout.php';
  static String get deleteAccount => '$baseUrl/auth/delete-account.php';
}

/// Helper para formatear fechas a formato estándar argentino: dd/mm/aaaa
String formatFecha(String? dateStr) {
  if (dateStr == null || dateStr.trim().isEmpty) return '';
  final cleaned = dateStr.trim();
  if (RegExp(r'^\d{2}/\d{2}/\d{4}$').hasMatch(cleaned)) return cleaned;
  try {
    final parsed = DateTime.parse(cleaned);
    final day = parsed.day.toString().padLeft(2, '0');
    final month = parsed.month.toString().padLeft(2, '0');
    final year = parsed.year.toString();
    return '$day/$month/$year';
  } catch (_) {
    return cleaned;
  }
}

/// Helper para convertir formato argentino dd/mm/aaaa a formato ISO aaaa-mm-dd
String parseFechaToIso(String? dateStr) {
  if (dateStr == null || dateStr.trim().isEmpty) return '';
  final cleaned = dateStr.trim();
  final match = RegExp(r'^(\d{1,2})/(\d{1,2})/(\d{4})$').firstMatch(cleaned);
  if (match != null) {
    final day = match.group(1)!.padLeft(2, '0');
    final month = match.group(2)!.padLeft(2, '0');
    final year = match.group(3)!;
    return '$year-$month-$day';
  }
  return cleaned;
}


