<?php

function site_home_defaults()
{
    return array(
        'hero_eyebrow' => 'Sindicato de Obreros Panaderos de Lanús',
        'hero_title' => 'El trabajo panadero',
        'hero_emphasis' => 'se defiende',
        'hero_after' => 'unido.',
        'hero_lead' => 'Acompañamos a quienes sostienen cada jornada de trabajo con información, beneficios y una organización presente.',
        'hero_quote' => 'La mejor manera de defender a los trabajadores es hacerles conocer sus derechos.',
        'hero_quote_author' => 'Gabriel Ruiz · Secretario General',
        'salary_url' => 'https://panaderoslanus.ar/public/escalas/ultimaEscala.pdf',
        'social_intro' => 'En Instagram y Facebook compartimos comunicados, actividad institucional, información gremial y la vida cotidiana de las y los trabajadores panaderos.',
        'instagram_url' => 'https://www.instagram.com/panaderoslanus/',
        'instagram_label' => '@panaderoslanus',
        'facebook_url' => 'https://www.facebook.com/PanaderosLanus',
        'facebook_label' => 'Panaderos Lanús',
        'address' => 'Llavallol 902 · Lanús Oeste, Buenos Aires.',
        'phone' => '+541142411920',
        'phone_label' => '4241-1920 · 4241-2477',
        'email' => 'info@panaderoslanus.ar',
        'maps_url' => 'https://www.google.com/maps/search/?api=1&query=Sindicato+de+Obreros+y+Empleados+Panaderos+de+Lan%C3%BAs',
        'maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3280.066545599629!2d-58.40769198807899!3d-34.70350146266279!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95bccce2a44f788d%3A0xbb95049302afb0fc!2sSindicato%20de%20Obreros%20y%20Empleados%20Panaderos%20de%20Lan%C3%B9s!5e0!3m2!1ses-419!2sar!4v1788191054124!5m2!1ses-419!2sar'
    );
}

function site_servicios_defaults()
{
    return array(
        'page_label' => 'Servicios del sindicato',
        'page_title' => 'Beneficios para afiliados',
        'page_intro' => 'Acompañamos las distintas etapas de la vida de las y los afiliados con servicios concretos y cercanos.',
        'section_kicker' => 'Acompañamiento cotidiano',
        'section_title' => 'Servicios que hacen la diferencia.',
        'items' => array(
            array(
                'icon' => 'fa-flask',
                'theme' => 'ink',
                'title' => 'Laboratorios',
                'description' => 'Análisis clínicos, bacteriológicos y de alta complejidad.',
                'detail' => "Ituzaingó 1807 · Lanús Este\n4225-3230 · Lun. a vie., 8:00 a 18:30"
            ),
            array(
                'icon' => 'fa-heart',
                'theme' => 'sun',
                'title' => 'Casamiento',
                'description' => 'Remise al Registro Civil o iglesia y una torta alegórica para la celebración.',
                'detail' => 'Presentá recibo de sueldo, certificado prenupcial y DNI.'
            ),
            array(
                'icon' => 'fa-child',
                'theme' => '',
                'title' => 'Nacimiento',
                'description' => 'Bolso de pañales descartables y ajuar para acompañar a la familia.',
                'detail' => 'Presentá recibo de sueldo, certificado de nacimiento y DNI.'
            ),
            array(
                'icon' => 'fa-plus-square',
                'theme' => '',
                'title' => 'Farmacia',
                'description' => 'Reintegro del 50% en medicamentos para el afiliado y su grupo familiar.',
                'detail' => 'Sitio de Montevideo 1640 · Lanús Oeste'
            ),
            array(
                'icon' => 'fa-truck',
                'theme' => 'ink',
                'title' => 'Mudanzas',
                'description' => 'Reintegro de hasta el 50% del servicio de transporte de mudanza.',
                'detail' => 'Presentá la boleta a nombre del afiliado y el último recibo de sueldo.'
            )
        ),
        'callout_kicker' => '¿Tenés una consulta?',
        'callout_title' => 'Acercate a la sede para conocer requisitos y gestión de cada beneficio.',
        'callout_action_text' => 'Escribir al sindicato',
        'callout_email' => 'info@panaderoslanus.ar'
    );
}

