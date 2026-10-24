<?php

/**
 * API Administrador: Consulta y Validación de Comprobante QR de Retiro
 * POST /api/admin/validar-comprobante.php
 * Header: Authorization: Bearer <auth_token> (Requiere rol admin)
 * Body:
 *   {
 *     "codigo_comprobante": "UTILES-2027-A1B2C",
 *     "confirmar_entrega": true/false
 *   }
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Método no permitido. Utilice POST.', 405);
}

// 1. Validar autenticación de administrador
$admin = api_require_auth();
if (!isset($admin['rol']) || $admin['rol'] !== 'admin') {
    api_error('Acceso restringido a administradores sindicales.', 403);
}

$input = api_input();
$codigo = isset($input['codigo_comprobante']) ? trim($input['codigo_comprobante']) : '';
$confirmarEntrega = !empty($input['confirmar_entrega']);

// Limpiar prefijo si viene del QR como COMPROBANTE:XXX
$codigoLimpio = preg_replace('/^COMPROBANTE:/i', '', $codigo);

if (empty($codigoLimpio)) {
    api_error('Debe ingresar o escanear un código de comprobante.', 400);
}

try {
    $pdo = db_connect();

    // 2. Buscar inscripción
    $stmt = $pdo->prepare("SELECT i.*, 
                                  a.`nombre` AS `afiliado_nombre`, 
                                  a.`apellido` AS `afiliado_apellido`, 
                                  a.`dni` AS `afiliado_dni`, 
                                  a.`numero_afiliado`, 
                                  a.`telefono` AS `afiliado_telefono`,
                                  e.`titulo` AS `evento_titulo`, 
                                  e.`tipo` AS `evento_tipo`,
                                  f.`nombre` AS `filial_nombre`
                           FROM `evento_inscripciones` i
                           JOIN `afiliados` a ON i.`afiliado_id` = a.`id`
                           JOIN `eventos` e ON i.`evento_id` = e.`id`
                           LEFT JOIN `filiales_retiro` f ON i.`filial_retiro_id` = f.`id`
                           WHERE i.`codigo_comprobante` = :codigo
                           LIMIT 1");
    $stmt->execute(array(':codigo' => $codigoLimpio));
    $inscripcion = $stmt->fetch();

    if (!$inscripcion) {
        api_error('Comprobante inexistente. Verifique el código escaneado.', 404);
    }

    // 3. Obtener beneficiarios vinculados
    $stmtBens = $pdo->prepare("SELECT b.*, fam.`nombre`, fam.`apellido`, fam.`parentesco`, fam.`dni` AS `hijo_dni`
                              FROM `evento_inscripcion_beneficiarios` b
                              JOIN `afiliado_familiares` fam ON b.`familiar_id` = fam.`id`
                              WHERE b.`inscripcion_id` = :insc_id");
    $stmtBens->execute(array(':insc_id' => $inscripcion['id']));
    $beneficiarios = $stmtBens->fetchAll();

    // 4. Si se solicitó confirmar entrega
    if ($confirmarEntrega) {
        if ($inscripcion['estado'] === 'entregado') {
            api_error('Este comprobante ya figura como ENTREGADO anteriormente.', 409);
        }

        $stmtUpdate = $pdo->prepare("UPDATE `evento_inscripciones` 
                                     SET `estado` = 'entregado', `updated_at` = NOW() 
                                     WHERE `id` = :id");
        $stmtUpdate->execute(array(':id' => $inscripcion['id']));
        $inscripcion['estado'] = 'entregado';
    }

    api_response(array(
        'inscripcion_id'     => (int)$inscripcion['id'],
        'codigo_comprobante' => $inscripcion['codigo_comprobante'],
        'estado'             => $inscripcion['estado'],
        'afiliado_titular'   => $inscripcion['afiliado_apellido'] . ', ' . $inscripcion['afiliado_nombre'],
        'afiliado_dni'       => $inscripcion['afiliado_dni'],
        'numero_afiliado'    => $inscripcion['numero_afiliado'],
        'afiliado_telefono'  => $inscripcion['afiliado_telefono'],
        'evento_titulo'      => $inscripcion['evento_titulo'],
        'filial_nombre'      => $inscripcion['filial_nombre'] ? $inscripcion['filial_nombre'] : 'Sede Central Lanús',
        'observaciones'      => $inscripcion['observaciones'],
        'beneficiarios'      => $beneficiarios,
        'fecha_inscripcion'  => $inscripcion['created_at'],
        'es_valido'          => true,
        'ya_entregado'       => $inscripcion['estado'] === 'entregado',
    ), 200);

} catch (Exception $e) {
    api_error('Error al validar comprobante: ' . $e->getMessage(), 500);
}
