import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/constants.dart';
import '../models/beneficio.dart';
import '../services/api_service.dart';

class BeneficiosScreen extends StatefulWidget {
  const BeneficiosScreen({super.key});

  @override
  State<BeneficiosScreen> createState() => _BeneficiosScreenState();
}

class _BeneficiosScreenState extends State<BeneficiosScreen> {
  final _apiService = ApiService();
  final _searchController = TextEditingController();

  List<Beneficio> _beneficios = [];
  bool _isLoading = true;
  String? _error;
  String _selectedRubro = 'todos';

  final List<Map<String, String>> _rubros = [
    {'id': 'todos', 'label': 'Todos', 'icon': 'apps'},
    {'id': 'farmacia', 'label': 'Farmacias', 'icon': 'medication'},
    {'id': 'optica', 'label': 'Ópticas', 'icon': 'visibility'},
    {'id': 'salud', 'label': 'Salud', 'icon': 'local_hospital'},
    {'id': 'subsidios', 'label': 'Subsidios', 'icon': 'card_giftcard'},
    {'id': 'turismo', 'label': 'Turismo', 'icon': 'beach_access'},
  ];

  @override
  void initState() {
    super.initState();
    _cargarBeneficios();
  }

  Future<void> _cargarBeneficios() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final list = await _apiService.getBeneficios(
        rubro: _selectedRubro,
        query: _searchController.text.trim(),
      );
      if (!mounted) return;
      setState(() {
        _beneficios = list;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _launchUrl(Uri uri) async {
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('No se pudo abrir la aplicación.')),
        );
      }
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Error al intentar abrir el enlace.')),
      );
    }
  }

  Color _getRubroColor(String rubro) {
    switch (rubro.toLowerCase()) {
      case 'farmacia':
        return const Color(0xFF107C41);
      case 'optica':
        return const Color(0xFF0078D4);
      case 'salud':
        return const Color(0xFFD83B01);
      case 'subsidios':
        return const Color(0xFFB48200);
      case 'turismo':
        return const Color(0xFF5C2D91);
      default:
        return AppColors.primary;
    }
  }

  IconData _getRubroIcon(String rubro) {
    switch (rubro.toLowerCase()) {
      case 'farmacia':
        return Icons.local_pharmacy_outlined;
      case 'optica':
        return Icons.visibility_outlined;
      case 'salud':
        return Icons.medical_services_outlined;
      case 'subsidios':
        return Icons.card_giftcard_outlined;
      case 'turismo':
        return Icons.beach_access_outlined;
      default:
        return Icons.star_outline;
    }
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Beneficios y Convenios'),
      ),
      body: Column(
        children: [
          // Barra de Búsqueda
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: TextField(
              controller: _searchController,
              onSubmitted: (_) => _cargarBeneficios(),
              textInputAction: TextInputAction.search,
              decoration: InputDecoration(
                hintText: 'Buscar farmacia, óptica o servicio...',
                prefixIcon: const Icon(Icons.search, color: AppColors.textMuted),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 20),
                        onPressed: () {
                          _searchController.clear();
                          _cargarBeneficios();
                        },
                      )
                    : null,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
          ),

          // Filtros Horizontales por Rubro
          Container(
            color: Colors.white,
            height: 48,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: _rubros.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (ctx, idx) {
                final r = _rubros[idx];
                final isSelected = _selectedRubro == r['id'];
                return ChoiceChip(
                  label: Text(r['label']!),
                  selected: isSelected,
                  selectedColor: AppColors.primary,
                  backgroundColor: AppColors.background,
                  labelStyle: TextStyle(
                    color: isSelected ? Colors.white : AppColors.textMain,
                    fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                    fontSize: 13,
                  ),
                  onSelected: (selected) {
                    if (selected) {
                      setState(() {
                        _selectedRubro = r['id']!;
                      });
                      _cargarBeneficios();
                    }
                  },
                );
              },
            ),
          ),
          const Divider(height: 1, color: AppColors.border),

          // Listado de Beneficios
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : _error != null
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24.0),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.error_outline, size: 48, color: AppColors.error),
                              const SizedBox(height: 12),
                              Text(
                                _error!,
                                textAlign: TextAlign.center,
                                style: const TextStyle(fontSize: 14, color: AppColors.textMain),
                              ),
                              const SizedBox(height: 16),
                              ElevatedButton(
                                onPressed: _cargarBeneficios,
                                child: const Text('Reintentar'),
                              ),
                            ],
                          ),
                        ),
                      )
                    : _beneficios.isEmpty
                        ? const Center(
                            child: Padding(
                              padding: EdgeInsets.all(32.0),
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.search_off, size: 54, color: AppColors.textMuted),
                                  SizedBox(height: 12),
                                  Text(
                                    'No se encontraron beneficios',
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 16,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                  SizedBox(height: 6),
                                  Text(
                                    'Probá seleccionando otro rubro o cambiando el término de búsqueda.',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(fontSize: 13, color: AppColors.textMuted),
                                  ),
                                ],
                              ),
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _cargarBeneficios,
                            color: AppColors.primary,
                            child: ListView.separated(
                              padding: const EdgeInsets.all(16),
                              itemCount: _beneficios.length,
                              separatorBuilder: (_, __) => const SizedBox(height: 14),
                              itemBuilder: (ctx, idx) {
                                return _buildBeneficioCard(_beneficios[idx]);
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildBeneficioCard(Beneficio b) {
    final rubroColor = _getRubroColor(b.rubro);
    final rubroIcon = _getRubroIcon(b.rubro);

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header: Rubro Tag + Descuento Badge
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: rubroColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(rubroIcon, size: 14, color: rubroColor),
                    const SizedBox(width: 5),
                    Text(
                      b.rubro.toUpperCase(),
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: rubroColor,
                      ),
                    ),
                  ],
                ),
              ),
              if (b.descuentoDetalle.isNotEmpty)
                Flexible(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppColors.accent.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      b.descuentoDetalle,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: AppColors.primary,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),

          // Título
          Text(
            b.nombre,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppColors.primary,
            ),
          ),

          // Descripción
          if (b.descripcion != null && b.descripcion!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              b.descripcion!,
              style: const TextStyle(
                fontSize: 13,
                color: AppColors.textMuted,
                height: 1.35,
              ),
            ),
          ],

          // Dirección
          if (b.direccion != null && b.direccion!.isNotEmpty) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(Icons.place_outlined, size: 16, color: AppColors.textMuted),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    b.direccion!,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                      color: AppColors.textMain,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
          ],

          const SizedBox(height: 14),
          const Divider(height: 1, color: AppColors.border),
          const SizedBox(height: 10),

          // Botones de Acción Directa
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              if (b.telefono != null && b.telefono!.isNotEmpty)
                TextButton.icon(
                  onPressed: () {
                    final cleanPhone = b.telefono!.replaceAll(RegExp(r'[^0-9+]'), '');
                    _launchUrl(Uri.parse('tel:$cleanPhone'));
                  },
                  icon: const Icon(Icons.phone, size: 16),
                  label: const Text('Llamar', style: TextStyle(fontSize: 12)),
                  style: TextButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  ),
                ),
              if (b.whatsapp != null && b.whatsapp!.isNotEmpty)
                TextButton.icon(
                  onPressed: () {
                    final cleanWa = b.whatsapp!.replaceAll(RegExp(r'[^0-9]'), '');
                    _launchUrl(Uri.parse('https://wa.me/$cleanWa'));
                  },
                  icon: const Icon(Icons.chat_bubble_outline, size: 16, color: Color(0xFF25D366)),
                  label: const Text('WhatsApp', style: TextStyle(fontSize: 12, color: Color(0xFF1E8E3E))),
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  ),
                ),
              if ((b.direccion != null && b.direccion!.isNotEmpty) ||
                  (b.coordenadas != null && b.coordenadas!.isNotEmpty))
                TextButton.icon(
                  onPressed: () {
                    final query = (b.coordenadas != null && b.coordenadas!.isNotEmpty)
                        ? b.coordenadas!
                        : '${b.direccion!}, ${b.localidad ?? "Lanús"}';
                    _launchUrl(Uri.parse('https://www.google.com/maps/search/?api=1&query=${Uri.encodeComponent(query)}'));
                  },
                  icon: const Icon(Icons.map_outlined, size: 16),
                  label: const Text('Mapa', style: TextStyle(fontSize: 12)),
                  style: TextButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}