function site_normativas_defaults()
{
    return array(
        'page_label' => 'Información para defender tus derechos',
        'page_title' => 'Normativas y documentos',
        'page_intro' => 'Consultá las normas, convenios y escalas que orientan el trabajo en la actividad panadera.',
        'section_kicker' => 'Marco legal',
        'section_title' => 'Documentación de referencia.',
        'items' => array(
            array(
                'kicker' => 'Convenio colectivo',
                'title' => "CCT\n231/94",
                'url' => 'pdf/covenio231-94.pdf',
                'is_featured' => true
            ),
            array(
                'kicker' => 'Ley de Contrato de Trabajo',
                'title' => 'Ley 20.744',
                'url' => 'https://www.argentina.gob.ar/normativa/nacional/ley-20744-25552',
                'is_featured' => false
            ),
            array(
                'kicker' => 'Asociaciones sindicales',
                'title' => 'Ley 23.551',
                'url' => 'https://www.argentina.gob.ar/normativa/nacional/ley-23551-20993',
                'is_featured' => false
            ),
            array(
                'kicker' => 'Higiene y seguridad',
                'title' => 'Ley 19.587',
                'url' => 'https://www.argentina.gob.ar/normativa/nacional/ley-19587-17612',
                'is_featured' => false
            ),
            array(
                'kicker' => 'Registro de establecimientos',
                'title' => 'Ley 13.006',
                'url' => 'https://www.argentina.gob.ar/normativa/nacional/ley-13006-51221',
                'is_featured' => false
            ),
            array(
                'kicker' => 'Regulación de alimentos',
                'title' => 'Código Alimentario',
                'url' => 'https://www.argentina.gob.ar/anmat/codigoalimentario',
                'is_featured' => false
            )
        ),
        'note_text' => 'Los documentos publicados se ofrecen como material de consulta. Para orientación sobre un caso particular, comunicate con el sindicato.',
        'note_action_text' => 'Hacer una consulta',
        'note_email' => 'info@panaderoslanus.ar'
    );
}

