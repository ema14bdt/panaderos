import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/constants.dart';
import '../services/api_service.dart';
import '../widgets/panaderos_app_bar.dart';
import '../widgets/qr_scanner_dialog.dart';

class AdminScreen extends StatefulWidget {
  final VoidCallback onSwitchToAfiliado;

  const AdminScreen({super.key, required this.onSwitchToAfiliado});

  @override
  State<AdminScreen> createState() => _AdminScreenState();
}

class _AdminScreenState extends State<AdminScreen> {
  final ApiService _apiService = ApiService();
  final TextEditingController _codigoController = TextEditingController();
  final TextEditingController _afiliadosSearchController = TextEditingController();
  final TextEditingController _inscripcionesSearchController = TextEditingController();

  int _currentIndex = 0;

  // Estado pestaña Métricas / Validador
  Map<String, dynamic>? _metricas;
  Map<String, dynamic>? _comprobanteValidado;
  bool _isLoadingMetricas = true;
  bool _isValidatingCodigo = false;
  bool _isConfirmingEntrega = false;
  String? _errorMetricas;
  String? _errorValidacion;

  // Estado pestaña Afiliados
  List<Map<String, dynamic>> _afiliados = [];
  bool _isLoadingAfiliados = false;
  String? _errorAfiliados;
  String _afiliadosFiltro = 'todos'; // todos | app | padron

  // Estado pestaña Inscriptos
  List<Map<String, dynamic>> _inscripciones = [];
  bool _isLoadingInscripciones = false;
  String? _errorInscripciones;
  String _inscripcionesEstadoFiltro = 'todos'; // todos | solicitado | entregado
  int? _inscripcionesEventoFiltro;
  String? _inscripcionesEventoFiltroTitulo;

  // Estado pestaña Eventos
  List<Map<String, dynamic>> _eventos = [];
  bool _isLoadingEventos = false;
  String? _errorEventos;

  @override
  void initState() {
    super.initState();
    _cargarMetricas();
  }

  @override
  void dispose() {
    _codigoController.dispose();
    _afiliadosSearchController.dispose();
    _inscripcionesSearchController.dispose();
    super.dispose();
  }

  void _onNavigationChanged(int index) {
    setState(() {
      _currentIndex = index;
    });

    if (index == 1 && _afiliados.isEmpty) {
      _cargarAfiliados();
    } else if (index == 2 && _inscripciones.isEmpty) {
      _cargarInscripciones();
    } else if (index == 3 && _eventos.isEmpty) {
      _cargarEventos();
    }
  }

  // ==========================================
  // CARGA DE DATOS DESDE LA API
  // ==========================================

  Future<void> _cargarMetricas() async {
    setState(() {
      _isLoadingMetricas = true;
      _errorMetricas = null;
    });

    try {
      final res = await _apiService.getAdminMetricas();
      if (!mounted) return;
      setState(() {
        _metricas = res;
        _isLoadingMetricas = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMetricas = e.message;
        _isLoadingMetricas = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _errorMetricas = 'No se pudieron cargar las métricas administrativas.';
        _isLoadingMetricas = false;
      });
    }
  }

  Future<void> _cargarAfiliados() async {
    setState(() {
      _isLoadingAfiliados = true;
      _errorAfiliados = null;
    });

    try {
      final res = await _apiService.getAdminAfiliados(
        search: _afiliadosSearchController.text.trim(),
        filtro: _afiliadosFiltro,
      );
      if (!mounted) return;
      setState(() {
        _afiliados = res;
        _isLoadingAfiliados = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _errorAfiliados = e.message;
        _isLoadingAfiliados = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _errorAfiliados = 'Error al cargar afiliados.';
        _isLoadingAfiliados = false;
      });
    }
  }

  Future<void> _cargarInscripciones() async {
    setState(() {
      _isLoadingInscripciones = true;
      _errorInscripciones = null;
    });

    try {
      final res = await _apiService.getAdminInscripciones(
        eventoId: _inscripcionesEventoFiltro,
        estado: _inscripcionesEstadoFiltro,
        search: _inscripcionesSearchController.text.trim(),
      );
      if (!mounted) return;
      setState(() {
        _inscripciones = res;
        _isLoadingInscripciones = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _errorInscripciones = e.message;
        _isLoadingInscripciones = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _errorInscripciones = 'Error al cargar inscriptos.';
        _isLoadingInscripciones = false;
      });
    }
  }

  Future<void> _cargarEventos() async {
    setState(() {
      _isLoadingEventos = true;
      _errorEventos = null;
    });

    try {
      final res = await _apiService.getAdminEventos();
      if (!mounted) return;
      setState(() {
        _eventos = res;
        _isLoadingEventos = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _errorEventos = e.message;
        _isLoadingEventos = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _errorEventos = 'Error al cargar eventos.';
        _isLoadingEventos = false;
      });
    }
  }

  // ==========================================
  // ESCÁNER QR Y VALIDACIÓN DE RETIRO
  // ==========================================

  Future<void> _escanearQrConCamara() async {
    final scannedCode = await QrScannerScreen.scan(context);
    if (scannedCode != null && scannedCode.trim().isNotEmpty) {
      _codigoController.text = scannedCode.trim();
      _buscarComprobante();
    }
  }

  Future<void> _buscarComprobante() async {
    final codigo = _codigoController.text.trim();
    if (codigo.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Por favor, ingresá o escaneá un código de comprobante.')),
      );
      return;
    }

    setState(() {
      _isValidatingCodigo = true;
      _errorValidacion = null;
      _comprobanteValidado = null;
    });

