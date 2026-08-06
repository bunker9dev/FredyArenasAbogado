<?php
/**
 * diagnostico2.php — Fase 2: error exacto del index.php
 * Sube a public_html/ → abre en navegador → borra después
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><title>Diagnóstico 2</title>
<style>
  body{font-family:monospace;font-size:13px;padding:2rem;background:#f6f4ef;color:#171a20}
  h2{margin:1.5rem 0 .5rem;color:#a8843e;font-family:sans-serif}
  table{border-collapse:collapse;width:100%;background:#fff;border:1px solid #d8d3c7;border-radius:4px;margin-bottom:1rem}
  td{padding:.5rem .8rem;border-bottom:1px solid #eee;vertical-align:top}
  .ok{color:#1a6b3c;width:30px} .er{color:#b3261e;width:30px;font-weight:bold} .in{color:#a8843e;width:30px}
  pre{background:#1a1a2e;color:#e0e0e0;padding:1rem;border-radius:4px;overflow-x:auto;font-size:11px;white-space:pre-wrap;word-break:break-all}
</style>
</head>
<body>
<h1 style="font-family:sans-serif">Diagnóstico 2 — Error exacto</h1>
<p style="color:#4a4f59">Generado: <?php echo date('Y-m-d H:i:s'); ?></p>

<!-- ── Rutas reales ──────────────────────────────────── -->
<h2>1. Rutas reales del servidor</h2>
<table>
<?php
$base = __DIR__;
echo "<tr><td class='in'>→</td><td>__DIR__ = <code>$base</code></td></tr>";
echo "<tr><td class='in'>→</td><td>open_basedir = <code>" . ini_get('open_basedir') . "</code></td></tr>";
echo "<tr><td class='in'>→</td><td>PHP version = <code>" . PHP_VERSION . "</code></td></tr>";
echo "<tr><td class='in'>→</td><td>memory_limit = <code>" . ini_get('memory_limit') . "</code></td></tr>";
echo "<tr><td class='in'>→</td><td>max_execution_time = <code>" . ini_get('max_execution_time') . "s</code></td></tr>";

// ¿Existe .htaccess?
$ht = $base . '/.htaccess';
if (file_exists($ht)) {
    echo "<tr><td class='ok'>✓</td><td>.htaccess existe (" . filesize($ht) . " bytes)</td></tr>";
    // Mostrar contenido
    $htc = htmlspecialchars(file_get_contents($ht));
    echo "<tr><td class='in'>→</td><td>.htaccess contenido:<pre>$htc</pre></td></tr>";
} else {
    echo "<tr><td class='er'>✗</td><td>.htaccess NO existe</td></tr>";
}

// ¿Existe includes/.htaccess?
$iht = $base . '/includes/.htaccess';
if (file_exists($iht)) {
    $ihtc = htmlspecialchars(file_get_contents($iht));
    echo "<tr><td class='ok'>✓</td><td>includes/.htaccess existe:<pre>$ihtc</pre></td></tr>";
} else {
    echo "<tr><td class='in'>→</td><td>includes/.htaccess no existe</td></tr>";
}
?>
</table>

<!-- ── Ejecutar index.php real y capturar error ──────── -->
<h2>2. Ejecución real del index.php</h2>
<table>
<?php
// Simular entorno de petición GET real
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI']    = '/';
$_SERVER['HTTP_HOST']      = 'fredyarenasabogado.com';
$_SERVER['REMOTE_ADDR']    = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Capturar cualquier error fatal con shutdown function
$error_capturado = null;

try {
    ob_start();
    // Ejecutar index.php en su propio scope para no contaminar variables
    $resultado = (function() use ($base) {
        ob_start();
        try {
            include $base . '/index.php';
            $html = ob_get_clean();
            return ['ok' => true, 'size' => strlen($html), 'preview' => substr($html, 0, 500)];
        } catch (\Throwable $e) {
            ob_end_clean();
            return [
                'ok'      => false,
                'tipo'    => get_class($e),
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ];
        }
    })();
    ob_end_clean();

    if ($resultado['ok']) {
        echo "<tr><td class='ok'>✓</td><td>index.php ejecutó sin errores — generó <strong>{$resultado['size']} bytes</strong></td></tr>";
        echo "<tr><td class='in'>→</td><td>Primeros 500 chars:<pre>" . htmlspecialchars($resultado['preview']) . "</pre></td></tr>";
    } else {
        echo "<tr><td class='er'>✗</td><td>";
        echo "<strong>{$resultado['tipo']}</strong>: " . htmlspecialchars($resultado['mensaje']) . "<br>";
        echo "Archivo: <code>" . htmlspecialchars($resultado['archivo']) . "</code> línea <strong>{$resultado['linea']}</strong><br><br>";
        echo "Traza:<pre>" . htmlspecialchars($resultado['trace']) . "</pre>";
        echo "</td></tr>";
    }

} catch (\Throwable $outer) {
    ob_end_clean();
    echo "<tr><td class='er'>✗</td><td>";
    echo "Error externo: " . htmlspecialchars($outer->getMessage());
    echo " en " . htmlspecialchars($outer->getFile()) . " línea " . $outer->getLine();
    echo "</td></tr>";
}
?>
</table>

<!-- ── Error log del hosting ─────────────────────────── -->
<h2>3. Error log del hosting</h2>
<?php
// En cPanel/LiteSpeed el error_log suele estar aquí:
$home = dirname(dirname(dirname($base))); // sube 3 niveles desde public_html
$log_candidatos = [
    $base . '/error_log',
    dirname($base) . '/error_log',    // un nivel arriba de public_html
    $home . '/logs/error_log',
    $home . '/logs/php_error',
    '/tmp/php_errors.log',
];
$encontrado = false;
foreach ($log_candidatos as $lp) {
    if (@file_exists($lp) && @is_readable($lp) && filesize($lp) > 0) {
        $lines = array_slice(file($lp), -40);
        echo "<p style='color:#999'>📄 $lp (" . number_format(filesize($lp)) . " bytes)</p>";
        echo "<pre>" . htmlspecialchars(implode('', $lines)) . "</pre>";
        $encontrado = true;
        break;
    }
}
if (!$encontrado) {
    // Buscar en la raíz del home del usuario
    echo "<table><tr><td class='in'>→</td><td>";
    echo "No encontrado automáticamente. Ruta del home del usuario: <code>$home</code><br>";
    echo "Busca en cPanel → Logs → <strong>Error Log</strong> y pega aquí las últimas líneas.";
    echo "</td></tr></table>";
}
?>

<hr style="margin:2rem 0;border-color:#d8d3c7">
<p style="color:#b3261e;font-weight:bold">⚠ BORRA ESTE ARCHIVO del servidor cuando termines.</p>
</body>
</html>