function site_filiales_defaults()
{
    return array(
        'page_label' => 'Una red de representación',
        'page_title' => 'Filiales',
        'page_intro' => 'Encontrá las sedes, teléfonos y autoridades de las filiales que forman parte de la organización.',
        'section_kicker' => 'Directorio',
        'section_title' => 'Filiales en todo el país.',
        'items' => array(
            array('name' => 'Adrogué · Alte. Brown', 'address' => 'Muratore 995 · CP 1846', 'phone' => '(54 11) 4214 1980', 'secretary' => 'Moreno Carlos'),
            array('name' => 'Avellaneda', 'address' => 'Belgrano 1218 · CP 1870', 'phone' => '(54 11) 4203 6770', 'secretary' => 'Frutos Abel'),
            array('name' => 'Capital Federal', 'address' => 'Av. Boedo 168 · CP 1206', 'phone' => '(54 11) 4957 1177', 'secretary' => 'Sánchez José'),
            array('name' => 'Florencio Varela', 'address' => 'Alte. Brown 473 · CP 1888', 'phone' => '(54 11) 4255 1747', 'secretary' => 'Alvarez Pedro'),
            array('name' => 'La Plata', 'address' => 'Calle 15 N° 878 · CP 1900', 'phone' => '(54 0221) 483 6193', 'secretary' => 'Rodríguez Miguel Ángel'),
            array('name' => 'Luján', 'address' => 'Mariano Moreno 1958 · CP 6700', 'phone' => '(54 02323) 431 143 / 433 500', 'secretary' => 'Rodríguez Julio'),
            array('name' => 'Morón', 'address' => 'Yatay 555 · CP 1708', 'phone' => '(54 11) 4629 0684', 'secretary' => 'Durán Jesús'),
            array('name' => 'Pergamino', 'address' => 'Catamarca 257 · CP 2700', 'phone' => '(54 02477) 430 794 / 422 245', 'secretary' => 'Devia Pedro'),
            array('name' => 'Quilmes', 'address' => 'M. Quintana 999 · CP 1879', 'phone' => '(54 11) 4255 1747', 'secretary' => 'Gamarra Elsa'),
            array('name' => 'Ramos Mejía', 'address' => 'Gral. Alvarado 372 · CP 1704', 'phone' => '(54 11) 4654 6585', 'secretary' => 'Castro Rubén'),
            array('name' => 'San Fernando', 'address' => 'Av. Juan D. Perón 325', 'phone' => '(54 11) 4744 0992', 'secretary' => 'Roldán Julio'),
            array('name' => 'San Isidro', 'address' => '3 de Febrero 356 · CP 1642', 'phone' => '(54 11) 4743 2290', 'secretary' => 'Altamirano Aníbal'),
            array('name' => 'San Martín', 'address' => 'Av. Belgrano 3953, 2° Piso Of. H · CP 1650', 'phone' => '(54 11) 4755 1133 / 2369', 'secretary' => 'Romero Ángel'),
            array('name' => 'San Nicolás', 'address' => 'Balcarce 77, Dto. 1 · CP 2900', 'phone' => '(54 03461) 421 499', 'secretary' => 'Cerviño Rodolfo'),
            array('name' => 'Tres Arroyos', 'address' => 'Lucio V. López 435 · CP 7500', 'phone' => '(54 02983) 430 794', 'secretary' => 'Salgado Juan Manuel'),
            array('name' => 'Tres de Febrero', 'address' => 'San Martín 2374, 1° Piso Of. 5 · CP 1678', 'phone' => '(54 11) 4734 5063', 'secretary' => 'Cepeda Abel'),
            array('name' => 'Azul', 'address' => 'Lavalle 357 · CP 7300', 'phone' => '(54 02281) 15520760 / 433 394', 'secretary' => 'Guardia Pedro'),
            array('name' => 'Bahía Blanca', 'address' => 'Soler 644 · CP 8000', 'phone' => '—', 'secretary' => '—'),
            array('name' => 'Bragado', 'address' => 'Pellegrini 999 · CP 6640', 'phone' => '(54 02342) 426 970', 'secretary' => 'Figuerón Abel'),
            array('name' => 'Junín', 'address' => 'República Libanesa 102 · CP 6000', 'phone' => '(54 02362) 431 143', 'secretary' => 'Farías Roxana'),
            array('name' => 'San Miguel', 'address' => '—', 'phone' => '—', 'secretary' => 'Gómez'),
            array('name' => 'Mar del Plata', 'address' => 'Bolívar 4441 · CP 7600', 'phone' => '(54 0223) 472 5680', 'secretary' => 'Coronel Ernesto')
        )
    );
}

function site_comision_defaults()
{
    return array(
        'page_label' => 'El sindicato',
        'page_title' => 'Comisión directiva',
        'page_intro' => 'Una comisión comprometida con representar, acompañar y defender a las y los trabajadores panaderos.',
        'section_kicker' => 'Representación',
        'section_title' => 'Las personas detrás de la organización.',
        'members' => array(
            array('name' => 'Ruiz Gabriel Antonio', 'role' => 'Secretario General', 'photo' => 'images/directivos/sin-foto.jpg'),
            array('name' => 'Ruiz Pablo Ariel', 'role' => 'Secretario de Organización', 'photo' => 'images/directivos/sin-foto.jpg'),
            array('name' => 'Ruiz Fabricio', 'role' => 'Secretaría Gremial', 'photo' => 'images/directivos/sin-foto.jpg'),
            array('name' => 'Bobero Daniel Abraham', 'role' => 'Secretaría de Actas', 'photo' => 'images/directivos/sin-foto.jpg'),
            array('name' => 'Romero Emanuel', 'role' => 'Secretaría de Prensa y Difusión', 'photo' => 'images/directivos/sin-foto.jpg')
        )
    );
}

