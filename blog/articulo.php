<?php
/**
 * blog/articulo.php — Página de artículo individual
 *
 * Ruta limpia: /blog/slug-del-articulo
 * El .htaccess redirige: /blog/([a-z0-9-]+) → /blog/articulo.php?slug=$1
 *
 * Incluye:
 *  - Contador de vistas (1 vista/IP cada 4h, vía API JS)
 *  - Compartir: WhatsApp + copiar enlace
 *  - ¿Te resultó útil? Sí / No (discreto, sin puntaje público)
 *  - Artículos relacionados (misma área, hasta 3)
 */

require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/functions/helpers.php';

/* ── Obtener slug ─────────────────────────────────────────── */
$slug = trim($_GET['slug'] ?? '');
if (!$slug || !preg_match('/^[a-z0-9-]+$/', $slug)) {
    http_response_code(404);
    header('Location: /blog.php');
    exit;
}

/* ── Cargar artículo desde la BD ─────────────────────────── */
$articulo = null;
$db = db_connect();

if ($db) {
    $st = $db->prepare(
        "SELECT id, titulo, slug, area, fecha, autor_nombre, autor_apellido,
                texto_inicio, texto_completo, imagen, imagen_autor, vistas
         FROM blog
         WHERE slug = ? AND estado = 'publicado'
         LIMIT 1"
    );
    $st->bind_param('s', $slug);
    $st->execute();
    $articulo = $st->get_result()->fetch_assoc();
}

if (!$articulo) {
    http_response_code(404);
    header('Location: /blog.php');
    exit;
}

/* ── Artículos relacionados (misma área, excluyendo el actual) ── */
$relacionados = [];
if ($db) {
    $st2 = $db->prepare(
        "SELECT titulo, slug, area, fecha, autor_nombre, autor_apellido, imagen
         FROM blog
         WHERE area = ? AND id != ? AND estado = 'publicado'
         ORDER BY fecha DESC
         LIMIT 3"
    );
    $st2->bind_param('si', $articulo['area'], $articulo['id']);
    $st2->execute();
    $res2 = $st2->get_result();
    while ($row = $res2->fetch_assoc()) $relacionados[] = $row;
}

/* ── Tiempo estimado de lectura ──────────────────────────── */
$palabras = str_word_count(strip_tags($articulo['texto_completo'] ?? ''));
$minutos  = max(1, (int)round($palabras / 200));

/* ── Variables para el head ──────────────────────────────── */
$page_title     = h($articulo['titulo']) . ' · Fredy Arenas Abogado';
$page_desc      = h(mb_strimwidth(strip_tags($articulo['texto_inicio']), 0, 155, '…'));
$page_canonical = 'https://fredyarenasabogado.com/blog/' . h($articulo['slug']);
$page_og_image  = !empty($articulo['imagen'])
    ? 'https://fredyarenasabogado.com/imagenes/' . rawurlencode($articulo['imagen'])
    : 'https://fredyarenasabogado.com/assets/img/perfil-fredyarenas5.webp';
$current_page   = 'blog';
$cargar_articulo_js = true;

require __DIR__ . '/../includes/templates/head.php';
require __DIR__ . '/../includes/templates/header.php';
?>