    try {
      final res = await _apiService.adminValidarComprobante(codigo);
      if (!mounted) return;
      setState(() {
        _comprobanteValidado = res;
        _isValidatingCodigo = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _errorValidacion = e.message;
        _isValidatingCodigo = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _errorValidacion = 'Error al consultar el comprobante.';
        _isValidatingCodigo = false;
      });
    }
  }

  Future<void> _confirmarEntrega({String? codigoDirecto}) async {
    final codigo = codigoDirecto ?? _comprobanteValidado?['codigo_comprobante']?.toString() ?? '';
    if (codigo.isEmpty) return;

    setState(() {
      _isConfirmingEntrega = true;
    });

    try {
      final res = await _apiService.adminValidarComprobante(codigo, confirmarEntrega: true);
      if (!mounted) return;

      setState(() {
        _comprobanteValidado = res;
        _isConfirmingEntrega = false;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('¡Entrega confirmada y registrada en el sistema!'),
          backgroundColor: AppColors.success,
        ),
      );

      _cargarMetricas();
      if (_inscripciones.isNotEmpty) _cargarInscripciones();
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _isConfirmingEntrega = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.message), backgroundColor: AppColors.error),
      );
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _isConfirmingEntrega = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Error al procesar la entrega.'), backgroundColor: AppColors.error),
      );
    }
  }

  Future<void> _abrirUrl(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  void _verInscriptosDeEvento(Map<String, dynamic> ev) {
    setState(() {
      _inscripcionesEventoFiltro = ev['id'] as int?;
      _inscripcionesEventoFiltroTitulo = ev['titulo'] as String?;
      _inscripcionesEstadoFiltro = 'todos';
      _currentIndex = 2; // Pasa a la pestaña Inscriptos
    });
    _cargarInscripciones();
  }

  Future<void> _seleccionarFecha(BuildContext context, TextEditingController controller) async {
    DateTime initial = DateTime.now();
    final parts = controller.text.trim().split('/');
    if (parts.length == 3) {
      final d = int.tryParse(parts[0]);
      final m = int.tryParse(parts[1]);
      final y = int.tryParse(parts[2]);
      if (d != null && m != null && y != null) {
        initial = DateTime(y, m, d);
      }
    }

    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(2020),
      lastDate: DateTime(2035),
      helpText: 'Seleccionar Fecha',
      confirmText: 'ACEPTAR',
      cancelText: 'CANCELAR',
    );

    if (picked != null) {
      final dia = picked.day.toString().padLeft(2, '0');
      final mes = picked.month.toString().padLeft(2, '0');
      final anio = picked.year.toString();
      controller.text = '$dia/$mes/$anio';
    }
  }

  // ==========================================
  // ESTRUCTURA PRINCIPAL: NAVEGACIÓN INFERIOR
  // ==========================================

  @override
  Widget build(BuildContext context) {
    final screens = [
      _buildMetricasTab(),
      _buildAfiliadosTab(),
      _buildInscriptosTab(),
      _buildEventosTab(),
    ];

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: PanaderosAppBar(
        actions: [
          TextButton.icon(
            onPressed: widget.onSwitchToAfiliado,
            icon: const Icon(Icons.person, color: AppColors.accent, size: 18),
            label: const Text(
              'Vista Afiliado',
              style: TextStyle(
                fontFamily: 'Manrope',
                fontWeight: FontWeight.bold,
                color: Colors.white,
                fontSize: 12,
              ),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Actualizar datos',
            onPressed: () {
              if (_currentIndex == 0) _cargarMetricas();
              if (_currentIndex == 1) _cargarAfiliados();
              if (_currentIndex == 2) _cargarInscripciones();
              if (_currentIndex == 3) _cargarEventos();
            },
          ),
        ],
      ),
      body: IndexedStack(
        index: _currentIndex,
        children: screens,
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: _onNavigationChanged,
        backgroundColor: Colors.white,
        elevation: 8,
        indicatorColor: AppColors.accent.withValues(alpha: 0.25),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.dashboard_outlined),
            selectedIcon: Icon(Icons.dashboard, color: AppColors.primary),
            label: 'Métricas',
          ),
          NavigationDestination(
            icon: Icon(Icons.people_outline),
            selectedIcon: Icon(Icons.people, color: AppColors.primary),
            label: 'Afiliados',
          ),
          NavigationDestination(
            icon: Icon(Icons.assignment_outlined),
            selectedIcon: Icon(Icons.assignment, color: AppColors.primary),
            label: 'Inscriptos',
          ),
          NavigationDestination(
            icon: Icon(Icons.event_outlined),
            selectedIcon: Icon(Icons.event, color: AppColors.primary),
            label: 'Eventos',
          ),
        ],
      ),
    );
  }

  // ==========================================
  // PESTAÑA 1: MÉTRICAS Y VALIDADOR QR
  // ==========================================

  Widget _buildMetricasTab() {
    return RefreshIndicator(
      onRefresh: _cargarMetricas,
      color: AppColors.accent,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Métricas Resumen
          _buildMetricasGrid(),

          const SizedBox(height: 20),

          // Validador con QR y Cámara
          _buildValidadorRetiroSection(),

          const SizedBox(height: 20),

          // Resumen de Talles
          _buildResumenTallesSection(),

          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildMetricasGrid() {
    if (_isLoadingMetricas) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24.0),
          child: CircularProgressIndicator(color: AppColors.primary),
        ),
      );
    }

    if (_errorMetricas != null) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border),
        ),
        child: Column(
          children: [
            Text(_errorMetricas!, style: const TextStyle(color: AppColors.error, fontSize: 13)),
            const SizedBox(height: 8),
            TextButton.icon(
              onPressed: _cargarMetricas,
              icon: const Icon(Icons.refresh, size: 16),
              label: const Text('Reintentar'),
            ),
          ],
        ),
      );
    }

    final m = _metricas!;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Panel General de Gestión',
          style: TextStyle(
            fontFamily: 'Manrope',
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: AppColors.primary,
          ),
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                label: 'Total Padrón',
                value: '${m['total_padron']}',
                icon: Icons.people_outline,
                color: AppColors.primary,
                onTap: () {
                  _afiliadosFiltro = 'todos';
                  _afiliadosSearchController.clear();
                  _cargarAfiliados();
                  _onNavigationChanged(1);
                },
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildMetricCard(
                label: 'Usuarios App',
                value: '${m['usuarios_app']}',
                icon: Icons.smartphone,
                color: AppColors.accentDark,
                onTap: () {
                  _afiliadosFiltro = 'app';
                  _afiliadosSearchController.clear();
                  _cargarAfiliados();
                  _onNavigationChanged(1);
                },
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildMetricCard(
                label: 'Tasa Adopción',
                value: '${m['tasa_adopcion']}%',
                icon: Icons.trending_up,
                color: AppColors.success,
                onTap: () {
                  _onNavigationChanged(1);
                },
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildMetricCard(
                label: 'Campañas',
                value: '${m['eventos_activos']}',
                icon: Icons.event,
                color: AppColors.primaryLight,
                onTap: () {
                  _cargarEventos();
                  _onNavigationChanged(3);
                },
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildMetricCard(
                label: 'Pendientes',
                value: '${m['pendientes_retiro']}',
                icon: Icons.hourglass_top,
                color: AppColors.accent,
                onTap: () {
                  _inscripcionesEstadoFiltro = 'solicitado';
                  _inscripcionesEventoFiltro = null;
                  _inscripcionesEventoFiltroTitulo = null;
                  _cargarInscripciones();
                  _onNavigationChanged(2);
                },
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildMetricCard(
                label: 'Entregados',
                value: '${m['entregados']}',
                icon: Icons.check_circle_outline,
                color: AppColors.success,
                onTap: () {
                  _inscripcionesEstadoFiltro = 'entregado';
                  _inscripcionesEventoFiltro = null;
                  _inscripcionesEventoFiltroTitulo = null;
                  _cargarInscripciones();
                  _onNavigationChanged(2);
                },
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildMetricCard({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.border),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          children: [
            Icon(icon, size: 20, color: color),
            const SizedBox(height: 6),
            Text(
              value,
              style: TextStyle(
                fontFamily: 'Manrope',
                fontSize: 18,
                fontWeight: FontWeight.w800,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 10.5,
                fontWeight: FontWeight.w600,
                color: Colors.grey.shade600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildValidadorRetiroSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.qr_code_scanner, color: AppColors.primary, size: 22),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Validación de Retiro en Sede Lanús',
                  style: TextStyle(
                    fontFamily: 'Manrope',
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: AppColors.primary,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            'Ingresá o escaneá con la cámara el código del comprobante QR:',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
          ),
          const SizedBox(height: 12),

          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _codigoController,
                  textCapitalization: TextCapitalization.characters,
                  decoration: InputDecoration(
                    hintText: 'Ej: UTILES-2027-A1B2C',
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                    suffixIcon: IconButton(
                      icon: const Icon(Icons.camera_alt, color: AppColors.primary),
                      tooltip: 'Escanear QR con la cámara',
                      onPressed: _escanearQrConCamara,
                    ),
                  ),
                  onSubmitted: (_) => _buscarComprobante(),
                ),
              ),
              const SizedBox(width: 8),
              ElevatedButton.icon(
                onPressed: _isValidatingCodigo ? null : _buscarComprobante,
                icon: const Icon(Icons.search, size: 16),
                label: _isValidatingCodigo
                    ? const SizedBox(
                        height: 18,
                        width: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text('Validar'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ],
          ),

          if (_errorValidacion != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.error.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.error.withValues(alpha: 0.3)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.error_outline, size: 18, color: AppColors.error),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _errorValidacion!,
                      style: const TextStyle(fontSize: 12, color: AppColors.error),
                    ),
                  ),
                ],
              ),
            ),
          ],

          if (_comprobanteValidado != null) ...[
            const SizedBox(height: 16),
            _buildComprobanteResultado(),
          ],
        ],
      ),
    );
  }

  Widget _buildComprobanteResultado() {
    final c = _comprobanteValidado!;
    final yaEntregado = c['ya_entregado'] == true;
    final beneficiarios = (c['beneficiarios'] as List<dynamic>?) ?? [];

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: yaEntregado
            ? AppColors.error.withValues(alpha: 0.05)
            : AppColors.success.withValues(alpha: 0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: yaEntregado ? AppColors.error : AppColors.success,
          width: 1.5,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                yaEntregado ? Icons.cancel : Icons.check_circle,
                color: yaEntregado ? AppColors.error : AppColors.success,
                size: 20,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  yaEntregado ? 'COMPROBANTE YA ENTREGADO' : 'COMPROBANTE VÁLIDO · PENDIENTE',
                  style: TextStyle(
                    fontFamily: 'Manrope',
                    fontWeight: FontWeight.bold,
                    fontSize: 13,
                    color: yaEntregado ? AppColors.error : AppColors.success,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 10),

          Text(
            'Titular: ${c['afiliado_titular']}',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
          ),
          Text(
            'DNI: ${c['afiliado_dni']} · N° Afiliado: ${c['numero_afiliado']}',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
          ),
          const SizedBox(height: 4),
          Text(
            'Campaña: ${c['evento_titulo']}',
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.accentDark),
          ),

          const SizedBox(height: 12),
          const Text(
            'Kits / Beneficiarios a entregar:',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
          ),
          const SizedBox(height: 4),

          ...beneficiarios.map((b) {
            final talle = b['talle']?.toString();
            final nivel = b['nivel_escolar']?.toString();
            return Padding(
              padding: const EdgeInsets.only(bottom: 4),
              child: Row(
                children: [
                  const Icon(Icons.arrow_right, size: 16, color: AppColors.accent),
                  Text(
                    '${b['nombre']} ${b['apellido'] ?? ""}'.trim(),
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                  ),
                  const Spacer(),
                  if (talle != null && talle.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text('Talle: $talle', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    ),
                  if (nivel != null && nivel.isNotEmpty) ...[
                    const SizedBox(width: 6),
                    Text(nivel.toUpperCase(), style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                  ],
                ],
              ),
            );
          }),

          const SizedBox(height: 14),

          if (!yaEntregado)
            SizedBox(
              width: double.infinity,
              height: 44,
              child: ElevatedButton.icon(
                onPressed: _isConfirmingEntrega ? null : () => _confirmarEntrega(),
                icon: const Icon(Icons.check, size: 18),
                label: _isConfirmingEntrega
                    ? const SizedBox(
                        height: 18,
                        width: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text('Confirmar Entrega de Kits', style: TextStyle(fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.success,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildResumenTallesSection() {
    final resumen = (_metricas?['talles_resumen'] as List<dynamic>?) ?? [];
    if (resumen.isEmpty) return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.checkroom, color: AppColors.primary, size: 20),
              SizedBox(width: 8),
              Text(
                'Demanda de Guardapolvos por Talle',
                style: TextStyle(
                  fontFamily: 'Manrope',
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: AppColors.primary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            'Control de inventario para compras mayoristas a proveedores:',
            style: TextStyle(fontSize: 11.5, color: Colors.grey.shade600),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: resumen.map((t) {
              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppColors.border),
                ),
                child: Text(
                  'Talle ${t['talle']}: ${t['cantidad']} u.',
                  style: const TextStyle(
                    fontFamily: 'Manrope',
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.primary,
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // PESTAÑA 2: LISTADO DETALLADO DE AFILIADOS
  // ==========================================

  Widget _buildAfiliadosTab() {
    return Column(
      children: [
        Container(
          padding: const EdgeInsets.all(12),
          color: Colors.white,
          child: Column(
            children: [
              TextField(
                controller: _afiliadosSearchController,
                decoration: InputDecoration(
                  hintText: 'Buscar por DNI, N° Afiliado, Nombre o Panadería...',
                  prefixIcon: const Icon(Icons.search, size: 20),
                  suffixIcon: _afiliadosSearchController.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear, size: 18),
                          onPressed: () {
                            _afiliadosSearchController.clear();
                            _cargarAfiliados();
                          },
                        )
                      : null,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onSubmitted: (_) => _cargarAfiliados(),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  _buildFiltroChip('Todos', 'todos', _afiliadosFiltro, (val) {
                    setState(() => _afiliadosFiltro = val);
                    _cargarAfiliados();
                  }),
                  const SizedBox(width: 8),
                  _buildFiltroChip('Solo en App', 'app', _afiliadosFiltro, (val) {
                    setState(() => _afiliadosFiltro = val);
                    _cargarAfiliados();
                  }),
                  const SizedBox(width: 8),
                  _buildFiltroChip('Solo Padrón', 'padron', _afiliadosFiltro, (val) {
                    setState(() => _afiliadosFiltro = val);
                    _cargarAfiliados();
                  }),
                  const Spacer(),
                  Text(
                    '${_afiliados.length} afiliados',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey.shade700),
                  ),
                ],
              ),
            ],
          ),
        ),

        Expanded(
          child: RefreshIndicator(
            onRefresh: _cargarAfiliados,
            color: AppColors.accent,
            child: _isLoadingAfiliados
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : _errorAfiliados != null
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(_errorAfiliados!, style: const TextStyle(color: AppColors.error)),
                            const SizedBox(height: 8),
                            ElevatedButton(onPressed: _cargarAfiliados, child: const Text('Reintentar')),
                          ],
                        ),
                      )
                    : _afiliados.isEmpty
                        ? const Center(
                            child: Text(
                              'No se encontraron afiliados con ese criterio.',
                              style: TextStyle(color: Colors.grey),
                            ),
                          )
                        : ListView.separated(
                            padding: const EdgeInsets.all(12),
                            itemCount: _afiliados.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 10),
                            itemBuilder: (context, index) {
                              final a = _afiliados[index];
                              final enApp = a['registrado_en_app'] == true;
                              return Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: AppColors.border),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        CircleAvatar(
                                          backgroundColor: enApp
                                              ? AppColors.primary.withValues(alpha: 0.12)
                                              : Colors.grey.shade200,
                                          foregroundColor: enApp ? AppColors.primary : Colors.grey.shade700,
                                          child: Text(
                                            (a['nombre'] as String? ?? 'A').substring(0, 1).toUpperCase(),
                                            style: const TextStyle(fontWeight: FontWeight.bold),
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                a['nombre_completo'] ?? '',
                                                style: const TextStyle(
                                                  fontFamily: 'Manrope',
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 14,
                                                ),
                                              ),
                                              const SizedBox(height: 2),
                                              Text(
                                                'DNI: ${a['dni']} · Afiliado N°: ${a['numero_afiliado']}',
                                                style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                                              ),
                                              if (a['empresa_panaderia'] != null && a['empresa_panaderia'].toString().isNotEmpty)
                                                Text(
                                                  '${a['empresa_panaderia']} · ${a['categoria_laboral'] ?? ""}',
                                                  style: const TextStyle(fontSize: 11, color: AppColors.accentDark, fontWeight: FontWeight.w600),
                                                ),
                                            ],
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: enApp
                                                ? AppColors.success.withValues(alpha: 0.12)
                                                : Colors.grey.shade200,
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: Text(
                                            enApp ? 'APP ACTIVA' : 'SÓLO PADRÓN',
                                            style: TextStyle(
                                              fontSize: 9.5,
                                              fontWeight: FontWeight.bold,
                                              color: enApp ? AppColors.success : Colors.grey.shade700,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    const Divider(height: 1),
                                    const SizedBox(height: 8),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          'Hijos a cargo: ${a['total_familiares'] ?? 0}',
                                          style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
                                        ),
                                        Row(
                                          children: [
                                            if (a['telefono'] != null && a['telefono'].toString().isNotEmpty)
                                              InkWell(
                                                onTap: () => _abrirUrl('tel:${a['telefono']}'),
                                                child: Padding(
                                                  padding: const EdgeInsets.symmetric(horizontal: 6),
                                                  child: Row(
                                                    children: [
                                                      const Icon(Icons.phone, size: 14, color: AppColors.success),
                                                      const SizedBox(width: 4),
                                                      Text(
                                                        '${a['telefono']}',
                                                        style: const TextStyle(fontSize: 11, color: AppColors.success, fontWeight: FontWeight.w600),
                                                      ),
                                                    ],
                                                  ),
                                                ),
                                              ),
                                            if (a['email'] != null && a['email'].toString().isNotEmpty)
                                              InkWell(
                                                onTap: () => _abrirUrl('mailto:${a['email']}'),
                                                child: Padding(
                                                  padding: const EdgeInsets.symmetric(horizontal: 6),
                                                  child: Row(
                                                    children: [
                                                      const Icon(Icons.email_outlined, size: 14, color: AppColors.primary),
                                                      const SizedBox(width: 4),
                                                      Text(
                                                        '${a['email']}',
                                                        style: const TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.w600),
                                                      ),
                                                    ],
                                                  ),
                                                ),
                                              ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
          ),
        ),
      ],
    );
  }

  // ==========================================
  // PESTAÑA 3: LISTADO DETALLADO DE INSCRIPTOS
  // ==========================================

  Widget _buildInscriptosTab() {
    return Column(
      children: [
        // Banner si viene filtrado por evento
        if (_inscripcionesEventoFiltro != null)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            color: AppColors.primary.withValues(alpha: 0.08),
            child: Row(
              children: [
                const Icon(Icons.filter_list, size: 18, color: AppColors.primary),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Mostrando inscriptos de: ${_inscripcionesEventoFiltroTitulo ?? "Campaña"}',
                    style: const TextStyle(
                      fontFamily: 'Manrope',
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: AppColors.primary,
                    ),
                  ),
                ),
                TextButton(
                  onPressed: () {
                    setState(() {
                      _inscripcionesEventoFiltro = null;
                      _inscripcionesEventoFiltroTitulo = null;
                    });
                    _cargarInscripciones();
                  },
                  child: const Text('Ver Todos', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),

        // Filtros y Buscador
        Container(
          padding: const EdgeInsets.all(12),
          color: Colors.white,
          child: Column(
            children: [
              TextField(
                controller: _inscripcionesSearchController,
                decoration: InputDecoration(
                  hintText: 'Buscar por comprobante, titular, DNI...',
                  prefixIcon: const Icon(Icons.search, size: 20),
                  suffixIcon: _inscripcionesSearchController.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear, size: 18),
                          onPressed: () {
                            _inscripcionesSearchController.clear();
                            _cargarInscripciones();
                          },
                        )
                      : null,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onSubmitted: (_) => _cargarInscripciones(),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  _buildFiltroChip('Todos', 'todos', _inscripcionesEstadoFiltro, (val) {
                    setState(() => _inscripcionesEstadoFiltro = val);
                    _cargarInscripciones();
                  }),
                  const SizedBox(width: 8),
                  _buildFiltroChip('Pendientes', 'solicitado', _inscripcionesEstadoFiltro, (val) {
                    setState(() => _inscripcionesEstadoFiltro = val);
                    _cargarInscripciones();
                  }),
                  const SizedBox(width: 8),
                  _buildFiltroChip('Entregados', 'entregado', _inscripcionesEstadoFiltro, (val) {
                    setState(() => _inscripcionesEstadoFiltro = val);
                    _cargarInscripciones();
                  }),
                  const Spacer(),
                  Text(
                    '${_inscripciones.length} registros',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey.shade700),
                  ),
                ],
              ),
            ],
          ),
        ),

        // Listado de Solicitudes
        Expanded(
          child: RefreshIndicator(
            onRefresh: _cargarInscripciones,
            color: AppColors.accent,
            child: _isLoadingInscripciones
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : _errorInscripciones != null
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(_errorInscripciones!, style: const TextStyle(color: AppColors.error)),
                            const SizedBox(height: 8),
                            ElevatedButton(onPressed: _cargarInscripciones, child: const Text('Reintentar')),
                          ],
                        ),
                      )
                    : _inscripciones.isEmpty
                        ? const Center(
                            child: Text(
                              'No hay inscripciones registradas para este filtro.',
                              style: TextStyle(color: Colors.grey),
                            ),
                          )
                        : ListView.separated(
                            padding: const EdgeInsets.all(12),
                            itemCount: _inscripciones.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 12),
                            itemBuilder: (context, index) {
                              final item = _inscripciones[index];
                              final entregado = item['estado'] == 'entregado';
                              final beneficiarios = (item['beneficiarios'] as List<dynamic>?) ?? [];

                              return Container(
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: entregado ? AppColors.success.withValues(alpha: 0.4) : AppColors.border,
                                    width: entregado ? 1.5 : 1.0,
                                  ),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: AppColors.primary.withValues(alpha: 0.08),
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: Text(
                                            item['codigo_comprobante'] ?? '',
                                            style: const TextStyle(
                                              fontFamily: 'Manrope',
                                              fontWeight: FontWeight.bold,
                                              fontSize: 12,
                                              color: AppColors.primary,
                                            ),
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: entregado
                                                ? AppColors.success.withValues(alpha: 0.12)
                                                : AppColors.accent.withValues(alpha: 0.2),
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: Text(
                                            entregado ? 'ENTREGADO' : 'PENDIENTE',
                                            style: TextStyle(
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              color: entregado ? AppColors.success : AppColors.primary,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),

                                    Text(
                                      item['afiliado_nombre_completo'] ?? '',
                                      style: const TextStyle(
                                        fontFamily: 'Manrope',
                                        fontSize: 14,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    Text(
                                      'DNI: ${item['afiliado_dni']} · N° Afiliado: ${item['numero_afiliado']}',
                                      style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                                    ),
                                    Text(
                                      'Evento: ${item['evento_titulo']}',
                                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.accentDark),
                                    ),

                                    const SizedBox(height: 8),
                                    const Divider(height: 1),
                                    const SizedBox(height: 8),

                                    Text(
                                      'Beneficiarios (${beneficiarios.length}):',
                                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                                    ),
                                    const SizedBox(height: 4),

                                    ...beneficiarios.map((b) {
                                      final talle = b['talle']?.toString();
                                      final nivel = b['nivel_escolar']?.toString();
                                      return Padding(
                                        padding: const EdgeInsets.symmetric(vertical: 2),
                                        child: Row(
                                          children: [
                                            const Icon(Icons.arrow_right, size: 16, color: AppColors.accent),
                                            Text(
                                              '${b['nombre_completo']} (${b['edad']})',
                                              style: const TextStyle(fontSize: 12),
                                            ),
                                            const Spacer(),
                                            if (talle != null && talle.isNotEmpty)
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: AppColors.primary.withValues(alpha: 0.08),
                                                  borderRadius: BorderRadius.circular(4),
                                                ),
                                                child: Text('Talle: $talle', style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold)),
                                              ),
                                            if (nivel != null && nivel.isNotEmpty) ...[
                                              const SizedBox(width: 4),
                                              Text(nivel, style: TextStyle(fontSize: 10.5, color: Colors.grey.shade600)),
                                            ],
                                          ],
                                        ),
                                      );
                                    }),

                                    if (!entregado) ...[
                                      const SizedBox(height: 10),
                                      SizedBox(
                                        width: double.infinity,
                                        height: 38,
                                        child: ElevatedButton.icon(
                                          onPressed: () => _confirmarEntrega(codigoDirecto: item['codigo_comprobante']),
                                          icon: const Icon(Icons.check, size: 16),
                                          label: const Text('Marcar como Entregado', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: AppColors.success,
                                            foregroundColor: Colors.white,
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              );
                            },
                          ),
          ),
        ),
      ],
    );
  }

  // ==========================================
  // PESTAÑA 4: GESTIÓN Y EDICIÓN DE EVENTOS
  // ==========================================

  Widget _buildEventosTab() {
    return Scaffold(
      backgroundColor: Colors.transparent,
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _mostrarDialogoEditarEvento(),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Nuevo Evento', style: TextStyle(fontFamily: 'Manrope', fontWeight: FontWeight.bold)),
      ),
      body: RefreshIndicator(
        onRefresh: _cargarEventos,
        color: AppColors.accent,
        child: _isLoadingEventos
            ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
            : _errorEventos != null
                ? Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(_errorEventos!, style: const TextStyle(color: AppColors.error)),
                        const SizedBox(height: 8),
                        ElevatedButton(onPressed: _cargarEventos, child: const Text('Reintentar')),
                      ],
                    ),
                  )
                : _eventos.isEmpty
                    ? const Center(child: Text('No hay eventos creados.'))
                    : ListView.separated(
                        padding: const EdgeInsets.fromLTRB(14, 14, 14, 80),
                        itemCount: _eventos.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 12),
                        itemBuilder: (context, index) {
                          final ev = _eventos[index];
                          final estado = ev['estado']?.toString() ?? 'borrador';
                          final isOpen = estado == 'abierto';

                          return Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: AppColors.border),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: isOpen
                                            ? AppColors.success.withValues(alpha: 0.12)
                                            : Colors.grey.shade200,
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        estado.toUpperCase(),
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.bold,
                                          color: isOpen ? AppColors.success : Colors.grey.shade700,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Text(
                                      'Tipo: ${ev['tipo']?.toString().toUpperCase()}',
                                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.grey.shade600),
                                    ),
                                    const Spacer(),
                                    IconButton(
                                      icon: const Icon(Icons.edit, size: 20, color: AppColors.primary),
                                      tooltip: 'Editar evento',
                                      onPressed: () => _mostrarDialogoEditarEvento(ev),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  ev['titulo'] ?? '',
                                  style: const TextStyle(
                                    fontFamily: 'Manrope',
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.primary,
                                  ),
                                ),
                                if (ev['descripcion'] != null && ev['descripcion'].toString().isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    ev['descripcion'],
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                                  ),
                                ],
                                const SizedBox(height: 10),
                                Row(
                                  children: [
                                    const Icon(Icons.calendar_today, size: 14, color: AppColors.accentDark),
                                    const SizedBox(width: 6),
                                    Text(
                                      'Inscripción: ${formatFecha(ev['fecha_inicio_inscripcion'])} al ${formatFecha(ev['fecha_fin_inscripcion'])}',
                                      style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w500),
                                    ),
                                  ],
                                ),
                                if (ev['fecha_entrega'] != null && ev['fecha_entrega'].toString().isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Row(
                                    children: [
                                      const Icon(Icons.local_shipping_outlined, size: 14, color: AppColors.success),
                                      const SizedBox(width: 6),
                                      Text(
                                        'Entrega estimada: ${formatFecha(ev['fecha_entrega'])}',
                                        style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w500),
                                      ),
                                    ],
                                  ),
                                ],
                                const SizedBox(height: 12),
                                const Divider(height: 1),
                                const SizedBox(height: 10),

                                // Métricas del evento
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                                  children: [
                                    _buildMiniStat('Inscriptos', '${ev['total_inscriptos'] ?? 0}'),
                                    _buildMiniStat('Pendientes', '${ev['total_pendientes'] ?? 0}', color: AppColors.accentDark),
                                    _buildMiniStat('Entregados', '${ev['total_entregados'] ?? 0}', color: AppColors.success),
                                    _buildMiniStat('Kits / Hijos', '${ev['total_beneficiarios'] ?? 0}'),
                                  ],
                                ),
                                const SizedBox(height: 12),

                                // Botón para ver inscriptos a este evento
                                SizedBox(
                                  width: double.infinity,
                                  height: 38,
                                  child: OutlinedButton.icon(
                                    onPressed: () => _verInscriptosDeEvento(ev),
                                    icon: const Icon(Icons.people_alt_outlined, size: 16),
                                    label: Text(
                                      'Ver Inscriptos (${ev['total_inscriptos'] ?? 0})',
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                    ),
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: AppColors.primary,
                                      side: const BorderSide(color: AppColors.primary),
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
      ),
    );
  }

  Widget _buildMiniStat(String label, String value, {Color color = AppColors.primary}) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(fontFamily: 'Manrope', fontSize: 16, fontWeight: FontWeight.bold, color: color),
        ),
        Text(
          label,
          style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
        ),
      ],
    );
  }

  // ==========================================
  // DIÁLOGO DE EDICIÓN / CREACIÓN DE EVENTO
  // ==========================================

  void _mostrarDialogoEditarEvento([Map<String, dynamic>? eventoExistente]) {
    final bool esEdicion = eventoExistente != null;
    final tituloCtrl = TextEditingController(text: eventoExistente?['titulo'] ?? '');
    final descCtrl = TextEditingController(text: eventoExistente?['descripcion'] ?? '');

    // Formato estricto argentino dd/mm/aaaa
    final fechaInicioCtrl = TextEditingController(
      text: formatFecha(eventoExistente?['fecha_inicio_inscripcion']),
    );
    final fechaFinCtrl = TextEditingController(
      text: formatFecha(eventoExistente?['fecha_fin_inscripcion']),
    );
    final fechaEntregaCtrl = TextEditingController(
      text: formatFecha(eventoExistente?['fecha_entrega']),
    );

    final edadMinCtrl = TextEditingController(text: '${eventoExistente?['edad_minima_hijo'] ?? 3}');
    final edadMaxCtrl = TextEditingController(text: '${eventoExistente?['edad_maxima_hijo'] ?? 17}');

    String tipo = eventoExistente?['tipo'] ?? 'utiles';
    String estado = eventoExistente?['estado'] ?? 'abierto';
    bool requiereTalle = eventoExistente?['requiere_talle'] == true || eventoExistente?['requiere_talle'] == 1;
    bool requiereEscolaridad = eventoExistente?['requiere_escolaridad'] == true || eventoExistente?['requiere_escolaridad'] == 1;
    bool isSaving = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                top: 20,
                left: 16,
                right: 16,
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          esEdicion ? 'Editar Evento' : 'Crear Nuevo Evento',
                          style: const TextStyle(
                            fontFamily: 'Manrope',
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: AppColors.primary,
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.of(ctx).pop(),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Título
                    TextField(
                      controller: tituloCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Título de la campaña *',
                        border: OutlineInputBorder(),
                        contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Descripción
                    TextField(
                      controller: descCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(
                        labelText: 'Descripción / Requisitos de la campaña',
                        border: OutlineInputBorder(),
                        contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Tipo y Estado
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            initialValue: tipo,
                            decoration: const InputDecoration(
                              labelText: 'Tipo',
                              border: OutlineInputBorder(),
                              contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                            ),
                            items: const [
                              DropdownMenuItem(value: 'utiles', child: Text('Útiles Escolares')),
                              DropdownMenuItem(value: 'juguetes', child: Text('Juguetes / Niño')),
                              DropdownMenuItem(value: 'turismo', child: Text('Turismo')),
                              DropdownMenuItem(value: 'general', child: Text('General')),
                            ],
                            onChanged: (val) {
                              if (val != null) setModalState(() => tipo = val);
                            },
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            initialValue: estado,
                            decoration: const InputDecoration(
                              labelText: 'Estado',
                              border: OutlineInputBorder(),
                              contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                            ),
                            items: const [
                              DropdownMenuItem(value: 'borrador', child: Text('Borrador')),
                              DropdownMenuItem(value: 'abierto', child: Text('Abierto')),
                              DropdownMenuItem(value: 'cerrado', child: Text('Cerrado')),
                              DropdownMenuItem(value: 'finalizado', child: Text('Finalizado')),
                            ],
                            onChanged: (val) {
                              if (val != null) setModalState(() => estado = val);
                            },
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Fechas en formato argentino dd/mm/aaaa con selector de calendario
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: fechaInicioCtrl,
                            keyboardType: TextInputType.datetime,
                            decoration: InputDecoration(
                              labelText: 'Inicio (dd/mm/aaaa) *',
                              hintText: '01/02/2027',
                              border: const OutlineInputBorder(),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                              suffixIcon: IconButton(
                                icon: const Icon(Icons.calendar_today, size: 16, color: AppColors.primary),
                                tooltip: 'Seleccionar fecha',
                                onPressed: () => _seleccionarFecha(context, fechaInicioCtrl),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: fechaFinCtrl,
                            keyboardType: TextInputType.datetime,
                            decoration: InputDecoration(
                              labelText: 'Fin (dd/mm/aaaa) *',
                              hintText: '05/03/2027',
                              border: const OutlineInputBorder(),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                              suffixIcon: IconButton(
                                icon: const Icon(Icons.calendar_today, size: 16, color: AppColors.primary),
                                tooltip: 'Seleccionar fecha',
                                onPressed: () => _seleccionarFecha(context, fechaFinCtrl),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: fechaEntregaCtrl,
                      keyboardType: TextInputType.datetime,
                      decoration: InputDecoration(
                        labelText: 'Fecha Estimada de Entrega (dd/mm/aaaa)',
                        hintText: '10/03/2027',
                        border: const OutlineInputBorder(),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                        suffixIcon: IconButton(
                          icon: const Icon(Icons.calendar_today, size: 16, color: AppColors.primary),
                          tooltip: 'Seleccionar fecha',
                          onPressed: () => _seleccionarFecha(context, fechaEntregaCtrl),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Edades y Requisitos
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: edadMinCtrl,
                            keyboardType: TextInputType.number,
                            decoration: const InputDecoration(
                              labelText: 'Edad mínima hijo',
                              border: OutlineInputBorder(),
                              contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: edadMaxCtrl,
                            keyboardType: TextInputType.number,
                            decoration: const InputDecoration(
                              labelText: 'Edad máxima hijo',
                              border: OutlineInputBorder(),
                              contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),

                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('Requiere Talle de Guardapolvo', style: TextStyle(fontSize: 13)),
                      value: requiereTalle,
                      onChanged: (val) => setModalState(() => requiereTalle = val),
                    ),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('Requiere Certificado de Escolaridad', style: TextStyle(fontSize: 13)),
                      value: requiereEscolaridad,
                      onChanged: (val) => setModalState(() => requiereEscolaridad = val),
                    ),
                    const SizedBox(height: 16),

                    // Botón Guardar
                    SizedBox(
                      width: double.infinity,
                      height: 46,
                      child: ElevatedButton(
                        onPressed: isSaving
                            ? null
                            : () async {
                                final titulo = tituloCtrl.text.trim();
                                final fechaIni = fechaInicioCtrl.text.trim();
                                final fechaFin = fechaFinCtrl.text.trim();

                                if (titulo.isEmpty || fechaIni.isEmpty || fechaFin.isEmpty) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('Completá los campos obligatorios (*).')),
                                  );
                                  return;
                                }

                                final messenger = ScaffoldMessenger.of(context);
                                final navigator = Navigator.of(ctx);

                                setModalState(() => isSaving = true);

                                try {
                                  // Se envían las fechas convertidas o validadas
                                  await _apiService.guardarAdminEvento({
                                    if (esEdicion) 'id': eventoExistente['id'],
                                    'titulo': titulo,
                                    'descripcion': descCtrl.text.trim(),
                                    'tipo': tipo,
                                    'fecha_inicio_inscripcion': parseFechaToIso(fechaIni),
                                    'fecha_fin_inscripcion': parseFechaToIso(fechaFin),
                                    'fecha_entrega': fechaEntregaCtrl.text.trim().isEmpty ? null : parseFechaToIso(fechaEntregaCtrl.text.trim()),
                                    'edad_minima_hijo': int.tryParse(edadMinCtrl.text) ?? 0,
                                    'edad_maxima_hijo': int.tryParse(edadMaxCtrl.text) ?? 18,
                                    'requiere_talle': requiereTalle ? 1 : 0,
                                    'requiere_escolaridad': requiereEscolaridad ? 1 : 0,
                                    'estado': estado,
                                  });

                                  navigator.pop();
                                  messenger.showSnackBar(
                                    SnackBar(
                                      content: Text(esEdicion ? '¡Evento actualizado con éxito!' : '¡Evento creado con éxito!'),
                                      backgroundColor: AppColors.success,
                                    ),
                                  );
                                  _cargarEventos();
                                  _cargarMetricas();
                                } on ApiException catch (e) {
                                  setModalState(() => isSaving = false);
                                  messenger.showSnackBar(
                                    SnackBar(content: Text(e.message), backgroundColor: AppColors.error),
                                  );
                                } catch (_) {
                                  setModalState(() => isSaving = false);
                                  messenger.showSnackBar(
                                    const SnackBar(content: Text('Error al guardar el evento.'), backgroundColor: AppColors.error),
                                  );
                                }
                              },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        child: isSaving
                            ? const CircularProgressIndicator(color: Colors.white, strokeWidth: 2)
                            : Text(
                                esEdicion ? 'Guardar Cambios' : 'Crear Evento',
                                style: const TextStyle(fontWeight: FontWeight.bold),
                              ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildFiltroChip(String label, String value, String currentValue, Function(String) onSelected) {
    final isSelected = value == currentValue;
    return InkWell(
      onTap: () => onSelected(value),
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : Colors.grey.shade100,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: isSelected ? AppColors.primary : AppColors.border),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11.5,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
            color: isSelected ? Colors.white : AppColors.textMain,
          ),
        ),
      ),
    );
  }
}
