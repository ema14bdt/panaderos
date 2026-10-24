class HomeHero {
  final String eyebrow;
  final String title;
  final String emphasis;
  final String after;
  final String lead;
  final String quote;
  final String quoteAuthor;

  HomeHero({
    required this.eyebrow,
    required this.title,
    required this.emphasis,
    required this.after,
    required this.lead,
    required this.quote,
    required this.quoteAuthor,
  });

  String get fullTitle => '$title $emphasis $after'.trim();

  factory HomeHero.fromJson(Map<String, dynamic> json) {
    return HomeHero(
      eyebrow: json['eyebrow']?.toString() ?? '',
      title: json['title']?.toString() ?? 'El trabajo panadero',
      emphasis: json['emphasis']?.toString() ?? 'se defiende',
      after: json['after']?.toString() ?? 'unido.',
      lead: json['lead']?.toString() ?? '',
      quote: json['quote']?.toString() ?? '',
      quoteAuthor: json['quote_author']?.toString() ?? '',
    );
  }
}

class HomeEscalaSalarial {
  final String titulo;
  final String subtitulo;
  final String pdfUrl;

  HomeEscalaSalarial({
    required this.titulo,
    required this.subtitulo,
    required this.pdfUrl,
  });

  factory HomeEscalaSalarial.fromJson(Map<String, dynamic> json) {
    return HomeEscalaSalarial(
      titulo: json['titulo']?.toString() ?? 'Escala Salarial Vigente',
      subtitulo: json['subtitulo']?.toString() ?? 'Consultá la última escala salarial homologada',
      pdfUrl: json['pdf_url']?.toString() ?? 'https://panaderoslanus.ar/public/escalas/ultimaEscala.pdf',
    );
  }
}

class HomeDiaPanadero {
  final bool enabled;
  final String badge;
  final String title;
  final String description;
  final String image;

  HomeDiaPanadero({
    required this.enabled,
    required this.badge,
    required this.title,
    required this.description,
    required this.image,
  });

  factory HomeDiaPanadero.fromJson(Map<String, dynamic> json) {
    return HomeDiaPanadero(
      enabled: json['enabled'] == true,
      badge: json['badge']?.toString() ?? '4 de Agosto · Conmemoración',
      title: json['title']?.toString() ?? '4 DE AGOSTO - DÍA DEL OBRERO PANADERO',
      description: json['description']?.toString() ?? '',
      image: json['image']?.toString() ?? 'images/dia-panaderos.png',
    );
  }
}

class HomeSocial {
  final String instagramUrl;
  final String instagramLabel;
  final String facebookUrl;
  final String facebookLabel;
  final String phone;
  final String phoneLabel;
  final String address;
  final String email;
  final String webUrl;

  HomeSocial({
    required this.instagramUrl,
    required this.instagramLabel,
    required this.facebookUrl,
    required this.facebookLabel,
    required this.phone,
    required this.phoneLabel,
    required this.address,
    required this.email,
    required this.webUrl,
  });

  factory HomeSocial.fromJson(Map<String, dynamic> json) {
    return HomeSocial(
      instagramUrl: json['instagram_url']?.toString() ?? 'https://www.instagram.com/panaderoslanus/',
      instagramLabel: json['instagram_label']?.toString() ?? '@panaderoslanus',
      facebookUrl: json['facebook_url']?.toString() ?? 'https://www.facebook.com/PanaderosLanus',
      facebookLabel: json['facebook_label']?.toString() ?? 'Panaderos Lanús',
      phone: json['phone']?.toString() ?? '+541142411920',
      phoneLabel: json['phone_label']?.toString() ?? '4241-1920 · 4241-2477',
      address: json['address']?.toString() ?? 'Llavallol 902 · Lanús Oeste',
      email: json['email']?.toString() ?? 'info@panaderoslanus.ar',
      webUrl: json['web_url']?.toString() ?? 'https://panaderoslanus.ar',
    );
  }
}

class HomeData {
  final HomeHero hero;
  final HomeEscalaSalarial escalaSalarial;
  final HomeDiaPanadero diaPanadero;
  final HomeSocial social;

  HomeData({
    required this.hero,
    required this.escalaSalarial,
    required this.diaPanadero,
    required this.social,
  });

  factory HomeData.fromJson(Map<String, dynamic> json) {
    return HomeData(
      hero: HomeHero.fromJson(json['hero'] is Map<String, dynamic> ? json['hero'] : {}),
      escalaSalarial: HomeEscalaSalarial.fromJson(json['escala_salarial'] is Map<String, dynamic> ? json['escala_salarial'] : {}),
      diaPanadero: HomeDiaPanadero.fromJson(json['dia_panadero'] is Map<String, dynamic> ? json['dia_panadero'] : {}),
      social: HomeSocial.fromJson(json['social'] is Map<String, dynamic> ? json['social'] : {}),
    );
  }
}
