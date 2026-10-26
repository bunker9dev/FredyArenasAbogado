<?php
require_once __DIR__ . '/../../bunker/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';

$areas = ['Derecho Administrativo','Derecho Disciplinario','Justicia Penal Militar',
          'Derecho Laboral - Pensión','Responsabilidad Médica','Insolvencia','General'];

$errores = [];
$d = ['titulo'=>'','url_original'=>'','video_id'=>'','plataforma'=>'youtube',
      'descripcion'=>'','area'=>'General','estado'=>'publicado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) { $errores[] = 'Sesión expirada.'; }
    else {
        $d['titulo']       = trim($_POST['titulo']       ?? '');
        $d['url_original'] = trim($_POST['url_original'] ?? '');
        $d['video_id']     = trim($_POST['video_id']     ?? '');
        $d['plataforma']   = $_POST['plataforma'] === 'tiktok' ? 'tiktok' : 'youtube';
        $d['descripcion']  = trim($_POST['descripcion']  ?? '');
        $d['area']         = trim($_POST['area']         ?? 'General');
        $d['estado']       = $_POST['estado'] === 'borrador' ? 'borrador' : 'publicado';

        if (!$d['titulo'])       $errores[] = 'El título es obligatorio.';
        if (!$d['url_original']) $errores[] = 'La URL del video es obligatoria.';
        if (!$d['video_id'])     $errores[] = 'No se pudo extraer el ID del video. Verifica la URL.';

        if (empty($errores)) {
            // Si es TikTok, pedirle a su API la miniatura real del video
            // (el fotograma inicial), para no mostrar el logo genérico.
            $thumbnail_url = $d['plataforma'] === 'tiktok'
                ? obtener_miniatura_tiktok($d['url_original'])
                : '';

            $db = db_connect();
            $st = $db->prepare(
                "INSERT INTO videos (titulo, plataforma, video_id, thumbnail_url, url_original, descripcion, area, estado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $st->bind_param('ssssssss',
                $d['titulo'],$d['plataforma'],$d['video_id'],$thumbnail_url,
                $d['url_original'],$d['descripcion'],$d['area'],$d['estado']);
            if ($st->execute()) { header('Location: /bunker/videos/?ok=creado'); exit; }
            $errores[] = 'Error al guardar en la base de datos.';
        }
    }
}

$adm_title = 'Nuevo video'; $adm_nav_active = 'vid-crear';
require __DIR__ . '/../../bunker/includes/admin-head.php';
require __DIR__ . '/../../bunker/includes/admin-layout.php';
?>

<div class="adm-page-head">
  <div><h1>Nuevo video</h1><p>Pega el enlace de YouTube o TikTok.</p></div>
  <a class="adm-btn adm-btn--ghost" href="/bunker/videos/">← Volver</a>
</div>

<?php foreach ($errores as $e): ?>
  <div class="adm-alert adm-alert--error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="adm-alert adm-alert--warn">
  El reproductor se genera automáticamente a partir de la URL. Solo pega el enlace completo.
</div>

<div class="adm-form-wrap">
  <form method="POST" action="/bunker/videos/crear.php" autocomplete="off">
    <?php echo csrf_campo(); ?>
    <div class="adm-form-grid">

      <div class="adm-field span2">
        <label for="url_original">URL del video *</label>
        <input id="url_original" name="url_original" type="url"
               placeholder="https://www.youtube.com/watch?v=... o https://www.tiktok.com/@.../video/..."
               value="<?php echo h($d['url_original']); ?>"
               oninput="previewVideo(this)" required>
        <span class="adm-field__hint">YouTube o TikTok. El ID y la plataforma se detectan solos.</span>
        <div class="video-preview" id="video-preview">
          <iframe id="video-iframe" allowfullscreen></iframe>
        </div>
      </div>

      <!-- Campos auto-rellenados por JS -->
      <div class="adm-field" style="display:none">
        <input id="video_id"    name="video_id"    type="hidden" value="<?php echo h($d['video_id']); ?>">
        <input id="plataforma"  name="plataforma"  type="hidden" value="<?php echo h($d['plataforma']); ?>">
      </div>

      <div class="adm-field span2">
        <label for="titulo">Título *</label>
        <input id="titulo" name="titulo" type="text"
               placeholder="Ej: ¿Qué es el derecho disciplinario?"
               value="<?php echo h($d['titulo']); ?>" required>
      </div>

      <div class="adm-field">
        <label for="area">Área</label>
        <select id="area" name="area">
          <?php foreach ($areas as $ar): ?>
          <option value="<?php echo h($ar); ?>" <?php echo $d['area']===$ar?'selected':''; ?>>
            <?php echo h($ar); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="adm-field">
        <label for="estado">Estado</label>
        <select id="estado" name="estado">
          <option value="publicado" <?php echo $d['estado']==='publicado'?'selected':''; ?>>Publicado</option>
          <option value="borrador"  <?php echo $d['estado']==='borrador' ?'selected':''; ?>>Borrador</option>
        </select>
      </div>

      <div class="adm-field span2">
        <label for="descripcion">Descripción breve</label>
        <textarea id="descripcion" name="descripcion"
                  placeholder="Una frase que acompañe el video en el sitio…"><?php echo h($d['descripcion']); ?></textarea>
      </div>

    </div>
    <div class="adm-form-actions">
      <button class="adm-btn adm-btn--primary" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
        Guardar video
      </button>
      <a class="adm-btn adm-btn--ghost" href="/bunker/videos/">Cancelar</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../../bunker/includes/admin-footer.php'; ?>