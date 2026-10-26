<?php
/**
 * tarjeta-video.php — Tarjeta de video reutilizable (home y /videos.php).
 * Requiere $v en scope: id, titulo, plataforma, video_id, thumbnail_url,
 * url_original, descripcion, area.
 *
 * No carga el reproductor real hasta que el usuario hace clic (facade con
 * miniatura + botón de play). Al hacer clic también se registra la vista.
 */
$es_yt = $v['plataforma'] === 'youtube';
if ($es_yt) {
    $thumb = 'https://img.youtube.com/vi/' . rawurlencode($v['video_id']) . '/hqdefault.jpg';
} else {
    // Miniatura real obtenida de la API de TikTok al guardar el video;
    // si no se pudo obtener, usamos el logo como respaldo.
    $thumb = !empty($v['thumbnail_url']) ? $v['thumbnail_url'] : '/assets/img/logo_fredyarenasabogado_412x366.webp';
}
?>
<article class="card video-card">
  <div class="video-card__player video-card__player--<?php echo $es_yt ? 'yt' : 'tt'; ?>"
       data-db-id="<?php echo (int)($v['id'] ?? 0); ?>"
       data-platform="<?php echo $es_yt ? 'youtube' : 'tiktok'; ?>"
       data-id="<?php echo h($v['video_id']); ?>"
       data-cite="<?php echo h($v['url_original'] ?? ''); ?>"
       data-title="<?php echo h($v['titulo']); ?>">
    <button class="video-card__facade" type="button" aria-label="Reproducir video: <?php echo h($v['titulo']); ?>">
      <img class="video-card__thumb" src="<?php echo h($thumb); ?>" alt="" loading="lazy">
      <span class="video-card__playicon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
      </span>
    </button>
  </div>
  <div class="card__body">
    <span class="card__cat"><?php echo h($v['area']); ?></span>
    <h3><?php echo h($v['titulo']); ?></h3>
    <?php if (!empty($v['descripcion'])): ?>
    <p class="card__excerpt"><?php echo h($v['descripcion']); ?></p>
    <?php endif; ?>

    <!-- Compartir -->
    <div class="video-card__share">
      <button class="share-btn share-btn--wa share-btn--sm video-share-wa" type="button"
              data-url="<?php echo h($v['url_original'] ?? ''); ?>"
              data-title="<?php echo h($v['titulo']); ?>">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.5 14.4c-.3-.15-1.7-.85-2-.95-.26-.1-.45-.15-.64.15-.19.28-.73.94-.9 1.13-.16.19-.33.21-.62.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.64-2.04-.17-.3-.02-.46.13-.6.13-.13.3-.34.44-.51.15-.17.2-.29.3-.48.1-.19.05-.36-.02-.5-.08-.15-.64-1.54-.88-2.11-.23-.55-.47-.48-.64-.49h-.55c-.19 0-.5.07-.76.36-.26.28-1 .98-1 2.39s1.02 2.77 1.17 2.96c.15.19 2.02 3.08 4.9 4.32.68.29 1.22.47 1.63.6.69.22 1.31.19 1.8.11.55-.08 1.7-.69 1.94-1.36.24-.67.24-1.24.17-1.36-.07-.12-.26-.19-.55-.34z"/>
          <path d="M12 2a10 10 0 00-8.6 15.06L2 22l5.06-1.33A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.18-1.14l-.3-.18-3 .79.8-2.92-.2-.31A8.2 8.2 0 1112 20.2z"/>
        </svg>
        WhatsApp
      </button>
      <button class="share-btn share-btn--sm video-share-link" type="button"
              data-url="<?php echo h($v['url_original'] ?? ''); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round">
          <path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1"/>
          <path d="M14 11a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1"/>
        </svg>
        <span>Copiar enlace</span>
      </button>
    </div>
  </div>
</article>