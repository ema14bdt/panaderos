import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/carnet.dart';

class StorageService {
  static const String _tokenKey = 'auth_token';
  static const String _carnetCacheKey = 'cached_carnet';

  /// Guardar token de sesión
  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  /// Obtener token guardado
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  /// Comprobar si existe sesión activa
  static Future<bool> hasToken() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  /// Guardar carnet en memoria persistente para acceso Offline
  static Future<void> cacheCarnet(CarnetData carnet) async {
    final prefs = await SharedPreferences.getInstance();
    final jsonStr = jsonEncode(carnet.toJson());
    await prefs.setString(_carnetCacheKey, jsonStr);
  }

  /// Cargar carnet guardado offline
  static Future<CarnetData?> getCachedCarnet() async {
    final prefs = await SharedPreferences.getInstance();
    final jsonStr = prefs.getString(_carnetCacheKey);
    if (jsonStr == null) return null;

    try {
      final map = jsonDecode(jsonStr);
      return CarnetData.fromJson(map);
    } catch (_) {
      return null;
    }
  }

  /// Cerrar sesión y limpiar almacenamiento
  static Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_carnetCacheKey);
  }
}