<article>

  <!-- Cabecera del artículo -->
  <div class="art-col">
    <a class="art-back" href="/blog.php">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M19 12H5M11 6l-6 6 6 6"/>
      </svg>
      Volver al blog
    </a>

    <div class="art-head">
      <span class="eyebrow"><?php echo h($articulo['area']); ?></span>
      <h1><?php echo h($articulo['titulo']); ?></h1>

      <div class="art-meta">
        <span class="art-meta__who">
          <img class="art-meta__avatar"
               src="/assets/img/perfil-fredyarenas5.webp"
               alt="Fredy A. Arenas">
          <?php echo h($articulo['autor_nombre'] . ' ' . $articulo['autor_apellido']); ?>
        </span>

        <span class="art-meta__sep"></span>
        <span><?php echo fecha_es($articulo['fecha']); ?></span>

        <span class="art-meta__sep"></span>
        <span><?php echo $minutos; ?> min de lectura</span>

        <span class="art-meta__sep"></span>
        <span class="art-meta__views" id="vistas-display">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
          <?php echo number_format($articulo['vistas'], 0, ',', '.'); ?> vistas
        </span>
      </div>
    </div>
  </div><!-- /art-col cabecera -->

  <!-- Imagen hero -->
  <?php if (!empty($articulo['imagen'])): ?>
  <div class="art-hero">
    <img src="/imagenes/<?php echo rawurlencode($articulo['imagen']); ?>"
         alt="<?php echo h($articulo['titulo']); ?>">
  </div>
  <?php endif; ?>

  <!-- Contenido del artículo -->
  <div class="art-col art-body">
    <?php
    // El texto_completo puede contener HTML básico (<p>,<h2>,<ul>,<strong>,etc.)
    // htmlspecialchars_decode porque viene de la BD como texto plano o HTML guardado
    echo $articulo['texto_completo'];
    ?>
  </div>

  <?php if (!empty($articulo['imagen_autor'])): ?>
  <div class="art-col" style="margin-top:.5rem">
    <p style="font-size:.75rem;color:var(--ink-soft)">
      Imagen: <?php echo h($articulo['imagen_autor']); ?>
    </p>
  </div>
  <?php endif; ?>

  <!-- Compartir + ¿Te resultó útil? -->
  <div class="art-col">

    <!-- Barra de compartir -->
    <div class="art-share">
      <span class="art-share__lbl">Compartir</span>
      <button class="share-btn share-btn--wa" id="share-wa" type="button">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.5 14.4c-.3-.15-1.7-.85-2-.95-.26-.1-.45-.15-.64.15-.19.28-.73.94-.9 1.13-.16.19-.33.21-.62.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.64-2.04-.17-.3-.02-.46.13-.6.13-.13.3-.34.44-.51.15-.17.2-.29.3-.48.1-.19.05-.36-.02-.5-.08-.15-.64-1.54-.88-2.11-.23-.55-.47-.48-.64-.49h-.55c-.19 0-.5.07-.76.36-.26.28-1 .98-1 2.39s1.02 2.77 1.17 2.96c.15.19 2.02 3.08 4.9 4.32.68.29 1.22.47 1.63.6.69.22 1.31.19 1.8.11.55-.08 1.7-.69 1.94-1.36.24-.67.24-1.24.17-1.36-.07-.12-.26-.19-.55-.34z"/>
          <path d="M12 2a10 10 0 00-8.6 15.06L2 22l5.06-1.33A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.18-1.14l-.3-.18-3 .79.8-2.92-.2-.31A8.2 8.2 0 1112 20.2z"/>
        </svg>
        WhatsApp
      </button>
      <button class="share-btn" id="share-link" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round">
          <path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1"/>
          <path d="M14 11a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1"/>
        </svg>
        <span>Copiar enlace</span>
      </button>
    </div>

    <!-- ¿Te resultó útil? -->
    <div class="art-useful" id="useful"
         data-id="<?php echo (int)$articulo['id']; ?>">
      <p class="art-useful__q">¿Te resultó útil este artículo?</p>
      <div class="art-useful__btns">
        <button class="useful-btn" data-v="si" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M7 11v9M2 11h5v9H2zM14 4l-2 7h6.5a2 2 0 012 2.4l-1.3 6A2 2 0 0117.2 21H7"/>
          </svg>
          Sí
        </button>
        <button class="useful-btn" data-v="no" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 13V4M22 13h-5V4h5zM10 20l2-7H5.5a2 2 0 01-2-2.4l1.3-6A2 2 0 016.8 3H17"/>
          </svg>
          No
        </button>
      </div>
      <p class="art-useful__thanks">Gracias por tu opinión.</p>
    </div>

  </div><!-- /art-col compartir -->

  <!-- Artículos relacionados -->
  <?php if (!empty($relacionados)): ?>
  <div class="art-related wrap">
    <h2>Artículos relacionados</h2>
    <div class="cards">
      <?php foreach ($relacionados as $r):
        $r_autor = trim(h($r['autor_nombre']) . ' ' . h($r['autor_apellido']));
        $r_url   = '/blog/' . h($r['slug']);
        $r_img   = imagen_blog($r['imagen'] ?? '');
      ?>
      <article class="card">
        <a href="<?php echo $r_url; ?>" class="card__img">
          <img loading="lazy" src="<?php echo $r_img; ?>"
               alt="<?php echo h($r['titulo']); ?>">
        </a>
        <div class="card__body">
          <span class="card__cat"><?php echo h($r['area']); ?></span>
          <h3><a href="<?php echo $r_url; ?>"><?php echo h($r['titulo']); ?></a></h3>
          <div class="card__meta">
            <span><?php echo fecha_es($r['fecha']); ?></span>
            <span class="dot"></span>
            <span><?php echo $r_autor ?: 'Fredy A. Arenas'; ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</article>

<!-- Toast de notificación -->
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php require __DIR__ . '/../includes/templates/footer.php'; ?>
