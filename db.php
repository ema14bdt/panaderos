<?php

/**
 * Base de Datos: Conexión PDO centralizada
 * Sindicato de Obreros Panaderos de Lanús
 */

function db_config() {
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $configFile = __DIR__ . '/private-content/database.local.php';
    if (file_exists($configFile)) {
        $loaded = include $configFile;
        if (is_array($loaded)) {
            $config = $loaded;
            return $config;
        }
    }

    $exampleFile = __DIR__ . '/private-content/database.local.example.php';
    if (file_exists($exampleFile)) {
        $loaded = include $exampleFile;
        if (is_array($loaded)) {
            $config = $loaded;
            return $config;
        }
    }

    $config = array(
        'db_host'    => '127.0.0.1',
        'db_port'    => 3306,
        'db_name'    => 'panaderos_db',
        'db_user'    => 'root',
        'db_pass'    => '',
        'db_charset' => 'utf8mb4'
    );
    return $config;
}

function db_connect() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $cfg = db_config();
    $host = isset($cfg['db_host']) ? $cfg['db_host'] : '127.0.0.1';
    $port = isset($cfg['db_port']) ? (int)$cfg['db_port'] : 3306;
    $dbname = isset($cfg['db_name']) ? $cfg['db_name'] : 'panaderos_db';
    $charset = isset($cfg['db_charset']) ? $cfg['db_charset'] : 'utf8mb4';
    $user = isset($cfg['db_user']) ? $cfg['db_user'] : 'root';
    $pass = isset($cfg['db_pass']) ? $cfg['db_pass'] : '';

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $dbname, $charset);

    $options = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        throw new RuntimeException('No se pudo conectar con la base de datos.');
    }
}
