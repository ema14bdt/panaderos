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
}
