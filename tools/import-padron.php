<?php

/**
 * Importador de Padrón de Afiliados desde CSV / Excel (CSV)
 * Uso: php tools/import-padron.php ruta/al/archivo.csv
 * Crear plantilla: php tools/import-padron.php --create-template
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo puede ejecutarse en modo CLI.\n");
    exit(1);
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = dirname(__DIR__);
require_once $baseDir . '/db.php';

// Modo: Crear plantilla CSV de ejemplo
if ($argc > 1 && $argv[1] === '--create-template') {
    $templatePath = $baseDir . '/padron-ejemplo.csv';
    $sampleData = "dni;numero_afiliado;apellido;nombre;empresa_panaderia;categoria_laboral;estado;fecha_vencimiento\n"
                . "28456123;10452;Perez;Juan Carlos;Panadería La Espiga;Maestro Panadero;activo;2027-12-31\n"
                . "32987654;10453;Gomez;Maria Elena;Panificadora Lanús;Oficial;activo;2027-12-31\n"
                . "35123456;10454;Rodriguez;Lucas;Confitería del Sur;Ayudante;activo;2027-12-31\n";
    file_put_contents($templatePath, $sampleData);
    echo "Plantilla de ejemplo creada en: " . $templatePath . "\n";
    exit(0);
}

if ($argc < 2) {
    echo "Uso:\n";
    echo "  php tools/import-padron.php <archivo.csv>\n";
    echo "  php tools/import-padron.php --create-template (genera un CSV de ejemplo)\n";
    exit(1);
}

$csvPath = $argv[1];
if (!file_exists($csvPath)) {
    fwrite(STDERR, "[ERROR] El archivo no existe: " . $csvPath . "\n");
    exit(1);
}

$handle = fopen($csvPath, 'r');
if (!$handle) {
    fwrite(STDERR, "[ERROR] No se pudo abrir el archivo CSV.\n");
    exit(1);
}

// 1. Detectar delimitador leyendo la primera línea
$firstLine = fgets($handle);
rewind($handle);
$delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

// 2. Leer cabeceras
$rawHeaders = fgetcsv($handle, 0, $delimiter);
if (!$rawHeaders) {
    fwrite(STDERR, "[ERROR] El archivo CSV está vacío.\n");
    fclose($handle);
    exit(1);
}

$headerMap = array();
foreach ($rawHeaders as $idx => $header) {
    $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $header))));
    $headerMap[$clean] = $idx;
}

// Mapear nombres de columnas comunes
$colDni = null;
$colNumero = null;
$colNombre = null;
$colApellido = null;
$colEmpresa = null;
$colCategoria = null;
$colEstado = null;
$colVencimiento = null;

foreach ($headerMap as $name => $idx) {
    if (in_array($name, array('dni', 'documento', 'nro_doc', 'ndoc'))) $colDni = $idx;
    if (in_array($name, array('numero_afiliado', 'nro_afiliado', 'afiliado', 'carnet', 'nro_carnet'))) $colNumero = $idx;
    if (in_array($name, array('nombre', 'nombres'))) $colNombre = $idx;
    if (in_array($name, array('apellido', 'apellidos'))) $colApellido = $idx;
    if (in_array($name, array('empresa', 'panaderia', 'empresa_panaderia', 'lugar_trabajo'))) $colEmpresa = $idx;
    if (in_array($name, array('categoria', 'categoria_laboral', 'puesto'))) $colCategoria = $idx;
    if (in_array($name, array('estado', 'condicion', 'situacion'))) $colEstado = $idx;
    if (in_array($name, array('vencimiento', 'fecha_vencimiento', 'vigencia'))) $colVencimiento = $idx;
}

if ($colDni === null || $colNumero === null || $colNombre === null || $colApellido === null) {
    fwrite(STDERR, "[ERROR] El CSV debe contener al menos las columnas: DNI, NUMERO_AFILIADO, NOMBRE y APELLIDO.\n");
    fwrite(STDERR, "Columnas detectadas: " . implode(', ', array_keys($headerMap)) . "\n");
    fclose($handle);
    exit(1);
}

// 3. Conexión a la BD
try {
    $pdo = db_connect();
} catch (Exception $e) {
    fwrite(STDERR, "[ERROR] No se pudo conectar a la base de datos: " . $e->getMessage() . "\n");
    fclose($handle);
    exit(1);
}

// Sentencia con ON DUPLICATE KEY UPDATE para preservar contraseñas y emails si ya se registraron
$sql = "INSERT INTO `afiliados` (
            `dni`, `numero_afiliado`, `nombre`, `apellido`, 
            `empresa_panaderia`, `categoria_laboral`, `estado`, `fecha_vencimiento`
        ) VALUES (
            :dni, :numero_afiliado, :nombre, :apellido, 
            :empresa, :categoria, :estado, :vencimiento
        ) ON DUPLICATE KEY UPDATE
            `nombre` = VALUES(`nombre`),
            `apellido` = VALUES(`apellido`),
            `empresa_panaderia` = VALUES(`empresa_panaderia`),
            `categoria_laboral` = VALUES(`categoria_laboral`),
            `estado` = VALUES(`estado`),
            `fecha_vencimiento` = IFNULL(VALUES(`fecha_vencimiento`), `fecha_vencimiento`)";

$stmt = $pdo->prepare($sql);

$totalRows = 0;
$imported = 0;
$errors = 0;

$pdo->beginTransaction();

while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
    $totalRows++;
    
    // Limpiar DNI: solo dígitos
    $rawDni = isset($row[$colDni]) ? trim($row[$colDni]) : '';
    $dni = preg_replace('/[^0-9]/', '', $rawDni);
    
    $numeroAfiliado = isset($row[$colNumero]) ? trim($row[$colNumero]) : '';
    $nombre = isset($row[$colNombre]) ? trim($row[$colNombre]) : '';
    $apellido = isset($row[$colApellido]) ? trim($row[$colApellido]) : '';
    $empresa = ($colEmpresa !== null && isset($row[$colEmpresa])) ? trim($row[$colEmpresa]) : null;
    $categoria = ($colCategoria !== null && isset($row[$colCategoria])) ? trim($row[$colCategoria]) : null;
    
    $rawEstado = ($colEstado !== null && isset($row[$colEstado])) ? strtolower(trim($row[$colEstado])) : 'activo';
    $estado = in_array($rawEstado, array('activo', 'inactivo', 'suspendido')) ? $rawEstado : 'activo';
    
    $rawVenc = ($colVencimiento !== null && isset($row[$colVencimiento])) ? trim($row[$colVencimiento]) : null;
    $vencimiento = null;
    if ($rawVenc !== null && $rawVenc !== '') {
        $parsedTime = strtotime($rawVenc);
        if ($parsedTime !== false) {
            $vencimiento = date('Y-m-d', $parsedTime);
        }
    }

    if (empty($dni) || empty($numeroAfiliado) || empty($nombre) || empty($apellido)) {
        fwrite(STDERR, "  [AVISO] Fila " . $totalRows . " ignorada por campos obligatorios faltantes.\n");
        $errors++;
        continue;
    }

    try {
        $stmt->execute(array(
            ':dni'              => $dni,
            ':numero_afiliado'   => $numeroAfiliado,
            ':nombre'           => $nombre,
            ':apellido'         => $apellido,
            ':empresa'          => $empresa,
            ':categoria'        => $categoria,
            ':estado'           => $estado,
            ':vencimiento'      => $vencimiento
        ));
        $imported++;
    } catch (PDOException $e) {
        fwrite(STDERR, "  [ERROR] Fila " . $totalRows . " (DNI " . $dni . "): " . $e->getMessage() . "\n");
        $errors++;
    }
}

fclose($handle);
$pdo->commit();

echo "\n=== RESULTADO DE IMPORTACIÓN DE PADRÓN ===\n";
echo "Total filas leídas: " . $totalRows . "\n";
echo "Afiliados insertados/actualizados: " . $imported . "\n";
echo "Filas con errores: " . $errors . "\n";
echo "==========================================\n";
