<?php
require_once __DIR__ . '/../../admin/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';

$areas = ['Derecho Administrativo','Derecho Disciplinario','Justicia Penal Militar',
          'Derecho Laboral - Pensión','Responsabilidad Médica','Insolvencia'];

$errores = [];
$d = ['titulo'=>'','slug'=>'','area'=>'Derecho Administrativo','fecha'=>date('Y-m-d'),
      'autor_nombre'=>'Fredy Antonio','autor_apellido'=>'Arenas Gutiérrez',
      'texto_inicio'=>'','texto_completo'=>'','imagen_autor'=>'','estado'=>'publicado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) { $errores[] = 'Sesión expirada. Recarga la página.'; }
    else {
        $d['titulo']          = trim($_POST['titulo']         ?? '');
        $d['slug']            = slugify($_POST['slug'] !== '' ? $_POST['slug'] : ($d['titulo']));
        $d['area']            = trim($_POST['area']           ?? '');
        $d['fecha']           = trim($_POST['fecha']          ?? date('Y-m-d'));
        $d['autor_nombre']    = trim($_POST['autor_nombre']   ?? '');
        $d['autor_apellido']  = trim($_POST['autor_apellido'] ?? '');
        $d['texto_inicio']    = trim($_POST['texto_inicio']   ?? '');
        $d['texto_completo']  = trim($_POST['texto_completo'] ?? '');
        $d['imagen_autor']    = trim($_POST['imagen_autor']   ?? '');
        $d['estado']          = $_POST['estado'] === 'borrador' ? 'borrador' : 'publicado';

        if (!$d['titulo'])         $errores[] = 'El título es obligatorio.';
        if (!$d['texto_inicio'])   $errores[] = 'El resumen es obligatorio.';
        if (!$d['texto_completo']) $errores[] = 'El contenido es obligatorio.';
        if (!in_array($d['area'], $areas, true)) $errores[] = 'Selecciona un área válida.';

        /* ── Imagen ────────────────────────────────────────── */
        $imagen = $_FILES['imagen'] ?? null;
        $nombre_img = '';
        if (!empty($imagen['name'])) {
            $max = 5 * 1024 * 1024;
            if ($imagen['size'] > $max) { $errores[] = 'La imagen supera los 5 MB.'; }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $tipo  = $finfo->file($imagen['tmp_name']);
            $exts  = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            if (!isset($exts[$tipo])) { $errores[] = 'Formato no permitido (JPG, PNG o WebP).'; }
            else {
                $nombre_img = bin2hex(random_bytes(12)) . '.' . $exts[$tipo];
            }
        }

        /* ── Verificar slug único ──────────────────────────── */
        $db = db_connect();
        if ($db && empty($errores)) {
            $chk = $db->prepare("SELECT COUNT(*) FROM blog WHERE slug = ?");
            $chk->bind_param('s', $d['slug']);
            $chk->execute(); $chk->bind_result($existe); $chk->fetch(); $chk->close();
            if ($existe) { $d['slug'] .= '-' . date('ymd'); }

            /* ── Guardar imagen ────────────────────────────── */
            if ($nombre_img) {
                $dir = __DIR__ . '/../../imagenes/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($imagen['tmp_name'], $dir . $nombre_img);
            }

            /* ── INSERT ────────────────────────────────────── */
            $st = $db->prepare(
                "INSERT INTO blog (titulo,slug,area,fecha,autor_nombre,autor_apellido,
                                   texto_inicio,texto_completo,imagen,imagen_autor,estado)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $st->bind_param('sssssssssss',
                $d['titulo'],$d['slug'],$d['area'],$d['fecha'],
                $d['autor_nombre'],$d['autor_apellido'],
                $d['texto_inicio'],$d['texto_completo'],
                $nombre_img,$d['imagen_autor'],$d['estado']);

            if ($st->execute()) {
                header('Location: /admin/blog/?ok=creado');
                exit;
            }
            $errores[] = 'Error al guardar en la base de datos.';
        }
    }
}

$adm_title      = 'Nuevo artículo';
$adm_nav_active = 'art-crear';
require __DIR__ . '/../../admin/includes/admin-head.php';
require __DIR__ . '/../../admin/includes/admin-layout.php';
?>

<div class="adm-page-head">
  <div>
    <h1>Nuevo artículo</h1>
    <p>Completa los campos y haz clic en "Publicar".</p>
  </div>
  <a class="adm-btn adm-btn--ghost" href="/admin/blog/">← Volver al listado</a>
</div>

