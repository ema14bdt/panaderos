<?php

/**
 * API: Validar DNI y Número de Afiliado contra el Padrón precargado
 * POST /api/auth/validar-padron.php
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Método no permitido. Utilice POST.', 405);
}

$input = api_input();

$rawDni = isset($input['dni']) ? trim($input['dni']) : '';
$dni = preg_replace('/[^0-9]/', '', $rawDni);
$numeroAfiliado = isset($input['numero_afiliado']) ? trim($input['numero_afiliado']) : '';

if (empty($dni) || empty($numeroAfiliado)) {
    api_error('El DNI y el número de afiliado son obligatorios.', 400);
}

try {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT `id`, `dni`, `numero_afiliado`, `nombre`, `apellido`, 
                                  `empresa_panaderia`, `categoria_laboral`, `estado`, 
                                  `password_hash` 
                           FROM `afiliados` 
                           WHERE `dni` = :dni AND `numero_afiliado` = :numero
                           LIMIT 1");
    $stmt->execute(array(
        ':dni'    => $dni,
        ':numero' => $numeroAfiliado
    ));
    $afiliado = $stmt->fetch();

    if (!$afiliado) {
        api_error('El DNI o número de afiliado no figura en el padrón del sindicato. Por favor contactate con tu filial.', 404);
    }

    if (!empty($afiliado['password_hash'])) {
        api_error('Este afiliado ya posee una cuenta activa. Por favor iniciá sesión con tu email o DNI.', 409);
    }

    if ($afiliado['estado'] !== 'activo') {
        api_error('El afiliado no se encuentra en estado activo en el padrón (' . htmlspecialchars($afiliado['estado']) . '). Contactate con el sindicato.', 403);
    }

    api_response(array(
        'afiliado_id'       => (int)$afiliado['id'],
        'dni'               => $afiliado['dni'],
        'numero_afiliado'   => $afiliado['numero_afiliado'],
        'nombre'            => $afiliado['nombre'],
        'apellido'          => $afiliado['apellido'],
        'empresa_panaderia' => $afiliado['empresa_panaderia'],
        'categoria_laboral' => $afiliado['categoria_laboral'],
        'mensaje'           => 'Identidad confirmada en el padrón. Podés continuar con la creación de tu cuenta.'
    ), 200);

} catch (Exception $e) {
    api_error('Error interno del servidor: ' . $e->getMessage(), 500);
}
