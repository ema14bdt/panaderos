import 'dart:convert';
import 'package:http/http.dart' as http;
import '../core/constants.dart';
import '../models/afiliado.dart';
import '../models/beneficio.dart';
import '../models/carnet.dart';
import 'storage_service.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;

  ApiException(this.message, [this.statusCode]);

  @override
  String toString() => message;
}

class ApiService {
  final http.Client _client;

  ApiService({http.Client? client}) : _client = client ?? http.Client();

  /// Paso 1: Validar DNI y Número de Afiliado contra el padrón
  Future<Map<String, dynamic>> validarPadron(String dni, String numeroAfiliado) async {
    try {
      final response = await _client.post(
        Uri.parse(ApiConfig.validarPadron),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'dni': dni.trim(),
          'numero_afiliado': numeroAfiliado.trim(),
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        return Map<String, dynamic>.from(data['data']);
      } else {
        throw ApiException(data['error'] ?? 'No se pudo verificar el padrón.', response.statusCode);
      }
    } on ApiException {
      rethrow;
    } catch (e) {
      throw ApiException('Error de conexión con el servidor. Verifique su acceso a internet.');
    }
  }

  /// Paso 2: Registrar y activar cuenta de afiliado
  Future<Afiliado> register({
    required String dni,
    required String numeroAfiliado,
    required String email,
    required String password,
    String? telefono,
  }) async {
    try {
      final response = await _client.post(
        Uri.parse(ApiConfig.register),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'dni': dni.trim(),
          'numero_afiliado': numeroAfiliado.trim(),
          'email': email.trim().toLowerCase(),
          'password': password,
          'telefono': telefono?.trim(),
        }),
      );

      final data = jsonDecode(response.body);

      if ((response.statusCode == 200 || response.statusCode == 201) && data['success'] == true) {
        final token = data['data']['auth_token'] as String;
        await StorageService.saveToken(token);
        return Afiliado.fromJson(data['data']['afiliado']);
      } else {
        throw ApiException(data['error'] ?? 'Error al registrar afiliado.', response.statusCode);
      }
    } on ApiException {
      rethrow;
    } catch (e) {
      throw ApiException('Error de conexión con el servidor.');
    }
  }

  /// Inicio de sesión con DNI o Email y contraseña
  Future<Afiliado> login(String usuario, String password) async {
    try {
      final response = await _client.post(
        Uri.parse(ApiConfig.login),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'usuario': usuario.trim(),
          'password': password,
        }),
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final token = data['data']['auth_token'] as String;
        await StorageService.saveToken(token);
        return Afiliado.fromJson(data['data']['afiliado']);
      } else {
        throw ApiException(data['error'] ?? 'Credenciales incorrectas.', response.statusCode);
      }
    } on ApiException {
      rethrow;
    } catch (e) {
      throw ApiException('Error al conectar con el servidor.');
    }
  }

  /// Obtener Carnet Digital (con soporte Offline automático)
  Future<CarnetData> getCarnetDigital() async {
    final token = await StorageService.getToken();
    if (token == null) {
      throw ApiException('No hay sesión iniciada.', 401);
    }

    try {
      final response = await _client.get(
        Uri.parse(ApiConfig.carnetDigital),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final carnet = CarnetData.fromJson(data['data']);
        // Guardar en caché para acceso sin internet
        await StorageService.cacheCarnet(carnet);
        return carnet;
      } else {
        throw ApiException(data['error'] ?? 'No se pudo cargar el carnet.', response.statusCode);
      }
    } catch (e) {
      // Si falla la red o el servidor, intentar cargar el carnet desde el caché offline
      final cached = await StorageService.getCachedCarnet();
      if (cached != null) {
        return cached;
      }
      throw ApiException('No se pudo conectar y no hay carnet guardado offline.');
    }
  }

  /// Obtener listado de Beneficios y Convenios
  Future<List<Beneficio>> getBeneficios({String? rubro, String? query}) async {
    try {
      final queryParams = <String, String>{};
      if (rubro != null && rubro.isNotEmpty && rubro != 'todos') {
        queryParams['rubro'] = rubro;
      }
      if (query != null && query.trim().isNotEmpty) {
        queryParams['q'] = query.trim();
      }

      final uri = Uri.parse(ApiConfig.beneficios).replace(
        queryParameters: queryParams.isNotEmpty ? queryParams : null,
      );

      final response = await _client.get(
        uri,
        headers: {'Content-Type': 'application/json'},
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        final list = (data['data']['beneficios'] as List<dynamic>?)
                ?.map((item) => Beneficio.fromJson(item as Map<String, dynamic>))
                .toList() ??
            [];
        return list;
      } else {
        throw ApiException(
            data['error'] ?? 'No se pudieron cargar los beneficios.',
            response.statusCode);
      }
    } on ApiException {
      rethrow;
    } catch (e) {
      throw ApiException(
          'Error al conectar con el servidor para consultar beneficios.');
    }
  }
}
