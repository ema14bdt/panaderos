import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

class AppColors {
  // Paleta institucional unificada con la web oficial (--ink, --sun, --cream, --red)
  static const Color primary = Color(0xFF3E4D5F);       // --ink: Azul/gris pizarra institucional
  static const Color primaryDark = Color(0xFF2B3744);   // Versión profunda para AppBars y headers
  static const Color primaryLight = Color(0xFF5A6B7E);  // Azul pizarra medio
  static const Color accent = Color(0xFF49B8E7);        // --sun: Celeste institucional
  static const Color accentLight = Color(0xFF67D0F8);   // Celeste claro para contrastes
  static const Color accentDark = Color(0xFF0369A1);    // --red: Azul cerúleo para enlaces y destacados
  static const Color cream = Color(0xFFF6F2E8);         // --cream: Crema suave
  static const Color background = Color(0xFFF8FAFC);    // Fondo limpio moderno
  static const Color surface = Colors.white;
  static const Color textMain = Color(0xFF1E293B);      // Texto principal
  static const Color textMuted = Color(0xFF64748B);     // Texto secundario
  static const Color success = Color(0xFF1E8E3E);       // Verde activo
  static const Color error = Color(0xFFD93025);         // Rojo alerta
  static const Color border = Color(0xFFE2E8F0);
}

class ApiConfig {
  /// Detecta automáticamente el host:
  /// - En Chrome (Web): http://localhost/panaderos/api
  /// - En Emulador Android: http://10.0.2.2/panaderos/api
  static String get baseUrl =>
      kIsWeb ? 'http://localhost/panaderos/api' : 'http://10.0.2.2/panaderos/api';

  static String get home => '$baseUrl/home/index.php';
  static String get validarPadron => '$baseUrl/auth/validar-padron.php';
  static String get register => '$baseUrl/auth/register.php';
  static String get login => '$baseUrl/auth/login.php';
  static String get carnetDigital => '$baseUrl/carnet/digital.php';
  static String get beneficios => '$baseUrl/beneficios/index.php';
  static String get eventos => '$baseUrl/eventos/index.php';
  static String get inscribirseEvento => '$baseUrl/eventos/inscribirse.php';
  static String get logout => '$baseUrl/auth/logout.php';
  static String get deleteAccount => '$baseUrl/auth/delete-account.php';
  static String get adminMetricas => '$baseUrl/admin/metricas.php';
  static String get adminValidarComprobante => '$baseUrl/admin/validar-comprobante.php';
  static String get adminAfiliados => '$baseUrl/admin/afiliados.php';
  static String get adminInscripciones => '$baseUrl/admin/inscripciones.php';
  static String get adminEventos => '$baseUrl/admin/eventos.php';
  static String get adminGuardarEvento => '$baseUrl/admin/guardar-evento.php';
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


