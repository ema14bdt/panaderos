class Beneficio {
  final int id;
  final String rubro;
  final String nombre;
  final String descuentoDetalle;
  final String? descripcion;
  final String? direccion;
  final String? localidad;
  final String? telefono;
  final String? whatsapp;
  final String? coordenadas;
  final String? imagenUrl;

  Beneficio({
    required this.id,
    required this.rubro,
    required this.nombre,
    required this.descuentoDetalle,
    this.descripcion,
    this.direccion,
    this.localidad,
    this.telefono,
    this.whatsapp,
    this.coordenadas,
    this.imagenUrl,
  });

  factory Beneficio.fromJson(Map<String, dynamic> json) {
    return Beneficio(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      rubro: json['rubro']?.toString() ?? 'otro',
      nombre: json['nombre']?.toString() ?? '',
      descuentoDetalle: json['descuento_detalle']?.toString() ?? '',
      descripcion: json['descripcion']?.toString(),
      direccion: json['direccion']?.toString(),
      localidad: json['localidad']?.toString(),
      telefono: json['telefono']?.toString(),
      whatsapp: json['whatsapp']?.toString(),
      coordenadas: json['coordenadas']?.toString(),
      imagenUrl: json['imagen_url']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'rubro': rubro,
      'nombre': nombre,
      'descuento_detalle': descuentoDetalle,
      'descripcion': descripcion,
      'direccion': direccion,
      'localidad': localidad,
      'telefono': telefono,
      'whatsapp': whatsapp,
      'coordenadas': coordenadas,
      'imagen_url': imagenUrl,
    };
  }
}
