<?php

/**
 * API: Obtener datos del Carnet Digital del Afiliado autenticado
 * GET /api/carnet/digital.php
 * Header requerido: Authorization: Bearer <auth_token>
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Método no permitido. Utilice GET.', 405);
}

// 1. Requiere autenticación activa
$afiliado = api_require_auth();

try {
    $pdo = db_connect();

    // 2. Obtener grupo familiar vinculado al afiliado
    $famStmt = $pdo->prepare("SELECT `id`, `nombre`, `apellido`, `dni`, 
                                     `fecha_nacimiento`, `parentesco`, `escolaridad` 
                              FROM `afiliado_familiares` 
                              WHERE `afiliado_id` = :id 
                              ORDER BY `fecha_nacimiento` DESC");
    $famStmt->execute(array(':id' => $afiliado['id']));
    $familiares = $famStmt->fetchAll();

    // 3. Generar payload de verificación para el código QR
    // Este payload permite que una farmacia o comercio verifique la vigencia escaneando el QR
    $hashVerificacion = hash('sha256', $afiliado['dni'] . '|' . $afiliado['numero_afiliado'] . '|' . $afiliado['estado'] . '|sop_lanus_secret');
    $qrData = array(
        'sindicato' => 'SOP Lanús',
        'dni'       => $afiliado['dni'],
        'nro'       => $afiliado['numero_afiliado'],
        'titular'   => $afiliado['apellido'] . ', ' . $afiliado['nombre'],
        'estado'    => $afiliado['estado'],
        'vigencia'  => $afiliado['fecha_vencimiento'] ? $afiliado['fecha_vencimiento'] : 'Sin vencimiento',
        'checksum'  => substr($hashVerificacion, 0, 16)
    );

    api_response(array(
        'carnet' => array(
            'id'                => (int)$afiliado['id'],
            'numero_afiliado'   => $afiliado['numero_afiliado'],
            'dni'               => $afiliado['dni'],
            'nombre'            => $afiliado['nombre'],
            'apellido'          => $afiliado['apellido'],
            'nombre_completo'   => $afiliado['apellido'] . ', ' . $afiliado['nombre'],
            'empresa_panaderia' => $afiliado['empresa_panaderia'],
            'categoria_laboral' => $afiliado['categoria_laboral'],
            'estado'            => $afiliado['estado'],
            'fecha_afiliacion'  => $afiliado['fecha_afiliacion'],
            'fecha_vencimiento' => $afiliado['fecha_vencimiento'],
            'foto_url'          => $afiliado['foto_url'],
            'qr_payload'        => json_encode($qrData, JSON_UNESCAPED_UNICODE),
            'institucion'       => array(
                'nombre'             => 'Sindicato de Obreros Panaderos de Lanús',
                'sigla'              => 'SOEPL',
                'cuit'               => '30-58836528-7',
                'personeria_gremial' => 'N° 110',
                'sede_central'       => 'Av. Hipólito Yrigoyen 3960, Lanús Oeste'
            )
        ),
        'grupo_familiar' => $familiares
    ), 200);

} catch (Exception $e) {
    api_error('Error al generar carnet digital: ' . $e->getMessage(), 500);
}
