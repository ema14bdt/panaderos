<?php

/**
 * API Administrador: Listado detallado de Afiliados (Padrón y Usuarios de App)
 * GET /api/admin/afiliados.php
 * Header: Authorization: Bearer <auth_token> (Requiere rol admin)
 * Query Params:
 *   ?search=xxx
 *   ?filtro=todos|app|padron
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

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filtro = isset($_GET['filtro']) ? trim($_GET['filtro']) : 'todos';

try {
    $pdo = db_connect();

    $sql = "SELECT a.`id`, a.`dni`, a.`numero_afiliado`, a.`nombre`, a.`apellido`, 
                   a.`empresa_panaderia`, a.`categoria_laboral`, a.`estado`, a.`rol`, 
                   a.`email`, a.`telefono`, a.`foto_url`, a.`fecha_registro`, a.`created_at`,
                   (CASE WHEN a.`password_hash` IS NOT NULL AND a.`email` IS NOT NULL THEN 1 ELSE 0 END) AS `registrado_en_app`,
                   COUNT(f.`id`) AS `total_familiares`
            FROM `afiliados` a
            LEFT JOIN `afiliado_familiares` f ON a.`id` = f.`afiliado_id`
            WHERE 1=1 ";

    $params = array();

    if ($search !== '') {
        $sql .= " AND (a.`dni` LIKE :search OR a.`numero_afiliado` LIKE :search 
                  OR a.`nombre` LIKE :search OR a.`apellido` LIKE :search 
                  OR a.`empresa_panaderia` LIKE :search OR a.`email` LIKE :search) ";
        $params[':search'] = '%' . $search . '%';
    }

    if ($filtro === 'app') {
        $sql .= " AND a.`password_hash` IS NOT NULL AND a.`email` IS NOT NULL ";
    } elseif ($filtro === 'padron') {
        $sql .= " AND (a.`password_hash` IS NULL OR a.`email` IS NULL) ";
    }

    $sql .= " GROUP BY a.`id` ORDER BY a.`apellido` ASC, a.`nombre` ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $afiliados = $stmt->fetchAll();

    // Formatear datos para respuesta
    $list = array();
    foreach ($afiliados as $row) {
        $list[] = array(
            'id'                 => (int)$row['id'],
            'dni'                => $row['dni'],
            'numero_afiliado'    => $row['numero_afiliado'],
            'nombre'             => $row['nombre'],
            'apellido'           => $row['apellido'],
            'nombre_completo'    => trim($row['nombre'] . ' ' . $row['apellido']),
            'empresa_panaderia'  => $row['empresa_panaderia'],
            'categoria_laboral'  => $row['categoria_laboral'],
            'estado'             => $row['estado'],
            'rol'                => $row['rol'],
            'email'              => $row['email'],
            'telefono'           => $row['telefono'],
            'foto_url'           => $row['foto_url'],
            'registrado_en_app'  => (bool)$row['registrado_en_app'],
            'fecha_registro'     => $row['fecha_registro'],
            'total_familiares'   => (int)$row['total_familiares'],
        );
    }

    api_response(array(
        'total'     => count($list),
        'filtro'    => $filtro,
        'afiliados' => $list
    ));
} catch (Exception $e) {
    api_error('Error al consultar lista de afiliados: ' . $e->getMessage(), 500);
}