<?php foreach ($errores as $e): ?>
  <div class="adm-alert adm-alert--error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="adm-form-wrap">
  <form method="POST" action="/admin/blog/crear.php"
        enctype="multipart/form-data" autocomplete="off">
    <?php echo csrf_campo(); ?>

    <div class="adm-form-grid">

      <!-- Título -->
      <div class="adm-field span2">
        <label for="titulo">Título *</label>
        <input id="titulo" name="titulo" type="text"
               placeholder="Ej: Fuero penal militar: qué conductas cobija"
               value="<?php echo h($d['titulo']); ?>" required
               oninput="autoSlug(this.value)">
      </div>

      <!-- Slug -->
      <div class="adm-field span2">
        <label for="slug">Slug (URL)</label>
        <input id="slug" name="slug" type="text"
               placeholder="fuero-penal-militar"
               value="<?php echo h($d['slug']); ?>">
        <span class="adm-field__hint">
          Se genera solo. URL final: /blog/<strong id="slug-preview"><?php echo h($d['slug']); ?></strong>
        </span>
      </div>

      <!-- Área -->
      <div class="adm-field">
        <label for="area">Área de práctica *</label>
        <select id="area" name="area" required>
          <?php foreach ($areas as $ar): ?>
          <option value="<?php echo h($ar); ?>" <?php echo $d['area']===$ar?'selected':''; ?>>
            <?php echo h($ar); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Fecha -->
      <div class="adm-field">
        <label for="fecha">Fecha de publicación *</label>
        <input id="fecha" name="fecha" type="date"
               value="<?php echo h($d['fecha']); ?>" required>
      </div>

      <!-- Autor -->
      <div class="adm-field">
        <label for="autor_nombre">Nombre del autor</label>
        <input id="autor_nombre" name="autor_nombre" type="text"
               value="<?php echo h($d['autor_nombre']); ?>">
      </div>
      <div class="adm-field">
        <label for="autor_apellido">Apellido del autor</label>
        <input id="autor_apellido" name="autor_apellido" type="text"
               value="<?php echo h($d['autor_apellido']); ?>">
      </div>

      <!-- Resumen -->
      <div class="adm-field span2">
        <label for="texto_inicio">Resumen / extracto * <span style="font-weight:400;font-size:.72rem">(aparece en las cards del blog)</span></label>
        <textarea id="texto_inicio" name="texto_inicio"
                  placeholder="Una o dos frases que describan el artículo…"
                  style="min-height:70px" required><?php echo h($d['texto_inicio']); ?></textarea>
      </div>

      <!-- Contenido -->
      <div class="adm-field span2">
        <label for="texto_completo">Contenido completo * <span style="font-weight:400;font-size:.72rem">(HTML básico permitido: &lt;p&gt; &lt;h2&gt; &lt;ul&gt; &lt;strong&gt; &lt;em&gt;)</span></label>
        <textarea id="texto_completo" name="texto_completo"
                  class="adm-editor"
                  placeholder="<p>Escribe el artículo aquí…</p>"
                  required><?php echo h($d['texto_completo']); ?></textarea>
      </div>

      <!-- Imagen -->
      <div class="adm-field">
        <label for="imagen">Imagen de portada</label>
        <input id="imagen" name="imagen" type="file"
               accept="image/jpeg,image/png,image/webp"
               onchange="previewImagen(this,'img-preview')">
        <span class="adm-field__hint">JPG, PNG o WebP. Máximo 5 MB.</span>
        <img id="img-preview" class="adm-field__preview" alt="Vista previa">
      </div>

      <!-- Fuente imagen -->
      <div class="adm-field">
        <label for="imagen_autor">Crédito / fuente de la imagen</label>
        <input id="imagen_autor" name="imagen_autor" type="text"
               placeholder="Ej: Unsplash / Freepik"
               value="<?php echo h($d['imagen_autor']); ?>">
      </div>

      <!-- Estado -->
      <div class="adm-field">
        <label for="estado">Estado</label>
        <select id="estado" name="estado">
          <option value="publicado" <?php echo $d['estado']==='publicado'?'selected':''; ?>>Publicado</option>
          <option value="borrador"  <?php echo $d['estado']==='borrador' ?'selected':''; ?>>Borrador</option>
        </select>
      </div>

    </div><!-- /grid -->

    <div class="adm-form-actions">
      <button class="adm-btn adm-btn--primary" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
        Guardar artículo
      </button>
      <a class="adm-btn adm-btn--ghost" href="/admin/blog/">Cancelar</a>
    </div>
  </form>
</div>

<script>
function autoSlug(titulo) {
  const slug = titulo.toLowerCase()
    .replace(/[áàä]/g,'a').replace(/[éèë]/g,'e').replace(/[íìï]/g,'i')
    .replace(/[óòö]/g,'o').replace(/[úùü]/g,'u').replace(/ñ/g,'n')
    .replace(/[^a-z0-9\s-]/g,'').trim().replace(/[\s-]+/g,'-');
  document.getElementById('slug').value = slug;
  document.getElementById('slug-preview').textContent = slug;
}
document.getElementById('slug').addEventListener('input', function(){
  document.getElementById('slug-preview').textContent = this.value;
});
</script>

<?php require __DIR__ . '/../../admin/includes/admin-footer.php'; ?>
