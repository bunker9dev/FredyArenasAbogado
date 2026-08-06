<?php
/**
 * diagnostico.php — Diagnóstico de errores del sitio
 * ────────────────────────────────────────────────────
 * 1. Sube este archivo a public_html/
 * 2. Ábrelo en: https://fredyarenasabogado.com/diagnostico.php
 * 3. Copia el resultado y compártelo
 * 4. ¡BÓRRALO del servidor inmediatamente después!
 */

// Mostrar TODOS los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

function ok($msg)  { echo "<tr><td class='ok'>✓</td><td>$msg</td></tr>"; }
function err($msg) { echo "<tr><td class='er'>✗</td><td><strong>$msg</strong></td></tr>"; }
function inf($msg) { echo "<tr><td class='in'>→</td><td>$msg</td></tr>"; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Diagnóstico</title>
<style>
  body{font-family:monospace;font-size:14px;padding:2rem;background:#f6f4ef;color:#171a20}
  h2{margin:1.5rem 0 .5rem;color:#a8843e;font-family:sans-serif}
  table{border-collapse:collapse;width:100%;max-width:900px;background:#fff;border:1px solid #d8d3c7;border-radius:4px;margin-bottom:1rem}
  td{padding:.5rem .8rem;border-bottom:1px solid #eee;vertical-align:top}
  .ok{color:#1a6b3c;font-size:1.1rem;width:30px}
  .er{color:#b3261e;font-size:1.1rem;width:30px}
  .in{color:#a8843e;font-size:1.1rem;width:30px}
  pre{background:#1a1a2e;color:#e0e0e0;padding:1rem;border-radius:4px;overflow-x:auto;font-size:12px}
</style>
</head>
<body>
<h1 style="font-family:sans-serif">Diagnóstico — fredyarenasabogado.com</h1>
<p style="color:#4a4f59">Generado: <?php echo date('Y-m-d H:i:s'); ?></p>

<!-- ── 1) PHP ──────────────────────────────────────────── -->
<h2>1. PHP</h2>
<table>
<?php
inf('Versión PHP: <strong>' . PHP_VERSION . '</strong>');
inf('SAPI: ' . php_sapi_name());

// Versión mínima
if (version_compare(PHP_VERSION, '7.4', '<'))
    err('PHP ' . PHP_VERSION . ' es muy antiguo. Se requiere 7.4+');
else
    ok('PHP ' . PHP_VERSION . ' — suficiente');

// Extensiones requeridas
foreach (['mysqli','json','mbstring','fileinfo'] as $ext) {
    if (extension_loaded($ext)) ok("Extensión $ext cargada");
    else err("Extensión $ext NO está cargada — requerida");
}
?>
</table>

<!-- ── 2) Archivos clave ───────────────────────────────── -->
<h2>2. Archivos clave</h2>
<table>
<?php
$base = __DIR__;
$archivos = [
    'index.php',
    'includes/config/database.php',
    'includes/functions/helpers.php',
    'includes/templates/head.php',
    'includes/templates/header.php',
    'includes/templates/footer.php',
    'includes/home/hero.php',
    'includes/home/perfil.php',
    'includes/home/servicios.php',
    'includes/home/blog.php',
    'includes/home/contacto.php',
    'assets/css/app.css',
    'assets/js/app.js',
];
foreach ($archivos as $f) {
    $ruta = $base . '/' . $f;
    if (file_exists($ruta))
        ok("$f <small style='color:#999'>(" . number_format(filesize($ruta)) . " bytes)</small>");
    else
        err("$f — NO EXISTE");
}
?>
</table>

<!-- ── 3) Base de datos ────────────────────────────────── -->
<h2>3. Base de datos</h2>
<table>
<?php
$db_file = $base . '/includes/config/database.php';
if (!file_exists($db_file)) {
    err('includes/config/database.php no existe');
} else {
    // Leer constantes sin ejecutar el archivo completo
    $db_src = file_get_contents($db_file);

    // Detectar si aún tiene el placeholder
    if (strpos($db_src, 'TU_CLAVE_SEGURA_AQUI') !== false)
        err('database.php tiene la clave de ejemplo "TU_CLAVE_SEGURA_AQUI" — <strong>hay que poner la clave real</strong>');

    // Intentar conexión real
    try {
        require_once $db_file;
        if (function_exists('db_connect')) {
            $conn = @db_connect();
            if ($conn && !($conn instanceof stdClass)) {
                ok('Conexión a la BD exitosa');

                // Verificar tabla blog y sus columnas
                $cols_res = $conn->query("SHOW COLUMNS FROM blog");
                if ($cols_res) {
                    $cols = [];
                    while ($c = $cols_res->fetch_assoc()) $cols[] = $c['Field'];
                    ok('Tabla <strong>blog</strong> existe. Columnas: <code>' . implode(', ', $cols) . '</code>');

                    $nuevas = ['id','slug','area','vistas','estado'];
                    foreach ($nuevas as $nc) {
                        if (in_array($nc, $cols)) ok("Columna <code>$nc</code> existe en blog");
                        else err("Columna <code>$nc</code> NO existe — necesitas correr migracion.sql");
                    }
                } else {
                    err('Tabla <strong>blog</strong> NO existe — necesitas crearla');
                }

                // Verificar tabla contacto
                $ct = $conn->query("SHOW COLUMNS FROM contacto");
                if ($ct) {
                    $cc = [];
                    while ($c = $ct->fetch_assoc()) $cc[] = $c['Field'];
                    ok('Tabla <strong>contacto</strong> existe. Columnas: <code>' . implode(', ', $cc) . '</code>');
                    if (!in_array('leido', $cc) && in_array('log', $cc))
                        err('Columna <code>leido</code> no existe — la migración renombra <code>log</code> → <code>leido</code>');
                } else {
                    err('Tabla <strong>contacto</strong> NO existe');
                }

            } else {
                err('No se pudo conectar a la BD — verifica host, usuario, clave y nombre de BD en database.php');
            }
        } else {
            err('La función db_connect() no está definida en database.php');
        }
    } catch (Throwable $e) {
        err('Error al cargar database.php: ' . htmlspecialchars($e->getMessage()));
    }
}
?>
</table>

<!-- ── 4) Carga de includes ───────────────────────────── -->
<h2>4. Carga de includes (simulada)</h2>
<table>
<?php
// Simular carga del index sin output
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI']    = '/';
$_SERVER['REMOTE_ADDR']    = '127.0.0.1';

// Intentar cargar helpers
try {
    if (file_exists($base . '/includes/functions/helpers.php')) {
        require_once $base . '/includes/functions/helpers.php';
        ok('helpers.php cargado sin errores');
        foreach (['h','fecha_es','slugify','url_whatsapp','imagen_blog','registrar_vista','guardar_feedback'] as $fn) {
            if (function_exists($fn)) ok("Función <code>$fn()</code> disponible");
            else err("Función <code>$fn()</code> NO definida en helpers.php");
        }
    }
} catch (Throwable $e) {
    err('Error en helpers.php: ' . htmlspecialchars($e->getMessage()));
}

// Intentar cargar cada partial del home
$partials = [
    'includes/home/hero.php',
    'includes/home/perfil.php',
    'includes/home/servicios.php',
    'includes/home/blog.php',
    'includes/home/contacto.php',
];
foreach ($partials as $p) {
    try {
        ob_start();
        $errores = []; $enviado = false;
        $nombre = $email = $celular = $asesoria = '';
        if (file_exists($base . '/' . $p)) {
            include $base . '/' . $p;
            $out = ob_get_clean();
            if (strlen($out) > 50)
                ok("$p — genera " . strlen($out) . " bytes de HTML");
            else
                err("$p — genera muy poco HTML (" . strlen($out) . " bytes)");
        } else {
            ob_end_clean();
            err("$p — no existe");
        }
    } catch (Throwable $e) {
        ob_end_clean();
        err("$p — <strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) .
            ' en línea ' . $e->getLine());
    }
}
?>
</table>

<!-- ── 5) Error log reciente ──────────────────────────── -->
<h2>5. Últimas líneas del error_log</h2>
<?php
$log_paths = [
    __DIR__ . '/error_log',
    __DIR__ . '/../error_log',
    ini_get('error_log'),
    '/var/log/apache2/error.log',
    '/var/log/php/error.log',
];
$found = false;
foreach ($log_paths as $lp) {
    if ($lp && file_exists($lp) && is_readable($lp)) {
        $lines = array_slice(file($lp), -30);
        echo "<p style='color:#999'>Archivo: $lp</p><pre>";
        echo htmlspecialchars(implode('', $lines));
        echo "</pre>";
        $found = true;
        break;
    }
}
if (!$found) {
    echo "<table><tr><td class='in'>→</td><td>No se encontró el error_log en rutas estándar. ";
    echo "Búscalo en cPanel → Logs → Error Log.</td></tr></table>";
}
?>

<hr style="margin:2rem 0;border-color:#d8d3c7">
<p style="color:#b3261e;font-weight:bold">
  ⚠ BORRA ESTE ARCHIVO del servidor en cuanto termines el diagnóstico.<br>
  Subir por SFTP y eliminarlo toma menos de 1 minuto.
</p>
</body>
</html>
