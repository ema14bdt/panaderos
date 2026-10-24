class Afiliado {
  final int id;
  final String dni;
  final String numeroAfiliado;
  final String nombre;
  final String apellido;
  final String? email;
  final String? telefono;
  final String? empresaPanaderia;
  final String? categoriaLaboral;
  final String estado;
  final String rol;
  final String? fechaAfiliacion;
  final String? fechaVencimiento;
  final String? fotoUrl;

  Afiliado({
    required this.id,
    required this.dni,
    required this.numeroAfiliado,
    required this.nombre,
    required this.apellido,
    this.email,
    this.telefono,
    this.empresaPanaderia,
    this.categoriaLaboral,
    required this.estado,
    this.rol = 'afiliado',
    this.fechaAfiliacion,
    this.fechaVencimiento,
    this.fotoUrl,
  });

  String get nombreCompleto => '$apellido, $nombre';
  bool get isActivo => estado.toLowerCase() == 'activo';
  bool get isAdmin => rol.toLowerCase() == 'admin';

  factory Afiliado.fromJson(Map<String, dynamic> json) {
    return Afiliado(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      dni: json['dni']?.toString() ?? '',
      numeroAfiliado: json['numero_afiliado']?.toString() ?? '',
      nombre: json['nombre']?.toString() ?? '',
      apellido: json['apellido']?.toString() ?? '',
      email: json['email']?.toString(),
      telefono: json['telefono']?.toString(),
      empresaPanaderia: json['empresa_panaderia']?.toString(),
      categoriaLaboral: json['categoria_laboral']?.toString(),
      estado: json['estado']?.toString() ?? 'activo',
      rol: json['rol']?.toString() ?? 'afiliado',
      fechaAfiliacion: json['fecha_afiliacion']?.toString(),
      fechaVencimiento: json['fecha_vencimiento']?.toString(),
      fotoUrl: json['foto_url']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'dni': dni,
      'numero_afiliado': numeroAfiliado,
      'nombre': nombre,
      'apellido': apellido,
      'email': email,
      'telefono': telefono,
      'empresa_panaderia': empresaPanaderia,
      'categoria_laboral': categoriaLaboral,
      'estado': estado,
      'rol': rol,
      'fecha_afiliacion': fechaAfiliacion,
      'fecha_vencimiento': fechaVencimiento,
      'foto_url': fotoUrl,
    };
  }
}
