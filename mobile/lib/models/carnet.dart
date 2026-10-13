import 'afiliado.dart';
import 'familiar.dart';

class CarnetData {
  final Afiliado afiliado;
  final String qrPayload;
  final Map<String, dynamic> institucion;
  final List<Familiar> familiares;
  final DateTime fechaActualizacion;

  CarnetData({
    required this.afiliado,
    required this.qrPayload,
    required this.institucion,
    required this.familiares,
    required this.fechaActualizacion,
  });

  factory CarnetData.fromJson(Map<String, dynamic> json) {
    final carnetJson = json['carnet'] is Map<String, dynamic> ? json['carnet'] : json;
    final familiaresList = (json['grupo_familiar'] as List<dynamic>?)
            ?.map((item) => Familiar.fromJson(item as Map<String, dynamic>))
            .toList() ??
        [];

    return CarnetData(
      afiliado: Afiliado.fromJson(carnetJson),
      qrPayload: carnetJson['qr_payload']?.toString() ?? '',
      institucion: carnetJson['institucion'] is Map<String, dynamic>
          ? Map<String, dynamic>.from(carnetJson['institucion'])
          : {
              'nombre': 'Sindicato de Obreros Panaderos de Lanús',
              'sigla': 'SOEPL',
              'cuit': '30-58836528-7',
              'personeria_gremial': 'N° 110',
            },
      familiares: familiaresList,
      fechaActualizacion: json['fecha_actualizacion'] != null
          ? DateTime.tryParse(json['fecha_actualizacion'].toString()) ?? DateTime.now()
          : DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'carnet': {
        ...afiliado.toJson(),
        'qr_payload': qrPayload,
        'institucion': institucion,
      },
      'grupo_familiar': familiares.map((f) => f.toJson()).toList(),
      'fecha_actualizacion': fechaActualizacion.toIso8601String(),
    };
  }
}
