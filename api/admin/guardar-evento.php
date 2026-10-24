<?php

/**
 * API Administrador: Crear o Editar un Evento
 * POST /api/admin/guardar-evento.php
 * Header: Authorization: Bearer <auth_token> (Requiere rol admin)
 * Body:
 *   {
 *     "id": 1, // Opcional: si viene, actualiza; si no viene, crea nuevo
 *     "titulo": "Entrega de Útiles 2027",
 *     "descripcion": "...",
 *     "tipo": "utiles|juguetes|turismo|general",
 *     "fecha_inicio_inscripcion": "2027-02-01",
 *     "fecha_fin_inscripcion": "2027-03-05",
 *     "fecha_entrega": "2027-03-10",
 *     "edad_minima_hijo": 3,
 *     "edad_maxima_hijo": 17,
 *     "requiere_escolaridad": 1,
 *     "requiere_talle": 1,
 *     "estado": "borrador|abierto|cerrado|finalizado"
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

function normalizar_fecha_arg($str) {
    if (empty($str)) return null;
    $s = trim($str);
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $s, $m)) {
        return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
        return $s;
    }
    return $s;
}

$id                     = isset($input['id']) && is_numeric($input['id']) ? (int)$input['id'] : null;
$titulo                 = isset($input['titulo']) ? trim($input['titulo']) : '';
$descripcion            = isset($input['descripcion']) ? trim($input['descripcion']) : '';
$tipo                   = isset($input['tipo']) ? trim($input['tipo']) : 'general';
$fechaInicioInscripcion = isset($input['fecha_inicio_inscripcion']) ? normalizar_fecha_arg($input['fecha_inicio_inscripcion']) : '';
$fechaFinInscripcion    = isset($input['fecha_fin_inscripcion']) ? normalizar_fecha_arg($input['fecha_fin_inscripcion']) : '';
$fechaEntrega           = !empty($input['fecha_entrega']) ? normalizar_fecha_arg($input['fecha_entrega']) : null;
$edadMinimaHijo         = isset($input['edad_minima_hijo']) ? (int)$input['edad_minima_hijo'] : 0;
$edadMaximaHijo         = isset($input['edad_maxima_hijo']) ? (int)$input['edad_maxima_hijo'] : 18;
$requiereEscolaridad    = !empty($input['requiere_escolaridad']) ? 1 : 0;
$requiereTalle          = !empty($input['requiere_talle']) ? 1 : 0;
$estado                 = isset($input['estado']) ? trim($input['estado']) : 'abierto';

if ($titulo === '') {
    api_error('El título del evento es obligatorio.', 400);
}

if ($fechaInicioInscripcion === '' || $fechaFinInscripcion === '') {
    api_error('Las fechas de inicio y fin de inscripción son obligatorias.', 400);
}

$tiposValidos = array('utiles', 'juguetes', 'turismo', 'general');
if (!in_array($tipo, $tiposValidos, true)) {
    $tipo = 'general';
}

$estadosValidos = array('borrador', 'abierto', 'cerrado', 'finalizado');
if (!in_array($estado, $estadosValidos, true)) {
    $estado = 'abierto';
}

try {
    $pdo = db_connect();

    if ($id !== null && $id > 0) {
        // Actualizar evento existente
        $stmt = $pdo->prepare("UPDATE `eventos` SET
            `titulo` = :titulo,
            `descripcion` = :descripcion,
            `tipo` = :tipo,
            `fecha_inicio_inscripcion` = :fecha_inicio,
            `fecha_fin_inscripcion` = :fecha_fin,
            `fecha_entrega` = :fecha_entrega,
            `edad_minima_hijo` = :edad_min,
            `edad_maxima_hijo` = :edad_max,
            `requiere_escolaridad` = :req_esc,
            `requiere_talle` = :req_talle,
            `estado` = :estado
            WHERE `id` = :id");

        $stmt->execute(array(
            ':titulo'        => $titulo,
            ':descripcion'   => $descripcion,
            ':tipo'          => $tipo,
            ':fecha_inicio'  => $fechaInicioInscripcion,
            ':fecha_fin'     => $fechaFinInscripcion,
            ':fecha_entrega' => $fechaEntrega,
            ':edad_min'      => $edadMinimaHijo,
            ':edad_max'      => $edadMaximaHijo,
            ':req_esc'       => $requiereEscolaridad,
            ':req_talle'     => $requiereTalle,
            ':estado'        => $estado,
            ':id'            => $id,
        ));
        $eventoId = $id;
        $mensaje = 'Evento actualizado exitosamente.';
    } else {
        // Crear nuevo evento
        $stmt = $pdo->prepare("INSERT INTO `eventos` (
            `titulo`, `descripcion`, `tipo`,
            `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, `fecha_entrega`,
            `edad_minima_hijo`, `edad_maxima_hijo`,
            `requiere_escolaridad`, `requiere_talle`, `estado`
        ) VALUES (
            :titulo, :descripcion, :tipo,
            :fecha_inicio, :fecha_fin, :fecha_entrega,
            :edad_min, :edad_max,
            :req_esc, :req_talle, :estado
        )");

        $stmt->execute(array(
            ':titulo'        => $titulo,
            ':descripcion'   => $descripcion,
            ':tipo'          => $tipo,
            ':fecha_inicio'  => $fechaInicioInscripcion,
            ':fecha_fin'     => $fechaFinInscripcion,
            ':fecha_entrega' => $fechaEntrega,
            ':edad_min'      => $edadMinimaHijo,
            ':edad_max'      => $edadMaximaHijo,
            ':req_esc'       => $requiereEscolaridad,
            ':req_talle'     => $requiereTalle,
            ':estado'        => $estado,
        ));
        $eventoId = (int)$pdo->lastInsertId();
        $mensaje = 'Evento creado exitosamente.';
    }

    // Devolver el evento actualizado
    $stmtFetch = $pdo->prepare("SELECT * FROM `eventos` WHERE `id` = :id");
    $stmtFetch->execute(array(':id' => $eventoId));
    $evento = $stmtFetch->fetch();

    api_response(array(
        'mensaje' => $mensaje,
        'evento'  => $evento,
    ));
} catch (Exception $e) {
    api_error('Error al guardar evento: ' . $e->getMessage(), 500);
}
