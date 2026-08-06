<?php
/**
 * migrar-imagenes.php — Migración de UN SOLO USO.
 *
 * Recorre todos los artículos que tengan una imagen con el formato
 * antiguo (un solo archivo, ej. "a1b2c3d4e5f6.jpg") y genera las
 * dos versiones nuevas (-1200.webp y -700.webp) que ahora usa el
 * sitio, actualizando la base de datos y borrando el archivo viejo.
 *
 * CÓMO USARLO:
 *   1. Sube este archivo a /bunker/ junto con el resto de cambios.
 *   2. Inicia sesión en el panel (/bunker/) como siempre.
 *   3. Visita https://fredyarenasabogado.com/bunker/migrar-imagenes.php
 *   4. Revisa el reporte en pantalla.
 *   5. IMPORTANTE: borra este archivo del servidor cuando termines.
 *      No debe quedar accesible en producción.
 */

require_once __DIR__ . '/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/functions/helpers.php';

$dir = __DIR__ . '/../imagenes';
$reporte = [];
$ejecutar = isset($_POST['confirmar']);

$db = db_connect();
$articulos = [];
if ($db) {
    $res = $db->query("SELECT id, titulo, imagen FROM blog WHERE imagen IS NOT NULL AND imagen <> ''");
    while ($row = $res->fetch_assoc()) $articulos[] = $row;
}

/**
 * Un nombre "ya migrado" es el base (12 bytes hex = 24 caracteres, sin extensión)
 * y existen sus dos archivos -1200.webp / -700.webp.
 * Un nombre "antiguo" trae extensión (.jpg, .png, .webp) en la propia columna.
 */
foreach ($articulos as $a) {
    $nombre = $a['imagen'];
    $ya_migrado = !preg_match('/\.(jpe?g|png|webp)$/i', $nombre)
        && is_file("$dir/{$nombre}-1200.webp")
        && is_file("$dir/{$nombre}-700.webp");

    if ($ya_migrado) {
        $reporte[] = ['id' => $a['id'], 'titulo' => $a['titulo'], 'estado' => 'ya migrado', 'detalle' => $nombre];
        continue;
    }

    $ruta_vieja = "$dir/$nombre";
    if (!is_file($ruta_vieja)) {
        $reporte[] = ['id' => $a['id'], 'titulo' => $a['titulo'], 'estado' => 'error', 'detalle' => "archivo no encontrado: $nombre"];
        continue;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $tipo  = $finfo->file($ruta_vieja);
    if (!in_array($tipo, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        $reporte[] = ['id' => $a['id'], 'titulo' => $a['titulo'], 'estado' => 'error', 'detalle' => "tipo no soportado: $tipo"];
        continue;
    }

    if (!$ejecutar) {
        $reporte[] = ['id' => $a['id'], 'titulo' => $a['titulo'], 'estado' => 'pendiente', 'detalle' => $nombre];
        continue;
    }

    $nuevo_base = procesar_imagen_blog($ruta_vieja, $tipo, $dir);
    if ($nuevo_base === '') {
        $reporte[] = ['id' => $a['id'], 'titulo' => $a['titulo'], 'estado' => 'error', 'detalle' => 'no se pudo procesar la imagen'];
        continue;
    }

    $st = $db->prepare("UPDATE blog SET imagen = ? WHERE id = ?");
    $st->bind_param('si', $nuevo_base, $a['id']);
    if ($st->execute()) {
        @unlink($ruta_vieja);
        $reporte[] = ['id' => $a['id'], 'titulo' => $a['titulo'], 'estado' => 'migrado', 'detalle' => "$nombre → $nuevo_base"];
    } else {
        $reporte[] = ['id' => $a['id'], 'titulo' => $a['titulo'], 'estado' => 'error', 'detalle' => 'fallo al actualizar la BD'];
    }
}

$pendientes = count(array_filter($reporte, fn($r) => $r['estado'] === 'pendiente'));

$adm_title      = 'Migración de imágenes';
$adm_nav_active = '';
require __DIR__ . '/includes/admin-head.php';
require __DIR__ . '/includes/admin-layout.php';
?>

<div class="adm-page-head">
  <div>
    <h1>Migración de imágenes</h1>
    <p>Convierte las imágenes antiguas (un solo archivo) al nuevo formato con dos tamaños.</p>
  </div>
  <a class="adm-btn adm-btn--ghost" href="/bunker/blog/">← Volver al listado</a>
</div>

<?php if (!$ejecutar && $pendientes > 0): ?>
  <div class="adm-alert adm-alert--warn">
    Se encontraron <strong><?php echo $pendientes; ?></strong> artículo(s) con imagen en formato antiguo.
    Esto es una simulación — nada se ha modificado todavía.
  </div>
  <form method="POST" style="margin-bottom:1.4rem">
    <button class="adm-btn adm-btn--primary" type="submit" name="confirmar" value="1">
      Migrar <?php echo $pendientes; ?> imagen(es) ahora
    </button>
  </form>
<?php elseif ($ejecutar): ?>
  <div class="adm-alert adm-alert--success">Migración ejecutada. Revisa el detalle abajo.</div>
<?php else: ?>
  <div class="adm-alert adm-alert--success">No hay imágenes pendientes de migrar. Todo está al día.</div>
<?php endif; ?>

<table class="adm-table" style="width:100%;border-collapse:collapse;font-size:.85rem">
  <thead>
    <tr style="text-align:left;border-bottom:1px solid var(--line)">
      <th style="padding:.5rem">ID</th>
      <th style="padding:.5rem">Título</th>
      <th style="padding:.5rem">Estado</th>
      <th style="padding:.5rem">Detalle</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($reporte as $r): ?>
    <tr style="border-bottom:1px solid var(--line)">
      <td style="padding:.5rem"><?php echo (int)$r['id']; ?></td>
      <td style="padding:.5rem"><?php echo h($r['titulo']); ?></td>
      <td style="padding:.5rem">
        <?php
          $color = match($r['estado']) {
            'migrado'    => 'var(--success)',
            'ya migrado' => 'var(--ink-soft)',
            'pendiente'  => '#a8843e',
            default      => 'var(--error)',
          };
        ?>
        <strong style="color:<?php echo $color; ?>"><?php echo h($r['estado']); ?></strong>
      </td>
      <td style="padding:.5rem;font-family:monospace;font-size:.78rem"><?php echo h($r['detalle']); ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($reporte)): ?>
    <tr><td colspan="4" style="padding:.8rem;color:var(--ink-soft)">No hay artículos con imagen.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php if ($ejecutar): ?>
<div class="adm-alert adm-alert--warn" style="margin-top:1.4rem">
  Ya terminaste. Borra este archivo (<code>bunker/migrar-imagenes.php</code>) del servidor —
  no debe quedar accesible en producción.
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
