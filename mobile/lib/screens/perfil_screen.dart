import 'package:flutter/material.dart';
import '../core/constants.dart';
import '../models/carnet.dart';
import '../services/api_service.dart';
import '../widgets/panaderos_app_bar.dart';
import 'login_screen.dart';

class PerfilScreen extends StatefulWidget {
  final VoidCallback? onSwitchToAdmin;

  const PerfilScreen({super.key, this.onSwitchToAdmin});

  @override
  State<PerfilScreen> createState() => _PerfilScreenState();
}

class _PerfilScreenState extends State<PerfilScreen> {
  final ApiService _apiService = ApiService();

  CarnetData? _carnetData;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _cargarPerfil();
  }

  Future<void> _cargarPerfil() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final carnet = await _apiService.getCarnetDigital();
      if (!mounted) return;
      setState(() {
        _carnetData = carnet;
        _isLoading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage = e.message;
        _isLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _errorMessage = 'No se pudo cargar el perfil.';
        _isLoading = false;
      });
    }
  }

  Future<void> _confirmarCerrarSesion() async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cerrar Sesión'),
        content: const Text('¿Estás seguro de que deseás cerrar la sesión en este dispositivo?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
            ),
            child: const Text('Cerrar Sesión'),
          ),
        ],
      ),
    );

    if (confirmar == true && mounted) {
      await _apiService.logout();
      if (!mounted) return;
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (route) => false,
      );
    }
  }

  Future<void> _confirmarEliminarCuenta() async {
    final passwordController = TextEditingController();

    final confirmar = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: AppColors.error),
            SizedBox(width: 8),
            Expanded(child: Text('Eliminar Cuenta')),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Esta acción eliminará de forma permanente tu usuario, contraseña y acceso en esta aplicación móvil.',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
              const SizedBox(height: 10),
              Text(
                '• Tu condición de afiliado en el padrón físico del Sindicato se mantiene inalterada según la ley gremial.\n'
                '• Se borrarán las sesiones activas y tokens de este dispositivo.\n'
                '• Si en el futuro deseás volver a ingresar, deberás registrarte nuevamente validando tu DNI.',
                style: TextStyle(fontSize: 12, color: Colors.grey.shade700, height: 1.4),
              ),
              const SizedBox(height: 16),
              const Text(
                'Para confirmar, ingresá tu contraseña:',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 6),
              TextField(
                controller: passwordController,
                obscureText: true,
                decoration: InputDecoration(
                  hintText: 'Tu contraseña',
                  hintStyle: TextStyle(fontSize: 13, color: Colors.grey.shade400),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Cancelar'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
            ),
            child: const Text('Eliminar Definitivamente'),
          ),
        ],
      ),
    );

    if (confirmar == true && mounted) {
      try {
        await _apiService.deleteAccount(
          password: passwordController.text.isNotEmpty ? passwordController.text : null,
        );
        if (!mounted) return;

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tu cuenta ha sido eliminada correctamente.'),
            backgroundColor: AppColors.primary,
          ),
        );

        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
          (route) => false,
        );
      } on ApiException catch (e) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.message),
            backgroundColor: AppColors.error,
          ),
        );
      } catch (_) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Error al conectar con el servidor.'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: PanaderosAppBar(
        subtitle: 'MI PERFIL SINDICAL',
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Actualizar',
            onPressed: _cargarPerfil,
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: AppColors.primary),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, size: 48, color: AppColors.error),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 15, color: AppColors.textMain),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: _cargarPerfil,
                icon: const Icon(Icons.refresh),
                label: const Text('Reintentar'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                ),
              ),
            ],
          ),
        ),
      );
    }

    final carnet = _carnetData!;
    final afiliado = carnet.afiliado;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Tarjeta de Cabecera del Afiliado
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppColors.primary, AppColors.primaryLight],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: AppColors.primary.withValues(alpha: 0.25),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              children: [
                // Avatar con iniciales
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white.withValues(alpha: 0.15),
                    border: Border.all(color: AppColors.accent, width: 2),
                  ),
                  child: Center(
                    child: Text(
                      _getInitials(afiliado.nombreCompleto),
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  afiliado.nombreCompleto.toUpperCase(),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'N° Afiliado: ${afiliado.numeroAfiliado}',
                  style: const TextStyle(
                    fontSize: 14,
                    color: AppColors.accentLight,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 10),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: afiliado.isActivo
                        ? AppColors.success.withValues(alpha: 0.2)
                        : AppColors.error.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: afiliado.isActivo ? AppColors.success : AppColors.error,
                    ),
                  ),
                  child: Text(
                    afiliado.estado.toUpperCase(),
                    style: TextStyle(
                      color: afiliado.isActivo ? const Color(0xFF81C995) : const Color(0xFFF28B82),
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // 1. Datos de Afiliación y Laborales
          _buildSectionContainer(
            title: 'Datos de Afiliación Sindical',
            icon: Icons.badge_outlined,
            children: [
              _buildDataRow('DNI:', afiliado.dni),
              _buildDataRow('Empresa / Panadería:', afiliado.empresaPanaderia ?? 'No especificada'),
              _buildDataRow('Categoría Laboral:', afiliado.categoriaLaboral ?? 'Panadero'),
              if (afiliado.fechaAfiliacion != null)
                _buildDataRow('Fecha de Afiliación:', formatFecha(afiliado.fechaAfiliacion)),
              if (afiliado.email != null && afiliado.email!.isNotEmpty)
                _buildDataRow('Email Registrado:', afiliado.email!),
              if (afiliado.telefono != null && afiliado.telefono!.isNotEmpty)
                _buildDataRow('Teléfono:', afiliado.telefono!),
            ],
          ),

          const SizedBox(height: 16),

          // 2. Grupo Familiar
          _buildSectionContainer(
            title: 'Grupo Familiar (${carnet.familiares.length})',
            icon: Icons.family_restroom,
            children: [
              if (carnet.familiares.isEmpty)
                Text(
                  'No tenés familiares o hijos declarados en el padrón.',
                  style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                )
              else
                ...carnet.familiares.map((fam) {
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Row(
                      children: [
                        const Icon(Icons.child_care, size: 18, color: AppColors.primaryLight),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                fam.nombreCompleto,
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                              Text(
                                '${fam.parentesco.toUpperCase()} · ${fam.edad} años · DNI: ${fam.dni ?? "S/D"}',
                                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                }),
            ],
          ),

          const SizedBox(height: 16),

          // 3. Sede Sindical de Referencia
          _buildSectionContainer(
            title: 'Sede Sindical Lanús',
            icon: Icons.location_on_outlined,
            children: [
              _buildDataRow('Dirección:', carnet.institucion['sede_central'] ?? 'Llavallol 902 esq. Rio de Janeiro'),
              _buildDataRow('Teléfono:', carnet.institucion['telefono'] ?? '4241-1920'),
              _buildDataRow('Atención:', 'Lunes a Viernes de 8:00 a 17:00 hs'),
            ],
          ),

          const SizedBox(height: 24),

          // 4. Acciones de Cuenta
          if (afiliado.isAdmin) ...[
            Container(
              margin: const EdgeInsets.only(bottom: 14),
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: widget.onSwitchToAdmin,
                icon: const Icon(Icons.admin_panel_settings, size: 20),
                label: const Text(
                  'Cambiar a Vista Administrador',
                  style: TextStyle(
                    fontFamily: 'Manrope',
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
          ],

          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: _confirmarCerrarSesion,
              icon: const Icon(Icons.logout, size: 18),
              label: const Text('Cerrar Sesión'),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.primary,
                side: const BorderSide(color: AppColors.primary),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          ),

          const SizedBox(height: 12),

          // Botón de Eliminación de Cuenta (Requisito Apple Store 5.1.1v)
          SizedBox(
            width: double.infinity,
            child: TextButton.icon(
              onPressed: _confirmarEliminarCuenta,
              icon: const Icon(Icons.delete_outline, size: 18, color: AppColors.error),
              label: const Text(
                'Eliminar Cuenta de la App',
                style: TextStyle(color: AppColors.error, fontWeight: FontWeight.w600),
              ),
            ),
          ),

          const SizedBox(height: 8),

          Text(
            'Sindicato de Obreros Panaderos de Lanús · Versión 1.0.0',
            style: TextStyle(fontSize: 11, color: Colors.grey.shade400),
          ),
          const SizedBox(height: 16),
        ],
      ),
    );
  }

  Widget _buildSectionContainer({
    required String title,
    required IconData icon,
    required List<Widget> children,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 20, color: AppColors.primary),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: AppColors.primary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Divider(height: 1),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }

  Widget _buildDataRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 140,
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: AppColors.textMuted,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: AppColors.textMain,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _getInitials(String fullName) {
    final parts = fullName.trim().split(RegExp(r'\s+'));
    if (parts.isEmpty) return 'P';
    if (parts.length == 1) return parts.first.substring(0, 1).toUpperCase();
    return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
  }
}
