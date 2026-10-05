<?php

/**
 * Migración y Creación de Tablas de Base de Datos
 * Ejecución vía terminal: php tools/migrate-db.php
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo puede ejecutarse en modo CLI.\n");
    exit(1);
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = dirname(__DIR__);
require_once $baseDir . '/db.php';

$cfg = db_config();
$host = isset($cfg['db_host']) ? $cfg['db_host'] : '127.0.0.1';
$port = isset($cfg['db_port']) ? (int)$cfg['db_port'] : 3306;
$dbname = isset($cfg['db_name']) ? $cfg['db_name'] : 'panaderos_db';
$charset = isset($cfg['db_charset']) ? $cfg['db_charset'] : 'utf8mb4';
$user = isset($cfg['db_user']) ? $cfg['db_user'] : 'root';
$pass = isset($cfg['db_pass']) ? $cfg['db_pass'] : '';

echo "=== MIGRACIÓN DE BASE DE DATOS: PANADEROS ===\n";
echo "Host: " . $host . ":" . $port . "\n";
echo "Base de datos: " . $dbname . "\n";
echo "Usuario: " . $user . "\n\n";

// 1. Conexión inicial al servidor MySQL para asegurar que la BD exista
try {
    $initDsn = sprintf('mysql:host=%s;port=%d;charset=%s', $host, $port, $charset);
    $pdoInit = new PDO($initDsn, $user, $pass, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
    $pdoInit->exec(sprintf("CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s_unicode_ci", $dbname, $charset, $charset));
    echo "  [OK] Base de datos `" . $dbname . "` verificada/creada.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "  [ERROR] No se pudo conectar al servidor MySQL: " . $e->getMessage() . "\n");
    exit(1);
}

// 2. Conexión a la BD específica y ejecución del schema.sql
try {
    $pdo = db_connect();
} catch (Exception $e) {
    fwrite(STDERR, "  [ERROR] Error al conectar a la base `" . $dbname . "`: " . $e->getMessage() . "\n");
    exit(1);
}

$schemaFile = $baseDir . '/database/schema.sql';
if (!file_exists($schemaFile)) {
    fwrite(STDERR, "  [ERROR] No se encontró el archivo schema.sql en database/\n");
    exit(1);
}

$sql = file_get_contents($schemaFile);
if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, "  [ERROR] El archivo schema.sql está vacío o no se pudo leer.\n");
    exit(1);
}

echo "Ejecutando schema.sql...\n";
try {
    $pdo->exec($sql);
    echo "  [OK] Estructura de tablas ejecutada exitosamente.\n\n";
} catch (PDOException $e) {
    fwrite(STDERR, "  [ERROR] Falló la ejecución del schema: " . $e->getMessage() . "\n");
    exit(1);
}

// 3. Verificación de tablas existentes
$tables = array(
    'afiliados',
    'afiliado_familiares',
    'filiales_retiro',
    'eventos',
    'evento_inscripciones',
    'evento_inscripcion_beneficiarios',
    'beneficios',
    'dispositivos_push'
);

echo "Verificando tablas en `" . $dbname . "`:\n";
foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t));
        $exists = $stmt->fetchColumn();
        if ($exists) {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `" . $t . "`");
            $rows = $countStmt->fetchColumn();
            echo "  [OK] Tabla `" . $t . "` lista (registros: " . $rows . ")\n";
        } else {
            echo "  [WARN] Tabla `" . $t . "` no encontrada.\n";
        }
    } catch (PDOException $e) {
        echo "  [ERROR] Al verificar `" . $t . "`: " . $e->getMessage() . "\n";
    }
}

echo "\nMigración completada con éxito.\n";
