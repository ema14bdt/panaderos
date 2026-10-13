class Familiar {
  final int id;
  final String nombre;
  final String apellido;
  final String? dni;
  final String fechaNacimiento;
  final String parentesco;
  final String escolaridad;

  Familiar({
    required this.id,
    required this.nombre,
    required this.apellido,
    this.dni,
    required this.fechaNacimiento,
    required this.parentesco,
    required this.escolaridad,
  });

  String get nombreCompleto => '$apellido, $nombre';

  factory Familiar.fromJson(Map<String, dynamic> json) {
    return Familiar(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      nombre: json['nombre']?.toString() ?? '',
      apellido: json['apellido']?.toString() ?? '',
      dni: json['dni']?.toString(),
      fechaNacimiento: json['fecha_nacimiento']?.toString() ?? '',
      parentesco: json['parentesco']?.toString() ?? 'hijo',
      escolaridad: json['escolaridad']?.toString() ?? 'ninguna',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nombre': nombre,
      'apellido': apellido,
      'dni': dni,
      'fecha_nacimiento': fechaNacimiento,
      'parentesco': parentesco,
      'escolaridad': escolaridad,
    };
  }
}
