<?php

require_once 'bootstrap.php';
admin_require_login();
admin_render_start('Panel');
?>
<main class="admin-shell">
    <header class="admin-header"><a class="admin-mark" href="index.php">Panaderos <span>Administración</span></a><a class="admin-logout" href="logout.php">Cerrar sesión</a></header>
    <section class="admin-intro"><p>Panel privado</p><h1>Contenido del sitio,<br><em>en orden.</em></h1><span>Todos los cambios se guardan con validación estricta y copia de respaldo automática en archivos JSON.</span></section>
    <section class="admin-cards" aria-label="Secciones editables">
        <a href="home.php">
            <span>01</span>
            <h2>Home y contacto</h2>
            <p>Portada, citas, escala salarial, redes sociales, dirección y mapa.</p>
            <b>Editar <i>→</i></b>
        </a>
        <a href="servicios.php">
            <span>02</span>
            <h2>Beneficios y servicios</h2>
            <p>Laboratorios, casamiento, nacimiento, farmacia, mudanzas y asesoramiento.</p>
            <b>Editar <i>→</i></b>
        </a>
        <a href="normativas.php">
            <span>03</span>
            <h2>Normativas y marco legal</h2>
            <p>Convenio colectivo CCT 231/94, leyes laborales y código alimentario.</p>
            <b>Editar <i>→</i></b>
        </a>
        <a href="filiales.php">
            <span>04</span>
            <h2>Directorio de filiales</h2>
            <p>Sedes de todo el país, direcciones, teléfonos y secretarías generales.</p>
            <b>Editar <i>→</i></b>
        </a>
        <a href="comision.php">
            <span>05</span>
            <h2>Comisión directiva</h2>
            <p>Integrantes de la conducción del sindicato, cargos y fotografías.</p>
            <b>Editar <i>→</i></b>
        </a>
        <a href="novedades.php">
            <span>06</span>
            <h2>Novedades y archivo</h2>
            <p>Información de canales oficiales y selección del archivo histórico.</p>
            <b>Editar <i>→</i></b>
        </a>
    </section>
</main>
<?php admin_render_end(); ?>
