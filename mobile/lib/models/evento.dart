class FilialRetiro {
  final int id;
  final String nombre;
  final String direccion;
  final String localidad;
  final String? telefono;
  final String? horarioAtencion;

  FilialRetiro({
    required this.id,
    required this.nombre,
    required this.direccion,
    required this.localidad,
    this.telefono,
    this.horarioAtencion,
  });

  factory FilialRetiro.fromJson(Map<String, dynamic> json) {
    return FilialRetiro(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      nombre: json['nombre']?.toString() ?? '',
      direccion: json['direccion']?.toString() ?? '',
      localidad: json['localidad']?.toString() ?? 'Lanús',
      telefono: json['telefono']?.toString(),
      horarioAtencion: json['horario_atencion']?.toString(),
    );
  }
}

class InscripcionBeneficiario {
  final int familiarId;
  final String nombre;
  final String? talle;
  final String? nivelEscolar;

  InscripcionBeneficiario({
    required this.familiarId,
    required this.nombre,
    this.talle,
    this.nivelEscolar,
  });

  factory InscripcionBeneficiario.fromJson(Map<String, dynamic> json) {
    return InscripcionBeneficiario(
      familiarId: json['familiar_id'] is int ? json['familiar_id'] : int.tryParse(json['familiar_id'].toString()) ?? 0,
      nombre: json['nombre']?.toString() ?? '',
      talle: json['talle']?.toString(),
      nivelEscolar: json['nivel_escolar']?.toString(),
    );
  }
}

class InscripcionAfiliado {
  final int id;
  final String codigoComprobante;
  final String estado;
  final String? filialNombre;
  final String? filialDireccion;
  final List<InscripcionBeneficiario> beneficiarios;

  InscripcionAfiliado({
    required this.id,
    required this.codigoComprobante,
    required this.estado,
    this.filialNombre,
    this.filialDireccion,
    required this.beneficiarios,
  });

  factory InscripcionAfiliado.fromJson(Map<String, dynamic> json) {
    final bens = (json['beneficiarios'] as List<dynamic>?)
            ?.map((item) => InscripcionBeneficiario.fromJson(item as Map<String, dynamic>))
            .toList() ??
        [];

    return InscripcionAfiliado(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      codigoComprobante: json['codigo_comprobante']?.toString() ?? '',
      estado: json['estado']?.toString() ?? 'solicitado',
      filialNombre: json['filial_nombre']?.toString(),
      filialDireccion: json['filial_direccion']?.toString(),
      beneficiarios: bens,
    );
  }
}

class Evento {
  final int id;
  final String titulo;
  final String? descripcion;
  final String tipo;
  final String? imagenUrl;
  final String fechaInicioInscripcion;
  final String fechaFinInscripcion;
  final String? fechaEntrega;
  final int edadMinimaHijo;
  final int edadMaximaHijo;
  final bool requiereEscolaridad;
  final bool requiereTalle;
  final String estado;
  final InscripcionAfiliado? inscripcion;

  Evento({
    required this.id,
    required this.titulo,
    this.descripcion,
    required this.tipo,
    this.imagenUrl,
    required this.fechaInicioInscripcion,
    required this.fechaFinInscripcion,
    this.fechaEntrega,
    required this.edadMinimaHijo,
    required this.edadMaximaHijo,
    required this.requiereEscolaridad,
    required this.requiereTalle,
    required this.estado,
    this.inscripcion,
  });

  bool get estaInscripto => inscripcion != null;
  bool get estaAbierto => estado.toLowerCase() == 'abierto';

  factory Evento.fromJson(Map<String, dynamic> json) {
    return Evento(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      titulo: json['titulo']?.toString() ?? '',
      descripcion: json['descripcion']?.toString(),
      tipo: json['tipo']?.toString() ?? 'general',
      imagenUrl: json['imagen_url']?.toString(),
      fechaInicioInscripcion: json['fecha_inicio_inscripcion']?.toString() ?? '',
      fechaFinInscripcion: json['fecha_fin_inscripcion']?.toString() ?? '',
      fechaEntrega: json['fecha_entrega']?.toString(),
      edadMinimaHijo: json['edad_minima_hijo'] is int
          ? json['edad_minima_hijo']
          : int.tryParse(json['edad_minima_hijo']?.toString() ?? '0') ?? 0,
      edadMaximaHijo: json['edad_maxima_hijo'] is int
          ? json['edad_maxima_hijo']
          : int.tryParse(json['edad_maxima_hijo']?.toString() ?? '18') ?? 18,
      requiereEscolaridad: json['requiere_escolaridad'] == 1 || json['requiere_escolaridad'] == true || json['requiere_escolaridad'] == '1',
      requiereTalle: json['requiere_talle'] == 1 || json['requiere_talle'] == true || json['requiere_talle'] == '1',
      estado: json['estado']?.toString() ?? 'abierto',
      inscripcion: json['inscripcion_afiliado'] is Map<String, dynamic>
          ? InscripcionAfiliado.fromJson(json['inscripcion_afiliado'] as Map<String, dynamic>)
          : null,
    );
  }
}
