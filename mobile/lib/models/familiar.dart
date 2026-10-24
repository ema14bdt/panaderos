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

  int get edad {
    try {
      final birthDate = DateTime.parse(fechaNacimiento);
      final now = DateTime.now();
      int age = now.year - birthDate.year;
      if (now.month < birthDate.month ||
          (now.month == birthDate.month && now.day < birthDate.day)) {
        age--;
      }
      return age >= 0 ? age : 0;
    } catch (_) {
      return 0;
    }
  }

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
