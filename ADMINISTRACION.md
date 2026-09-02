# Administración sin base de datos (Flat-File)

El panel de administración se encuentra en `admin/login.php` y permite gestionar el contenido estructurado del sitio sin base de datos mediante archivos JSON protegidos en `private-content/`.

El directorio `private-content/` incluye directivas Apache (`.htaccess`) para bloquear el acceso web directo. En producción (Hostinger / cPanel), la opción recomendada es ubicarlo fuera de `public_html` y adaptar las rutas en `site-content.php` y `admin/bootstrap.php`.

---

## Secciones Administrables

| Sección | Archivo JSON | Editor en Panel | Página Pública |
| :--- | :--- | :--- | :--- |
| **01. Home y contacto** | `private-content/home.json` | `admin/home.php` | `index.php`, `footer.php`, `menu.php` |
| **02. Beneficios y servicios** | `private-content/servicios.json` | `admin/servicios.php` | `servicios.php` |
| **03. Normativas y documentos** | `private-content/normativas.json` | `admin/normativas.php` | `normativas.php` |
| **04. Directorio de filiales** | `private-content/filiales.json` | `admin/filiales.php` | `filiales.php` |
| **05. Comisión directiva** | `private-content/comision.json` | `admin/comision.php` | `comision-directiva.php` |
| **06. Novedades y archivo** | `private-content/novedades.json` | `admin/novedades.php` | `novedades.php` |
| **07. Instalaciones y espacios** | `private-content/instalaciones.json` | `admin/instalaciones.php` | `instalaciones.php` |

---

## Primer acceso y configuración de credenciales

Por seguridad, no existe ninguna contraseña predeterminada ni usuario almacenado en texto plano.

1. Generá el hash de tu contraseña segura (mínimo 14 caracteres) desde la terminal:
   ```sh
   php tools/create-admin-hash.php
   ```
2. Copiá `private-content/admin.local.example.php` como `private-content/admin.local.php` y pegá el hash generado:
   ```php
   <?php
   return array(
       'password_hash' => '$2y$10$...'
   );
   ```
3. Alternativamente, podés definir la variable de entorno `PANADEROS_ADMIN_PASSWORD_HASH` en el servidor web.

---

## Medidas de Seguridad Implementadas

- **Autenticación robusta**: Uso de `password_hash()` con `PASSWORD_DEFAULT` y verificación mediante `password_verify()`.
- **Sesiones endurecidas**: `session.use_strict_mode=1`, `session.cookie_httponly=1`, `samesite=Strict`, `secure` en HTTPS y regeneración de ID (`session_regenerate_id(true)`) al iniciar sesión.
- **Protección CSRF**: Token criptográfico `admin_csrf_token()` verificado con `hash_equals()` en todos los envíos `POST`.
- **Límite de intentos (Rate-Limiting)**: Bloqueo tras 5 intentos fallidos durante 15 minutos registrado por hash de IP en `private-content/login-attempts.json`.
- **Validación estricta y sanitización**: Validación de tipos, longitudes máximas, formato de correo, URLs HTTPS y rutas locales seguras antes de persistir.
- **Escritura y lectura segura**: Bloqueo compartido (`LOCK_SH`) en lecturas, bloqueo exclusivo (`LOCK_EX`) en escrituras con archivo temporal y reemplazo atómico (`rename`).
- **Copias de seguridad automáticas**: Cada guardado genera una copia versionada con fecha y hora en `private-content/backups/`.
- **Registro de auditoría**: Acciones administrativas registradas con timestamp e IP hasheada en `private-content/audit.log`.

---

## Recomendaciones de publicación

- Usá HTTPS forzado en todo el sitio y, si el hosting lo permite, activá protección de directorio (`.htpasswd`) en `/admin/` como capa adicional.
- Nunca hagas commit ni publiques `admin.local.php`, `audit.log`, `login-attempts.json` ni los respaldos de `private-content/backups/`.
- Confirmá que el directorio `private-content/` y su subdirectorio `backups/` cuenten con permisos de escritura para el usuario de PHP.
