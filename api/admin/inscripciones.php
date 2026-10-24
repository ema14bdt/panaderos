<?php

/**
 * API Administrador: Listado detallado de Inscripciones a Eventos
 * GET /api/admin/inscripciones.php
 * Header: Authorization: Bearer <auth_token> (Requiere rol admin)
 * Query Params:
 *   ?evento_id=1
 *   ?estado=solicitado|entregado|todos
 *   ?search=xxx
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Método no permitido. Utilice GET.', 405);
}

// 1. Validar autenticación de administrador
$admin = api_require_auth();
if (!isset($admin['rol']) || $admin['rol'] !== 'admin') {
    api_error('Acceso restringido a administradores sindicales.', 403);
}

$eventoId = isset($_GET['evento_id']) && is_numeric($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
$estado   = isset($_GET['estado']) ? trim($_GET['estado']) : 'todos';
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $pdo = db_connect();

    $sql = "SELECT i.*, 
                   a.`nombre` AS `afiliado_nombre`, 
                   a.`apellido` AS `afiliado_apellido`, 
                   a.`dni` AS `afiliado_dni`, 
                   a.`numero_afiliado`, 
                   a.`telefono` AS `afiliado_telefono`,
                   a.`empresa_panaderia`,
                   e.`titulo` AS `evento_titulo`, 
                   e.`tipo` AS `evento_tipo`,
                   f.`nombre` AS `filial_nombre`
            FROM `evento_inscripciones` i
            JOIN `afiliados` a ON i.`afiliado_id` = a.`id`
            JOIN `eventos` e ON i.`evento_id` = e.`id`
            LEFT JOIN `filiales_retiro` f ON i.`filial_retiro_id` = f.`id`
            WHERE 1=1 ";

    $params = array();

    if ($eventoId > 0) {
        $sql .= " AND i.`evento_id` = :evento_id ";
        $params[':evento_id'] = $eventoId;
    }

    if ($estado !== '' && $estado !== 'todos') {
        $sql .= " AND i.`estado` = :estado ";
        $params[':estado'] = $estado;
    }

    if ($search !== '') {
        $sql .= " AND (i.`codigo_comprobante` LIKE :search 
                  OR a.`dni` LIKE :search 
                  OR a.`numero_afiliado` LIKE :search 
                  OR a.`nombre` LIKE :search 
                  OR a.`apellido` LIKE :search) ";
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY i.`id` DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscripciones = $stmt->fetchAll();

    // Obtener beneficiarios para cada inscripción
    $result = array();
    foreach ($inscripciones as $row) {
        $benStmt = $pdo->prepare("SELECT b.*, f.`nombre`, f.`apellido`, f.`parentesco`, 
                                         f.`fecha_nacimiento`, f.`dni` AS `familiar_dni`
                                  FROM `evento_inscripcion_beneficiarios` b
                                  JOIN `afiliado_familiares` f ON b.`familiar_id` = f.`id`
                                  WHERE b.`inscripcion_id` = :insc_id");
        $benStmt->execute(array(':insc_id' => $row['id']));
        $beneficiariosRows = $benStmt->fetchAll();

        $beneficiarios = array();
        foreach ($beneficiariosRows as $b) {
            $edad = '';
            if (!empty($b['fecha_nacimiento'])) {
                try {
                    $birthDate = new DateTime($b['fecha_nacimiento']);
                    $now = new DateTime();
                    $edad = $now->diff($birthDate)->y . ' años';
                } catch (Exception $e) {}
            }

            $beneficiarios[] = array(
                'id'            => (int)$b['id'],
                'nombre'        => $b['nombre'],
                'apellido'      => $b['apellido'],
                'nombre_completo' => trim($b['nombre'] . ' ' . $b['apellido']),
                'parentesco'    => $b['parentesco'],
                'edad'          => $edad,
                'talle'         => $b['talle'],
                'nivel_escolar' => $b['nivel_escolar'],
                'observacion'   => $b['observacion'],
            );
        }

        $result[] = array(
            'id'                      => (int)$row['id'],
            'evento_id'               => (int)$row['evento_id'],
            'evento_titulo'           => $row['evento_titulo'],
            'evento_tipo'             => $row['evento_tipo'],
            'afiliado_id'             => (int)$row['afiliado_id'],
            'afiliado_nombre'         => $row['afiliado_nombre'],
            'afiliado_apellido'       => $row['afiliado_apellido'],
            'afiliado_nombre_completo'=> trim($row['afiliado_nombre'] . ' ' . $row['afiliado_apellido']),
            'afiliado_dni'            => $row['afiliado_dni'],
            'numero_afiliado'         => $row['numero_afiliado'],
            'afiliado_telefono'       => $row['afiliado_telefono'],
            'empresa_panaderia'       => $row['empresa_panaderia'],
            'filial_nombre'           => $row['filial_nombre'] ? $row['filial_nombre'] : 'Sede Central Lanús (Llavallol 902)',
            'codigo_comprobante'      => $row['codigo_comprobante'],
            'estado'                  => $row['estado'],
            'fecha_entrega_realizada' => $row['fecha_entrega_realizada'],
            'observaciones'           => $row['observaciones'],
            'created_at'              => $row['created_at'],
            'total_beneficiarios'     => count($beneficiarios),
            'beneficiarios'           => $beneficiarios,
        );
    }

    api_response(array(
        'total'         => count($result),
        'inscripciones' => $result
    ));
} catch (Exception $e) {
    api_error('Error al consultar inscripciones: ' . $e->getMessage(), 500);
}
