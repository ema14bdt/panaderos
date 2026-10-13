# Panaderos de Lanús · Aplicación Móvil (Android & iOS)

Aplicación oficial del **Sindicato de Obreros Panaderos de Lanús** desarrollada en **Flutter**, diseñada para publicación en Google Play Store y Apple App Store.

---

## Características de la App

1. **Verificación de Padrón y Registro en 2 Pasos**:
   * **Paso 1**: El afiliado ingresa su DNI y N° de Afiliado. La app valida contra el padrón precargado del sindicato.
   * **Paso 2**: Crea su contraseña, confirma su email y teléfono, activando su carnet al instante.
2. **Inicio de Sesión**:
   * Ingreso con DNI o Email y contraseña.
   * Manejo de sesión persistente con Bearer Token seguro.
3. **Carnet Digital Interactivo**:
   * Visualización frontal con diseño gremial (colores azul marino y dorado, foto, datos de la panadería y categoría).
   * Badge de estado en tiempo real (**ACTIVO**).
   * **Código QR dinámico** para validación rápida en farmacias, ópticas y prestadores.
   * **Modo Offline**: Guarda la credencial en el almacenamiento local del teléfono para mostrarla en comercios o farmacias aún sin señal móvil ni Wi-Fi.
4. **Grupo Familiar**:
   * Visualización de los hijos y cónyuge a cargo registrados en el sindicato (preparado para la entrega de útiles y juguetes del Día del Niño).

---

## Cómo Ejecutar el Proyecto

### 1. Requisitos previos
Si aún no tenés Flutter instalado en tu equipo Ubuntu/Debian:
```bash
sudo snap install flutter --classic
```

Verificá el estado con:
```bash
flutter doctor
```

### 2. Instalar dependencias
Desde la carpeta `mobile/`:
```bash
cd mobile
flutter pub get
```

### 3. Configuración del Servidor / API
En [mobile/lib/core/constants.dart](lib/core/constants.dart), ajustá `ApiConfig.baseUrl` según dónde estés probando la app:
* **Emulador Android**: `http://10.0.2.2/api` (apunta automáticamente al localhost de tu PC).
* **Emulador iOS o Web**: `http://localhost/api`.
* **Teléfono Físico (vía Wi-Fi)**: `http://TU_IP_LOCAL/api` (ej: `http://192.168.1.50/api`).

### 4. Iniciar la aplicación
```bash
flutter run
```
