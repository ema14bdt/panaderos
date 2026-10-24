<?php

/**
 * API: Contenido dinámico de la Home (Información institucional, escala salarial, día del panadero y redes)
 * GET /api/home/index.php
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(dirname(__DIR__)) . '/site-content.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Método no permitido. Utilice GET.', 405);
}

try {
    $homeContent = site_content('home', site_home_defaults());

    $response = array(
        'hero' => array(
            'eyebrow'      => isset($homeContent['hero_eyebrow']) ? $homeContent['hero_eyebrow'] : 'Sindicato de Obreros y Empleados Panaderos de Lanús',
            'title'        => isset($homeContent['hero_title']) ? $homeContent['hero_title'] : 'El trabajo panadero',
            'emphasis'     => isset($homeContent['hero_emphasis']) ? $homeContent['hero_emphasis'] : 'se defiende',
            'after'        => isset($homeContent['hero_after']) ? $homeContent['hero_after'] : 'unido.',
            'lead'         => isset($homeContent['hero_lead']) ? $homeContent['hero_lead'] : '',
            'quote'        => isset($homeContent['hero_quote']) ? $homeContent['hero_quote'] : 'La mejor manera de defender a los trabajadores es hacerles conocer sus derechos.',
            'quote_author' => isset($homeContent['hero_quote_author']) ? $homeContent['hero_quote_author'] : 'Gabriel Ruiz · Secretario General',
        ),
        'escala_salarial' => array(
            'titulo' => 'Escala Salarial Vigente',
            'subtitulo' => 'Consultá la última escala salarial homologada del gremio',
            'pdf_url' => isset($homeContent['salary_url']) ? $homeContent['salary_url'] : 'https://panaderoslanus.ar/public/escalas/ultimaEscala.pdf',
        ),
        'dia_panadero' => array(
            'enabled'     => !empty($homeContent['dia_panadero_enabled']),
            'badge'       => isset($homeContent['dia_panadero_badge']) ? $homeContent['dia_panadero_badge'] : '4 de Agosto · Conmemoración',
            'title'       => isset($homeContent['dia_panadero_title']) ? $homeContent['dia_panadero_title'] : '4 DE AGOSTO - DÍA DEL OBRERO PANADERO',
            'description' => isset($homeContent['dia_panadero_description']) ? $homeContent['dia_panadero_description'] : '',
            'image'       => isset($homeContent['dia_panadero_image']) ? $homeContent['dia_panadero_image'] : 'images/dia-panaderos.png',
        ),
        'social' => array(
            'instagram_url'   => isset($homeContent['instagram_url']) ? $homeContent['instagram_url'] : 'https://www.instagram.com/panaderoslanus/',
            'instagram_label' => isset($homeContent['instagram_label']) ? $homeContent['instagram_label'] : '@panaderoslanus',
            'facebook_url'    => isset($homeContent['facebook_url']) ? $homeContent['facebook_url'] : 'https://www.facebook.com/PanaderosLanus',
            'facebook_label'  => isset($homeContent['facebook_label']) ? $homeContent['facebook_label'] : 'Panaderos Lanús',
            'phone'           => isset($homeContent['phone']) ? $homeContent['phone'] : '+541142411920',
            'phone_label'     => isset($homeContent['phone_label']) ? $homeContent['phone_label'] : '4241-1920 · 4241-2477',
            'address'         => isset($homeContent['address']) ? $homeContent['address'] : 'Llavallol 902 · Lanús Oeste, Buenos Aires.',
            'email'           => isset($homeContent['email']) ? $homeContent['email'] : 'info@panaderoslanus.ar',
            'web_url'         => 'https://panaderoslanus.ar',
        )
    );

    api_response($response, 200);

} catch (Exception $e) {
    api_error('Error al cargar contenido de inicio: ' . $e->getMessage(), 500);
}
