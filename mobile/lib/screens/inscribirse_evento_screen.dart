import 'package:flutter/material.dart';
import '../core/constants.dart';
import '../models/evento.dart';
import '../models/familiar.dart';
import '../services/api_service.dart';
import '../widgets/comprobante_dialog.dart';

class InscribirseEventoScreen extends StatefulWidget {
  final Evento evento;
  final List<FilialRetiro> filiales;
  final List<Familiar> familiares;

  const InscribirseEventoScreen({
    super.key,
    required this.evento,
    required this.filiales,
    required this.familiares,
  });

  @override
  State<InscribirseEventoScreen> createState() => _InscribirseEventoScreenState();
}

class _BeneficiarioFormItem {
  final Familiar familiar;
  bool seleccionado;
  String? talle;
  String? nivelEscolar;

  _BeneficiarioFormItem({
    required this.familiar,
    this.seleccionado = false,
    this.talle,
    this.nivelEscolar,
  });
}

class _InscribirseEventoScreenState extends State<InscribirseEventoScreen> {
  final ApiService _apiService = ApiService();
  final TextEditingController _observacionesController = TextEditingController();

  FilialRetiro? _filialSeleccionada;
  late List<_BeneficiarioFormItem> _itemsBeneficiarios;
  bool _isLoading = false;

  final List<String> _tallesDisponibles = const [
    'Talle 6',
    'Talle 8',
    'Talle 10',
    'Talle 12',
    'Talle 14',
    'Talle 16',
    'Talle S',
    'Talle M',
    'Talle L',
    'Talle XL',
  ];

  final List<String> _nivelesEscolares = const [
    'Jardín / Inicial',
    'Primaria',
    'Secundaria',
    'Especial',
  ];

  @override
  void initState() {
    super.initState();
    if (widget.filiales.isNotEmpty) {
      _filialSeleccionada = widget.filiales.first;
    }

    _itemsBeneficiarios = widget.familiares.map((fam) {
      // Valor por defecto según escolaridad previa si coincide
      String? nivelDefecto;
      if (fam.escolaridad == 'primaria') {
        nivelDefecto = 'Primaria';
      } else if (fam.escolaridad == 'jardin' || fam.escolaridad == 'maternal') {
        nivelDefecto = 'Jardín / Inicial';
      } else if (fam.escolaridad == 'secundaria') {
        nivelDefecto = 'Secundaria';
      }

      return _BeneficiarioFormItem(
        familiar: fam,
        seleccionado: false,
        talle: widget.evento.requiereTalle ? 'Talle 10' : null,
        nivelEscolar: nivelDefecto ?? (widget.evento.requiereEscolaridad ? 'Primaria' : null),
      );
    }).toList();
  }

  @override
  void dispose() {
    _observacionesController.dispose();
    super.dispose();
  }

  Future<void> _enviarInscripcion() async {
    if (_filialSeleccionada == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Por favor, seleccioná una sede de retiro.')),
      );
      return;
    }

    final seleccionados = _itemsBeneficiarios.where((i) => i.seleccionado).toList();

