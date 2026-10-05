-- =============================================================================
-- BASE DE DATOS: Sindicato de Obreros Panaderos de Lanús
-- Módulo: Padrón de Afiliados, Carnet Digital, Eventos y Beneficios
-- Motor: MySQL 5.7+ / MariaDB 10.3+
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. Tabla: afiliados (Padrón precargado y cuentas activas de la App)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `afiliados` (
    `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dni`                     VARCHAR(20) NOT NULL,
    `numero_afiliado`         VARCHAR(30) NOT NULL,
    `nombre`                  VARCHAR(100) NOT NULL,
    `apellido`                VARCHAR(100) NOT NULL,
    `empresa_panaderia`       VARCHAR(150) NULL DEFAULT NULL,
    `categoria_laboral`       VARCHAR(100) NULL DEFAULT NULL,
    `estado`                  ENUM('activo', 'inactivo', 'suspendido') NOT NULL DEFAULT 'activo',
    `fecha_afiliacion`        DATE NULL DEFAULT NULL,
    `fecha_vencimiento`       DATE NULL DEFAULT NULL,
    
    -- Campos completados durante la registración en la App Móvil:
    `email`                   VARCHAR(150) NULL DEFAULT NULL,
    `telefono`                VARCHAR(50) NULL DEFAULT NULL,
    `password_hash`           VARCHAR(255) NULL DEFAULT NULL,
    `foto_url`                VARCHAR(255) NULL DEFAULT NULL,
    `auth_token`              VARCHAR(255) NULL DEFAULT NULL,
    `auth_token_expires`      DATETIME NULL DEFAULT NULL,
    `reset_token`             VARCHAR(255) NULL DEFAULT NULL,
    `reset_token_expires`     DATETIME NULL DEFAULT NULL,
    `fecha_registro`          DATETIME NULL DEFAULT NULL,
    `ultimo_acceso`           DATETIME NULL DEFAULT NULL,
    
    `created_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_afiliados_dni` (`dni`),
    UNIQUE KEY `uk_afiliados_numero` (`numero_afiliado`),
    UNIQUE KEY `uk_afiliados_email` (`email`),
    KEY `idx_afiliados_auth_token` (`auth_token`),
    KEY `idx_afiliados_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Tabla: afiliado_familiares (Hijos y cónyuge a cargo)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `afiliado_familiares` (
    `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `afiliado_id`             INT UNSIGNED NOT NULL,
    `nombre`                  VARCHAR(100) NOT NULL,
    `apellido`                VARCHAR(100) NOT NULL,
    `dni`                     VARCHAR(20) NULL DEFAULT NULL,
    `fecha_nacimiento`        DATE NOT NULL,
    `parentesco`              ENUM('hijo', 'hija', 'conyuge', 'otro') NOT NULL DEFAULT 'hijo',
    `escolaridad`             ENUM('ninguna', 'maternal', 'jardin', 'primaria', 'secundaria', 'terciario', 'universitario') NOT NULL DEFAULT 'ninguna',
    
    `created_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    KEY `idx_familiares_afiliado` (`afiliado_id`),
    CONSTRAINT `fk_familiares_afiliado` FOREIGN KEY (`afiliado_id`) 
        REFERENCES `afiliados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Tabla: filiales_retiro (Puntos de entrega físicos para eventos)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `filiales_retiro` (
    `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nombre`                  VARCHAR(150) NOT NULL,
    `direccion`               VARCHAR(200) NOT NULL,
    `localidad`               VARCHAR(100) NOT NULL DEFAULT 'Lanús',
    `telefono`                VARCHAR(50) NULL DEFAULT NULL,
    `horario_atencion`        VARCHAR(150) NULL DEFAULT NULL,
    `activo`                  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Tabla: eventos (Campañas de entrega de útiles, juguetes del día del niño, etc.)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `eventos` (
    `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `titulo`                  VARCHAR(200) NOT NULL,
    `descripcion`             TEXT NULL DEFAULT NULL,
    `tipo`                    ENUM('utiles', 'juguetes', 'turismo', 'general') NOT NULL DEFAULT 'general',
    `imagen_url`              VARCHAR(255) NULL DEFAULT NULL,
    `fecha_inicio_inscripcion` DATE NOT NULL,
    `fecha_fin_inscripcion`   DATE NOT NULL,
    `fecha_entrega`           DATE NULL DEFAULT NULL,
    `edad_minima_hijo`        TINYINT UNSIGNED NULL DEFAULT 0,
    `edad_maxima_hijo`        TINYINT UNSIGNED NULL DEFAULT 18,
    `requiere_escolaridad`    TINYINT(1) NOT NULL DEFAULT 0,
    `requiere_talle`          TINYINT(1) NOT NULL DEFAULT 0,
    `estado`                  ENUM('borrador', 'abierto', 'cerrado', 'finalizado') NOT NULL DEFAULT 'borrador',
    
    `created_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    KEY `idx_eventos_estado` (`estado`),
    KEY `idx_eventos_fechas` (`fecha_inicio_inscripcion`, `fecha_fin_inscripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. Tabla: evento_inscripciones (Solicitudes de inscripción por afiliado)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `evento_inscripciones` (
    `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `evento_id`               INT UNSIGNED NOT NULL,
    `afiliado_id`             INT UNSIGNED NOT NULL,
    `filial_retiro_id`        INT UNSIGNED NULL DEFAULT NULL,
    `codigo_comprobante`      VARCHAR(50) NOT NULL,
    `estado`                  ENUM('solicitado', 'confirmado', 'entregado', 'cancelado') NOT NULL DEFAULT 'solicitado',
    `fecha_entrega_realizada` DATETIME NULL DEFAULT NULL,
    `observaciones`           TEXT NULL DEFAULT NULL,
    
    `created_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_inscripciones_comprobante` (`codigo_comprobante`),
    UNIQUE KEY `uk_evento_afiliado` (`evento_id`, `afiliado_id`),
    KEY `idx_inscripciones_afiliado` (`afiliado_id`),
    KEY `idx_inscripciones_filial` (`filial_retiro_id`),
    
    CONSTRAINT `fk_inscripcion_evento` FOREIGN KEY (`evento_id`) 
        REFERENCES `eventos` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_inscripcion_afiliado` FOREIGN KEY (`afiliado_id`) 
        REFERENCES `afiliados` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_inscripcion_filial` FOREIGN KEY (`filial_retiro_id`) 
        REFERENCES `filiales_retiro` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. Tabla: evento_inscripcion_beneficiarios (Hijos incluidos en cada solicitud)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `evento_inscripcion_beneficiarios` (
    `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `inscripcion_id`          INT UNSIGNED NOT NULL,
    `familiar_id`             INT UNSIGNED NOT NULL,
    `talle`                   VARCHAR(20) NULL DEFAULT NULL,
    `nivel_escolar`           VARCHAR(50) NULL DEFAULT NULL,
    `observacion`             VARCHAR(255) NULL DEFAULT NULL,
    
    `created_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_inscripcion_familiar` (`inscripcion_id`, `familiar_id`),
    KEY `idx_beneficiarios_familiar` (`familiar_id`),
    
    CONSTRAINT `fk_beneficiario_inscripcion` FOREIGN KEY (`inscripcion_id`) 
        REFERENCES `evento_inscripciones` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_beneficiario_familiar` FOREIGN KEY (`familiar_id`) 
        REFERENCES `afiliado_familiares` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. Tabla: beneficios (Guía de convenios y comercios con descuentos)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `beneficios` (
    `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rubro`                   ENUM('farmacia', 'optica', 'salud', 'turismo', 'recreacion', 'comercio', 'otro') NOT NULL,
    `nombre_comercio`         VARCHAR(150) NOT NULL,
    `descuento_detalle`       VARCHAR(255) NOT NULL,
    `descripcion`             TEXT NULL DEFAULT NULL,
    `direccion`               VARCHAR(200) NULL DEFAULT NULL,
    `localidad`               VARCHAR(100) NOT NULL DEFAULT 'Lanús',
    `telefono`                VARCHAR(50) NULL DEFAULT NULL,
    `whatsapp`                VARCHAR(50) NULL DEFAULT NULL,
    `coordenadas`             VARCHAR(60) NULL DEFAULT NULL,
    `imagen_url`              VARCHAR(255) NULL DEFAULT NULL,
    `activo`                  TINYINT(1) NOT NULL DEFAULT 1,
    `orden`                   INT NOT NULL DEFAULT 0,
    
    `created_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    KEY `idx_beneficios_rubro` (`rubro`, `activo`, `orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. Tabla: dispositivos_push (Tokens para notificaciones en Android / iOS)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dispositivos_push` (
    `id`                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `afiliado_id`             INT UNSIGNED NOT NULL,
    `platform`                ENUM('android', 'ios') NOT NULL,
    `device_token`            VARCHAR(255) NOT NULL,
    `last_seen`               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_dispositivos_token` (`device_token`),
    KEY `idx_dispositivos_afiliado` (`afiliado_id`),
    
    CONSTRAINT `fk_dispositivos_afiliado` FOREIGN KEY (`afiliado_id`) 
        REFERENCES `afiliados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
