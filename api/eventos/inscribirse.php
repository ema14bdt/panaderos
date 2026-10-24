<?php

/**
 * API: Inscripción de Afiliado y Familiares a un Evento
 * POST /api/eventos/inscribirse.php
 * Header: Authorization: Bearer <auth_token>
 * Body JSON:
 *   {
 *     "evento_id": 1,
 *     "filial_retiro_id": 1,
 *     "observaciones": "Opcional",
 *     "beneficiarios": [
 *       {
 *         "familiar_id": 5,
 *         "talle": "10",
 *         "nivel_escolar": "primaria",
 *         "observacion": ""
 *       }
 *     ]
 *   }
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Método no permitido. Utilice POST.', 405);
}

// 1. Validar autenticación
$afiliado = api_require_auth();
$afiliadoId = (int)$afiliado['id'];

$input = api_input();

$eventoId = isset($input['evento_id']) ? (int)$input['evento_id'] : 0;
$filialId = isset($input['filial_retiro_id']) ? (int)$input['filial_retiro_id'] : 0;
$observaciones = isset($input['observaciones']) ? trim($input['observaciones']) : null;
$beneficiarios = isset($input['beneficiarios']) && is_array($input['beneficiarios']) ? $input['beneficiarios'] : array();

if ($eventoId <= 0) {
    api_error('Debe indicar un evento válido.', 400);
}

if ($filialId <= 0) {
    api_error('Debe seleccionar la filial o sede donde retirará el beneficio.', 400);
}

if (empty($beneficiarios)) {
    api_error('Debe seleccionar al menos un hijo/beneficiario para la inscripción.', 400);
}

try {
    $pdo = db_connect();

    // 2. Verificar existencia y estado del evento
    $stmtEvento = $pdo->prepare("SELECT * FROM `eventos` WHERE `id` = :id LIMIT 1");
    $stmtEvento->execute(array(':id' => $eventoId));
    $evento = $stmtEvento->fetch();

    if (!$evento) {
        api_error('El evento seleccionado no existe.', 404);
    }

    if ($evento['estado'] !== 'abierto') {
        api_error('Las inscripciones para este evento se encuentran cerradas.', 400);
    }

    // 3. Verificar que el afiliado no esté ya inscripto
    $stmtCheck = $pdo->prepare("SELECT `id`, `codigo_comprobante` FROM `evento_inscripciones` 
                                WHERE `evento_id` = :evento_id AND `afiliado_id` = :afiliado_id 
                                LIMIT 1");
    $stmtCheck->execute(array(
        ':evento_id'   => $eventoId,
        ':afiliado_id' => $afiliadoId
    ));
    $existente = $stmtCheck->fetch();

    if ($existente) {
        api_error('Ya poseés una inscripción confirmada para este evento (Comprobante: ' . $existente['codigo_comprobante'] . ').', 409);
    }

    // 4. Verificar filial de retiro
    $stmtFilial = $pdo->prepare("SELECT `id`, `nombre`, `direccion` FROM `filiales_retiro` WHERE `id` = :id AND `activo` = 1 LIMIT 1");
    $stmtFilial->execute(array(':id' => $filialId));
    $filial = $stmtFilial->fetch();

    if (!$filial) {
        api_error('La sede de retiro seleccionada no es válida.', 400);
    }

    // 5. Validar cada beneficiario (pertenencia al afiliado y rango de edad)
    $stmtFam = $pdo->prepare("SELECT `id`, `nombre`, `apellido`, `fecha_nacimiento` 
                              FROM `afiliado_familiares` 
                              WHERE `id` = :id AND `afiliado_id` = :afiliado_id 
                              LIMIT 1");

    $beneficiariosValidados = array();
    $familiaresIdsUnicos = array();

    foreach ($beneficiarios as $b) {
        $famId = isset($b['familiar_id']) ? (int)$b['familiar_id'] : 0;
        if ($famId <= 0 || in_array($famId, $familiaresIdsUnicos)) {
            continue;
        }

        $stmtFam->execute(array(
            ':id'          => $famId,
            ':afiliado_id' => $afiliadoId
        ));
        $fam = $stmtFam->fetch();

        if (!$fam) {
            api_error('Uno de los familiares seleccionados no corresponde a tu grupo familiar registrado.', 403);
        }

        // Validar edad respecto a las reglas del evento
        $fechaNac = new DateTime($fam['fecha_nacimiento']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNac)->y;

        $edadMin = (int)$evento['edad_minima_hijo'];
        $edadMax = (int)$evento['edad_maxima_hijo'];

        if ($edad < $edadMin || $edad > $edadMax) {
            api_error('El familiar ' . $fam['nombre'] . ' ' . $fam['apellido'] . ' tiene ' . $edad . ' años y no cumple con el rango de edad requerido (' . $edadMin . ' a ' . $edadMax . ' años).', 400);
        }

        $talle = isset($b['talle']) ? trim($b['talle']) : null;
        if ($evento['requiere_talle'] && empty($talle)) {
            api_error('Debe indicar el talle para ' . $fam['nombre'] . '.', 400);
        }

        $nivel = isset($b['nivel_escolar']) ? trim($b['nivel_escolar']) : null;
        if ($evento['requiere_escolaridad'] && empty($nivel)) {
            api_error('Debe indicar el nivel de escolaridad para ' . $fam['nombre'] . '.', 400);
        }

        $familiaresIdsUnicos[] = $famId;
        $beneficiariosValidados[] = array(
            'familiar_id'   => $famId,
            'nombre'        => $fam['nombre'] . ' ' . $fam['apellido'],
            'talle'         => $talle,
            'nivel_escolar' => $nivel,
            'observacion'   => isset($b['observacion']) ? trim($b['observacion']) : null
        );
    }

    if (empty($beneficiariosValidados)) {
        api_error('No se seleccionaron beneficiarios válidos para la inscripción.', 400);
    }

    // 6. Generar código de comprobante único
    $prefijo = strtoupper($evento['tipo'] === 'utiles' ? 'UTILES' : ($evento['tipo'] === 'juguetes' ? 'JUGUETES' : 'EVENTO'));
    $codigoComprobante = sprintf('%s-%s-%s', $prefijo, date('Y'), strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)));

    // 7. Insertar en base de datos con transacción atómica
    $pdo->beginTransaction();

    $stmtInsertInsc = $pdo->prepare("INSERT INTO `evento_inscripciones` (
        `evento_id`, `afiliado_id`, `filial_retiro_id`, `codigo_comprobante`, `estado`, `observaciones`, `created_at`
    ) VALUES (
        :evento_id, :afiliado_id, :filial_id, :codigo, 'solicitado', :obs, NOW()
    )");

    $stmtInsertInsc->execute(array(
        ':evento_id'   => $eventoId,
        ':afiliado_id' => $afiliadoId,
        ':filial_id'   => $filialId,
        ':codigo'      => $codigoComprobante,
        ':obs'         => $observaciones
    ));

    $inscripcionId = (int)$pdo->lastInsertId();

    $stmtInsertBen = $pdo->prepare("INSERT INTO `evento_inscripcion_beneficiarios` (
        `inscripcion_id`, `familiar_id`, `talle`, `nivel_escolar`, `observacion`, `created_at`
    ) VALUES (
        :inscripcion_id, :familiar_id, :talle, :nivel_escolar, :observacion, NOW()
    )");

    foreach ($beneficiariosValidados as $bv) {
        $stmtInsertBen->execute(array(
            ':inscripcion_id' => $inscripcionId,
            ':familiar_id'    => $bv['familiar_id'],
            ':talle'          => $bv['talle'],
            ':nivel_escolar'  => $bv['nivel_escolar'],
            ':observacion'    => $bv['observacion']
        ));
    }

    $pdo->commit();

    api_response(array(
        'inscripcion_id'         => $inscripcionId,
        'codigo_comprobante'     => $codigoComprobante,
        'evento_titulo'          => $evento['titulo'],
        'filial_nombre'          => $filial['nombre'],
        'filial_direccion'       => $filial['direccion'],
        'estado'                 => 'solicitado',
        'cantidad_beneficiarios' => count($beneficiariosValidados),
        'mensaje'                => '¡Inscripción confirmada! Presentá tu comprobante digital y carnet al momento del retiro.'
    ), 201);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    api_error('Error al procesar la inscripción: ' . $e->getMessage(), 500);
}
