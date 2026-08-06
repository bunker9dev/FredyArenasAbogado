<?php
require_once __DIR__ . '/../../bunker/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';

$areas = ['Derecho Administrativo','Derecho Disciplinario','Justicia Penal Militar',
          'Derecho Laboral - Pensión','Responsabilidad Médica','Insolvencia'];

/* ── Cargar artículo ─────────────────────────────────────── */
$slug_param = trim($_GET['slug'] ?? '');
$db = db_connect();
$art = null;

if ($db && $slug_param) {
    $st = $db->prepare(
        "SELECT id,titulo,slug,area,fecha,autor_nombre,autor_apellido,
                texto_inicio,texto_completo,imagen,imagen_autor,estado
         FROM blog WHERE slug=? LIMIT 1");
    $st->bind_param('s', $slug_param);
    $st->execute();
    $art = $st->get_result()->fetch_assoc();
}

if (!$art) {
    header('Location: /bunker/blog/?ok=noexiste');
    exit;
}
$d = $art;

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) { $errores[] = 'Sesión expirada. Recarga la página.'; }
    else {
        $d['titulo']         = trim($_POST['titulo']         ?? '');
        $d['slug']           = slugify($_POST['slug'] !== '' ? $_POST['slug'] : $d['titulo']);
        $d['area']           = trim($_POST['area']           ?? '');
        $d['fecha']          = trim($_POST['fecha']          ?? '');
        $d['autor_nombre']   = trim($_POST['autor_nombre']   ?? '');
        $d['autor_apellido'] = trim($_POST['autor_apellido'] ?? '');
        $d['texto_inicio']   = trim($_POST['texto_inicio']   ?? '');
        $d['texto_completo'] = trim($_POST['texto_completo'] ?? '');
        $d['imagen_autor']   = trim($_POST['imagen_autor']   ?? '');
        $d['estado']         = $_POST['estado']==='borrador' ? 'borrador' : 'publicado';

        if (!$d['titulo'])         $errores[] = 'El título es obligatorio.';
        if (!$d['texto_inicio'])   $errores[] = 'El resumen es obligatorio.';
        if (!$d['texto_completo']) $errores[] = 'El contenido es obligatorio.';

        /* ── Nueva imagen (opcional) ─────────────────────────
         * Se sube UN solo archivo; procesar_imagen_blog() genera
         * -1200.webp y -700.webp automáticamente.
         * ──────────────────────────────────────────────────── */
        $imagen = $_FILES['imagen'] ?? null;
        $nombre_img = $art['imagen']; // conservar la existente por defecto

        if (!empty($imagen['name'])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $tipo  = $finfo->file($imagen['tmp_name']);
            if ($imagen['size'] > 5*1024*1024) {
                $errores[] = 'La imagen supera los 5 MB.';
            } elseif (!in_array($tipo, ['image/jpeg','image/png','image/webp'], true)) {
                $errores[] = 'Formato no permitido.';
            } else {
                $dir = __DIR__ . '/../../imagenes';
                $nuevo_base = procesar_imagen_blog($imagen['tmp_name'], $tipo, $dir);
                if ($nuevo_base === '') {
                    $errores[] = 'No se pudo procesar la imagen. Intenta con otro archivo.';
                } else {
                    borrar_imagen_blog($art['imagen'], $dir); // borra las 2 versiones anteriores
                    $nombre_img = $nuevo_base;
                }
            }
        }

        /* ── UPDATE ──────────────────────────────────────────── */
        if (empty($errores)) {
            $st2 = $db->prepare(
                "UPDATE blog SET titulo=?,slug=?,area=?,fecha=?,autor_nombre=?,autor_apellido=?,
                                 texto_inicio=?,texto_completo=?,imagen=?,imagen_autor=?,estado=?
                 WHERE id=?");
            $st2->bind_param('sssssssssssi',
                $d['titulo'],$d['slug'],$d['area'],$d['fecha'],
                $d['autor_nombre'],$d['autor_apellido'],
                $d['texto_inicio'],$d['texto_completo'],
                $nombre_img,$d['imagen_autor'],$d['estado'],
                $art['id']);

            if ($st2->execute()) {
                header('Location: /bunker/blog/?ok=editado');
                exit;
            }
            $errores[] = 'Error al guardar los cambios.';
        }
    }
}

$adm_title      = 'Editar artículo';
$adm_nav_active = 'articulos';
require __DIR__ . '/../../bunker/includes/admin-head.php';
require __DIR__ . '/../../bunker/includes/admin-layout.php';
?>

