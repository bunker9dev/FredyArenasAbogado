<?php
/**
 * videos.php — Listado completo de videos
 * URL: /videos.php
 */

require_once __DIR__ . '/includes/config/database.php';
require_once __DIR__ . '/includes/functions/helpers.php';

/* ── Áreas de práctica (filtros) ─────────────────────────── */
$areas = [
    'Derecho Administrativo',
    'Derecho Disciplinario',
    'Justicia Penal Militar',
    'Derecho Laboral - Pensión',
    'Responsabilidad Médica',
    'Insolvencia',
    'General',
];

/* ── Parámetros de búsqueda / filtro ────────────────────── */
$area = trim($_GET['area'] ?? '');
$pag  = max(1, (int)($_GET['p'] ?? 1));
$por_pag = 9;
$offset  = ($pag - 1) * $por_pag;

/* ── Consulta a la BD ────────────────────────────────────── */
$videos = [];
$total  = 0;
$db     = db_connect();

if ($db) {
    $where  = ["estado = 'publicado'"];
    $params = [];
    $tipos  = '';

    if ($area !== '' && in_array($area, $areas, true)) {
        $where[]  = "area = ?";
        $params[] = $area;
        $tipos   .= 's';
    }

    $sql_where = 'WHERE ' . implode(' AND ', $where);

    $sql_count = "SELECT COUNT(*) FROM videos $sql_where";
    $st = $db->prepare($sql_count);
    if ($params) $st->bind_param($tipos, ...$params);
    $st->execute();
    $st->bind_result($total);
    $st->fetch();
    $st->close();

    $sql = "SELECT id, titulo, plataforma, video_id, thumbnail_url, url_original, descripcion, area
            FROM videos $sql_where
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";
    $st2 = $db->prepare($sql);
    $p2  = array_merge($params, [$por_pag, $offset]);
    $t2  = $tipos . 'ii';
    $st2->bind_param($t2, ...$p2);
    $st2->execute();
    $res = $st2->get_result();
    while ($row = $res->fetch_assoc()) $videos[] = $row;
}

$total_pags = $total > 0 ? (int)ceil($total / $por_pag) : 1;

/* ── Helper de paginación ────────────────────────────────── */
function url_pag_video(int $p, string $area): string {
    $qs = http_build_query(array_filter(['area'=>$area,'p'=>$p>1?$p:null]));
    return '/videos.php' . ($qs ? '?' . $qs : '');
}

/* ── Variables para el head ──────────────────────────────── */
$page_title     = 'Videos · Fredy Arenas Abogado';
$page_desc      = 'Videos explicativos sobre derecho administrativo, disciplinario, penal militar, pensional y más.';
$page_canonical = 'https://fredyarenasabogado.com/videos.php';
$current_page   = 'videos';

require __DIR__ . '/includes/templates/head.php';
require __DIR__ . '/includes/templates/header.php';
?>

<!-- Cabecera de página -->
<div class="page-head">
  <div class="wrap">
    <p class="eyebrow">Videos</p>
    <h1>Contenido audiovisual</h1>
    <p class="page-head__sub">Explicaciones breves sobre derecho administrativo,
       disciplinario, penal militar, pensional y más.<br>
       Haz clic en cualquier video para reproducirlo.</p>
  </div>
</div>

<main>
  <div class="wrap">

    <!-- Filtros por área -->
    <form class="toolbar" method="GET" action="/videos.php">
      <div class="filters">
        <a class="chip <?php echo $area==='' ? 'active' : ''; ?>" href="/videos.php">Todos</a>
        <?php foreach ($areas as $ar): ?>
        <a class="chip <?php echo $area===$ar ? 'active' : ''; ?>"
           href="/videos.php?<?php echo http_build_query(array_filter(['area'=>$ar])); ?>">
          <?php echo h($ar); ?>
        </a>
        <?php endforeach; ?>
      </div>
    </form>

    <!-- Grid de videos -->
    <div class="cards video-cards">
      <?php if (!empty($videos)): ?>
        <?php foreach ($videos as $v): include __DIR__ . '/includes/partials/tarjeta-video.php'; endforeach; ?>
      <?php else: ?>
        <p class="empty">
          <?php echo $area ? 'No hay videos para esta área todavía.' : 'Pronto publicaremos nuestros primeros videos.'; ?>
        </p>
      <?php endif; ?>
    </div>

    <!-- Paginación -->
    <?php if ($total_pags > 1): ?>
    <nav class="paginacion" aria-label="Páginas">
      <?php if ($pag > 1): ?>
      <a class="pag-btn" href="<?php echo url_pag_video($pag-1, $area); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M19 12H5M11 6l-6 6 6 6"/>
        </svg>
        Anterior
      </a>
      <?php endif; ?>
      <span class="pag-info">Página <?php echo $pag; ?> de <?php echo $total_pags; ?></span>
      <?php if ($pag < $total_pags): ?>
      <a class="pag-btn" href="<?php echo url_pag_video($pag+1, $area); ?>">
        Siguiente
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M5 12h14M13 6l6 6-6 6"/>
        </svg>
      </a>
      <?php endif; ?>
    </nav>
    <?php endif; ?>

  </div><!-- /wrap -->
</main>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>