    if (seleccionados.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Debés seleccionar al menos un hijo/beneficiario.')),
      );
      return;
    }

    // Validar requerimientos
    if (widget.evento.requiereTalle) {
      final sinTalle = seleccionados.any((i) => i.talle == null || i.talle!.isEmpty);
      if (sinTalle) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Por favor indicá el talle para todos los beneficiarios seleccionados.')),
        );
        return;
      }
    }

    if (widget.evento.requiereEscolaridad) {
      final sinNivel = seleccionados.any((i) => i.nivelEscolar == null || i.nivelEscolar!.isEmpty);
      if (sinNivel) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Por favor indicá el nivel escolar para todos los beneficiarios seleccionados.')),
        );
        return;
      }
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final beneficiariosPayload = seleccionados.map((item) {
        return {
          'familiar_id': item.familiar.id,
          'talle': item.talle?.replaceAll('Talle ', '').trim(),
          'nivel_escolar': item.nivelEscolar?.toLowerCase(),
        };
      }).toList();

      final res = await _apiService.inscribirseEvento(
        eventoId: widget.evento.id,
        filialRetiroId: _filialSeleccionada!.id,
        observaciones: _observacionesController.text.trim().isNotEmpty
            ? _observacionesController.text.trim()
            : null,
        beneficiarios: beneficiariosPayload,
      );

      if (!mounted) return;

      final codigo = res['codigo_comprobante']?.toString() ?? '';

      // Crear objeto temporal para mostrar comprobante
      final inscripcionRes = InscripcionAfiliado(
        id: res['inscripcion_id'] is int ? res['inscripcion_id'] : int.tryParse(res['inscripcion_id'].toString()) ?? 0,
        codigoComprobante: codigo,
        estado: 'solicitado',
        filialNombre: _filialSeleccionada!.nombre,
        filialDireccion: _filialSeleccionada!.direccion,
        beneficiarios: seleccionados.map((item) {
          return InscripcionBeneficiario(
            familiarId: item.familiar.id,
            nombre: item.familiar.nombreCompleto,
            talle: item.talle,
            nivelEscolar: item.nivelEscolar,
          );
        }).toList(),
      );

      // Mostrar modal con comprobante
      await showModalBottomSheet(
        context: context,
        isScrollControlled: true,
        backgroundColor: Colors.transparent,
        builder: (ctx) => ComprobanteModalBottomSheet(
          evento: widget.evento,
          inscripcion: inscripcionRes,
        ),
      );

      if (!mounted) return;
      Navigator.of(context).pop(true); // Retorna true para refrescar la lista de eventos
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.message),
          backgroundColor: AppColors.error,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Error inesperado al conectar con el servidor.'),
          backgroundColor: AppColors.error,
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Inscripción al Evento'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Cabecera del Evento
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
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
                  Text(
                    widget.evento.titulo,
                    style: const TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.bold,
                      color: AppColors.primary,
                    ),
                  ),
                  if (widget.evento.descripcion != null) ...[
                    const SizedBox(height: 6),
                    Text(
                      widget.evento.descripcion!,
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade700,
                        height: 1.35,
                      ),
                    ),
                  ],
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 6,
                    children: [
                      _buildBadge(
                        Icons.child_care,
                        'Edad: ${widget.evento.edadMinimaHijo} a ${widget.evento.edadMaximaHijo} años',
                      ),
                      if (widget.evento.fechaEntrega != null)
                        _buildBadge(
                          Icons.calendar_today,
                          'Entrega: ${widget.evento.fechaEntrega}',
                        ),
                    ],
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Selección de Filial de Retiro
            const Text(
              '1. Sede o Filial de Retiro',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: AppColors.primary,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'Elegí dónde vas a retirar el kit o beneficio:',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 8),

            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<FilialRetiro>(
                  isExpanded: true,
                  value: _filialSeleccionada,
                  icon: const Icon(Icons.arrow_drop_down, color: AppColors.primary),
                  items: widget.filiales.map((f) {
                    return DropdownMenuItem<FilialRetiro>(
                      value: f,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            f.nombre,
                            style: const TextStyle(
                              fontWeight: FontWeight.w600,
                              fontSize: 14,
                            ),
                          ),
                          Text(
                            '${f.direccion} (${f.localidad})',
                            style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                  onChanged: (val) {
                    setState(() {
                      _filialSeleccionada = val;
                    });
                  },
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Selección de Hijos / Beneficiarios
            const Text(
              '2. Hijos / Beneficiarios',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: AppColors.primary,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'Marcá quiénes recibirán el beneficio y completá los datos requeridos:',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 10),

            if (_itemsBeneficiarios.isEmpty)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  children: [
                    Icon(Icons.info_outline, color: Colors.grey.shade400, size: 36),
                    const SizedBox(height: 8),
                    const Text(
                      'No tenés familiares registrados en tu ficha sindical.',
                      style: TextStyle(fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Acercate a la sede gremial con los DNI y partidas de nacimiento de tus hijos para incorporarlos a tu grupo familiar.',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                    ),
                  ],
                ),
              )
            else
              ..._itemsBeneficiarios.map((item) {
                final fam = item.familiar;
                final cumpleEdad = fam.edad >= widget.evento.edadMinimaHijo &&
                    fam.edad <= widget.evento.edadMaximaHijo;

                return Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: item.seleccionado ? AppColors.accent : AppColors.border,
                      width: item.seleccionado ? 1.5 : 1,
                    ),
                  ),
                  child: Column(
                    children: [
                      CheckboxListTile(
                        value: item.seleccionado,
                        activeColor: AppColors.primary,
                        enabled: cumpleEdad,
                        onChanged: cumpleEdad
                            ? (val) {
                                setState(() {
                                  item.seleccionado = val ?? false;
                                });
                              }
                            : null,
                        title: Row(
                          children: [
                            Expanded(
                              child: Text(
                                fam.nombreCompleto,
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                  color: cumpleEdad
                                      ? AppColors.textMain
                                      : Colors.grey.shade400,
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: cumpleEdad
                                    ? AppColors.primary.withValues(alpha: 0.08)
                                    : Colors.grey.shade100,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                '${fam.edad} años',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: cumpleEdad
                                      ? AppColors.primary
                                      : Colors.grey.shade500,
                                ),
                              ),
                            ),
                          ],
                        ),
                        subtitle: Text(
                          cumpleEdad
                              ? 'DNI: ${fam.dni ?? "S/D"} · ${fam.parentesco.toUpperCase()}'
                              : 'No cumple rango de edad (${widget.evento.edadMinimaHijo} a ${widget.evento.edadMaximaHijo} años)',
                          style: TextStyle(
                            fontSize: 12,
                            color: cumpleEdad ? Colors.grey.shade600 : AppColors.error,
                          ),
                        ),
                      ),

                      // Campos adicionales si está seleccionado
                      if (item.seleccionado) ...[
                        const Divider(height: 1),
                        Padding(
                          padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
                          child: Row(
                            children: [
                              // Talle
                              if (widget.evento.requiereTalle)
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'Talle guardapolvo:',
                                        style: TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w600,
                                          color: AppColors.textMuted,
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10),
                                        decoration: BoxDecoration(
                                          color: AppColors.background,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: AppColors.border),
                                        ),
                                        child: DropdownButtonHideUnderline(
                                          child: DropdownButton<String>(
                                            isExpanded: true,
                                            value: item.talle,
                                            items: _tallesDisponibles.map((t) {
                                              return DropdownMenuItem<String>(
                                                value: t,
                                                child: Text(t, style: const TextStyle(fontSize: 13)),
                                              );
                                            }).toList(),
                                            onChanged: (val) {
                                              setState(() {
                                                item.talle = val;
                                              });
                                            },
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),

                              if (widget.evento.requiereTalle && widget.evento.requiereEscolaridad)
                                const SizedBox(width: 12),

                              // Nivel Escolar
                              if (widget.evento.requiereEscolaridad)
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'Nivel escolar:',
                                        style: TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w600,
                                          color: AppColors.textMuted,
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10),
                                        decoration: BoxDecoration(
                                          color: AppColors.background,
                                          borderRadius: BorderRadius.circular(8),
                                          border: Border.all(color: AppColors.border),
                                        ),
                                        child: DropdownButtonHideUnderline(
                                          child: DropdownButton<String>(
                                            isExpanded: true,
                                            value: item.nivelEscolar,
                                            items: _nivelesEscolares.map((n) {
                                              return DropdownMenuItem<String>(
                                                value: n,
                                                child: Text(n, style: const TextStyle(fontSize: 13)),
                                              );
                                            }).toList(),
                                            onChanged: (val) {
                                              setState(() {
                                                item.nivelEscolar = val;
                                              });
                                            },
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                );
              }),

            const SizedBox(height: 16),

            // Observaciones
            const Text(
              '3. Observaciones o aclaraciones (Opcional)',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: AppColors.primary,
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _observacionesController,
              maxLines: 2,
              decoration: InputDecoration(
                hintText: 'Ej: Retiro por la mañana, turno noche...',
                hintStyle: TextStyle(fontSize: 13, color: Colors.grey.shade400),
                fillColor: Colors.white,
                filled: true,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                contentPadding: const EdgeInsets.all(12),
              ),
            ),

            const SizedBox(height: 28),

            // Botón de Enviar
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _enviarInscripcion,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text(
                        'Confirmar Inscripción',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildBadge(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.background,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: AppColors.primaryLight),
          const SizedBox(width: 5),
          Text(
            text,
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: AppColors.textMain,
            ),
          ),
        ],
      ),
    );
  }
}