<div class="adm-page-head">
  <div>
    <h1>Editar artículo</h1>
    <p><?php echo h($art['titulo']); ?></p>
  </div>
  <div style="display:flex;gap:.7rem">
    <a class="adm-btn adm-btn--ghost" href="/blog/<?php echo h($art['slug']); ?>" target="_blank">Ver en sitio</a>
    <a class="adm-btn adm-btn--ghost" href="/bunker/blog/">← Volver</a>
  </div>
</div>

<?php foreach ($errores as $e): ?>
  <div class="adm-alert adm-alert--error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="adm-form-wrap">
  <form method="POST"
        action="/bunker/blog/editar.php?slug=<?php echo urlencode($slug_param); ?>"
        enctype="multipart/form-data" autocomplete="off">
    <?php echo csrf_campo(); ?>

    <div class="adm-form-grid">
      <div class="adm-field span2">
        <label for="titulo">Título *</label>
        <input id="titulo" name="titulo" type="text"
               value="<?php echo h($d['titulo']); ?>" required>
      </div>
      <div class="adm-field span2">
        <label for="slug">Slug (URL)</label>
        <input id="slug" name="slug" type="text"
               value="<?php echo h($d['slug']); ?>">
        <span class="adm-field__hint">URL: /blog/<strong><?php echo h($d['slug']); ?></strong></span>
      </div>
      <div class="adm-field">
        <label for="area">Área *</label>
        <select id="area" name="area" required>
          <?php foreach ($areas as $ar): ?>
          <option value="<?php echo h($ar); ?>" <?php echo $d['area']===$ar?'selected':''; ?>>
            <?php echo h($ar); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="adm-field">
        <label for="fecha">Fecha</label>
        <input id="fecha" name="fecha" type="date" value="<?php echo h($d['fecha']); ?>">
      </div>
      <div class="adm-field">
        <label for="autor_nombre">Nombre</label>
        <input id="autor_nombre" name="autor_nombre" type="text"
               value="<?php echo h($d['autor_nombre']); ?>">
      </div>
      <div class="adm-field">
        <label for="autor_apellido">Apellido</label>
        <input id="autor_apellido" name="autor_apellido" type="text"
               value="<?php echo h($d['autor_apellido']); ?>">
      </div>
      <div class="adm-field span2">
        <label for="texto_inicio">Resumen *</label>
        <textarea id="texto_inicio" name="texto_inicio"
                  style="min-height:70px" required><?php echo h($d['texto_inicio']); ?></textarea>
      </div>
      <div class="adm-field span2">
        <label for="texto_completo">Contenido * <span style="font-weight:400;font-size:.72rem">(usa los botones de arriba para dar formato)</span></label>
        <textarea id="texto_completo" name="texto_completo"
                  class="adm-editor" required><?php echo h($d['texto_completo']); ?></textarea>
      </div>
      <div class="adm-field">
        <label>Imagen actual</label>
        <?php if (!empty($d['imagen'])): ?>
          <img src="<?php echo h(imagen_blog_url($d['imagen'])); ?>"
               style="max-height:120px;border-radius:4px;border:1px solid var(--line)" alt="">
        <?php else: ?>
          <span style="font-size:.82rem;color:var(--ink-soft)">Sin imagen</span>
        <?php endif; ?>
        <label for="imagen" style="margin-top:.6rem">Reemplazar imagen</label>
        <input id="imagen" name="imagen" type="file"
               accept="image/jpeg,image/png,image/webp"
               onchange="previewImagen(this,'img-preview')">
        <img id="img-preview" class="adm-field__preview" alt="">
      </div>
      <div class="adm-field">
        <label for="imagen_autor">Crédito de imagen</label>
        <input id="imagen_autor" name="imagen_autor" type="text"
               value="<?php echo h($d['imagen_autor'] ?? ''); ?>">
        <label for="estado" style="margin-top:1rem">Estado</label>
        <select id="estado" name="estado">
          <option value="publicado" <?php echo $d['estado']==='publicado'?'selected':''; ?>>Publicado</option>
          <option value="borrador"  <?php echo $d['estado']==='borrador' ?'selected':''; ?>>Borrador</option>
        </select>
      </div>
    </div>

    <div class="adm-form-actions">
      <button class="adm-btn adm-btn--primary" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
        Guardar cambios
      </button>
      <button class="adm-btn adm-btn--danger" type="button"
              onclick="confirmarBorrar('/bunker/blog/borrar.php?id=<?php echo (int)$art['id']; ?>')">
        Eliminar artículo
      </button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../../bunker/includes/admin-footer.php'; ?>