function site_novedades_defaults()
{
    return array(
        'page_label' => 'Información y comunidad',
        'page_title' => 'Actualidad del sindicato',
        'page_intro' => 'Para conocer comunicados y actividad reciente, seguí los canales oficiales de Panaderos de Lanús en Instagram y Facebook.',
        'social_kicker' => 'Canal oficial',
        'social_title' => 'Las novedades, donde pasan.',
        'social_intro' => 'Instagram es nuestro canal más directo para compartir comunicaciones, información gremial, actividades y beneficios para afiliados.',
        'archive_kicker' => 'Memoria institucional',
        'archive_title' => 'Archivo histórico',
        'archive_intro' => 'Una selección de actividades y encuentros que forman parte de la historia del sindicato.',
        'archive_items' => array(
            array(
                'title' => 'El sindicato marchó por #NiUnaMenos',
                'tag' => 'Archivo',
                'url' => 'novedades/ni-una-menos/index.php',
                'image' => 'novedades/ni-una-menos/fotos/17.jpg',
                'alt' => 'Movilización Ni Una Menos'
            ),
            array(
                'title' => 'Centenario del Club El Porvenir',
                'tag' => 'Archivo',
                'url' => 'novedades/centenario-porvenir/index.php',
                'image' => 'novedades/centenario-porvenir/fotos/01.jpg',
                'alt' => 'Centenario del Club El Porvenir'
            ),
            array(
                'title' => 'Asamblea General Ordinaria',
                'tag' => 'Archivo',
                'url' => 'novedades/asamblea-2015/asamblea-2015.php',
                'image' => 'novedades/asamblea-2015/fotos/full/01.jpg',
                'alt' => 'Asamblea General Ordinaria'
            ),
            array(
                'title' => 'Jornada en familia en las piletas del Porve',
                'tag' => 'Archivo',
                'url' => 'novedades/pileta-porve.php',
                'image' => 'images/novedades/pileta-porve/full/porve-01.jpg',
                'alt' => 'Jornada familiar'
            )
        )
    );
}

function site_instalaciones_defaults()
{
    return array(
        'page_label' => 'Espacios para encontrarnos',
        'page_title' => 'Nuestras instalaciones',
        'page_intro' => 'Conocé los espacios que forman parte de la vida cotidiana del sindicato y de sus afiliados.',
        'section_kicker' => 'Galería',
        'section_title' => 'Un lugar para estar cerca.',
        'items' => array(
            array('image' => 'images/instalaciones/instalaciones-01.jpg', 'title' => 'Sede central e instalaciones'),
            array('image' => 'images/instalaciones/instalaciones-02.jpg', 'title' => 'Espacios de atención y encuentro'),
            array('image' => 'images/instalaciones/instalaciones-03.jpg', 'title' => 'Áreas recreativas y deportivas'),
            array('image' => 'images/instalaciones/instalaciones-04.jpg', 'title' => 'Salón de usos múltiples'),
            array('image' => 'images/instalaciones/instalaciones-05.jpg', 'title' => 'Predio e infraestructura'),
            array('image' => 'images/instalaciones/instalaciones-06.jpg', 'title' => 'Servicios e instalaciones gremiales')
        )
    );
}

function site_content($name, array $defaults)
{
    $allowed = array('home', 'servicios', 'normativas', 'filiales', 'comision', 'novedades', 'instalaciones');
    if (!in_array($name, $allowed, true)) {
        return $defaults;
    }

    $path = __DIR__ . '/private-content/' . $name . '.json';
    if (!is_file($path) || !is_readable($path)) {
        return $defaults;
    }

    $handle = @fopen($path, 'rb');
    if ($handle === false) {
        return $defaults;
    }

    flock($handle, LOCK_SH);
    $json = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $content = json_decode($json, true);
    if (!is_array($content)) {
        return $defaults;
    }

    return array_replace($defaults, array_intersect_key($content, $defaults));
}

function site_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

