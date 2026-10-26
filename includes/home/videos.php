<?php
/**
 * videos.php — Sección de videos en la home.
 * Muestra solo los 3 últimos videos publicados; el resto vive en /videos.php.
 */

$videos_home = [];
$db_videos = db_connect();

if ($db_videos) {
    $res = $db_videos->query(
        "SELECT id, titulo, plataforma, video_id, thumbnail_url, url_original, descripcion, area
         FROM videos WHERE estado = 'publicado'
         ORDER BY created_at DESC LIMIT 3"
    );
    if ($res) while ($row = $res->fetch_assoc()) $videos_home[] = $row;
}

if (empty($videos_home)) return; // no se muestra la sección si no hay videos publicados
?>
<!-- ── Sección Videos ──────────────────────────────────── -->
<section class="blk" id="videos">
  <div class="wrap">
    <div class="blog-top rv">
      <div>
        <p class="eyebrow">Contenido audiovisual</p>
        <h2 style="margin-top:1rem">Videos</h2>
      </div>
      <a class="see-all" href="/videos.php">
        Ver todos los videos
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M5 12h14M13 6l6 6-6 6"/>
        </svg>
      </a>
    </div>

    <div class="cards video-cards rv">
      <?php foreach ($videos_home as $v): include __DIR__ . '/../partials/tarjeta-video.php'; endforeach; ?>
    </div>
  </div>
</section>