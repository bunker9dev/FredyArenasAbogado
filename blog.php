<?php
/**
 * blog.php — Listado completo de artículos
 * URL: /blog.php  o  /blog/  (según .htaccess)
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
];

/* ── Parámetros de búsqueda / filtro ────────────────────── */
$q    = trim($_GET['q']    ?? '');
$area = trim($_GET['area'] ?? '');
$pag  = max(1, (int)($_GET['p'] ?? 1));
$por_pag = 9;
$offset  = ($pag - 1) * $por_pag;

/* ── Consulta a la BD ────────────────────────────────────── */
$articulos = [];
$total     = 0;
$db        = db_connect();

if ($db) {
    // Construir WHERE dinámico con placeholders
    $where  = ["estado = 'publicado'"];
    $params = [];
    $tipos  = '';

    if ($q !== '') {
        $where[]  = "(titulo LIKE ? OR texto_inicio LIKE ?)";
        $like     = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;
        $tipos   .= 'ss';
    }
    if ($area !== '' && in_array($area, $areas, true)) {
        $where[]  = "area = ?";
        $params[] = $area;
        $tipos   .= 's';
    }

    $sql_where = 'WHERE ' . implode(' AND ', $where);

    // Total para paginación
    $sql_count = "SELECT COUNT(*) FROM blog $sql_where";
    $st = $db->prepare($sql_count);
    if ($params) $st->bind_param($tipos, ...$params);
    $st->execute();
    $st->bind_result($total);
    $st->fetch();
    $st->close();

    // Artículos de esta página
    $sql = "SELECT id, titulo, slug, area, fecha,
                   autor_nombre, autor_apellido, imagen, texto_inicio, vistas
            FROM blog $sql_where
            ORDER BY fecha DESC
            LIMIT ? OFFSET ?";
    $st2 = $db->prepare($sql);
    $p2  = array_merge($params, [$por_pag, $offset]);
    $t2  = $tipos . 'ii';
    $st2->bind_param($t2, ...$p2);
    $st2->execute();
    $res = $st2->get_result();
    while ($row = $res->fetch_assoc()) $articulos[] = $row;
}

$total_pags = $total > 0 ? (int)ceil($total / $por_pag) : 1;

/* ── Helper de paginación ────────────────────────────────── */
function url_pag(int $p, string $q, string $area): string {
    $qs = http_build_query(array_filter(['q'=>$q,'area'=>$area,'p'=>$p>1?$p:null]));
    return '/blog.php' . ($qs ? '?' . $qs : '');
}

/* ── Variables para el head ──────────────────────────────── */
$page_title     = 'Blog · Análisis y notas jurídicas — Fredy Arenas Abogado';
$page_desc      = 'Artículos prácticos sobre derecho administrativo, disciplinario, penal militar, pensional y más.';
$page_canonical = 'https://fredyarenasabogado.com/blog.php';
$current_page   = 'blog';

require __DIR__ . '/includes/templates/head.php';
require __DIR__ . '/includes/templates/header.php';
?>

<!-- Cabecera de página -->
<div class="page-head">
  <div class="wrap">
    <p class="eyebrow">Blog</p>
    <h1>Análisis y notas jurídicas</h1>
    <p class="page-head__sub">Comentarios prácticos sobre derecho administrativo,
       disciplinario, penal militar, pensional y más.<br>
       Escritos para aclarar, no para complicar.</p>
  </div>
</div>

<main>
  <div class="wrap">

    <!-- Barra de búsqueda + filtros -->
    <form class="toolbar" method="GET" action="/blog.php" role="search">
      <div class="search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke-width="2">
          <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3" stroke-linecap="round"/>
        </svg>
        <input id="q" name="q" type="search"
               placeholder="Buscar por título o tema…"
               value="<?php echo h($q); ?>"
               autocomplete="off">
      </div>
      <div class="filters">
        <a class="chip <?php echo $area==='' ? 'active' : ''; ?>"
           href="/blog.php<?php echo $q ? '?q='.urlencode($q) : ''; ?>">Todos</a>
        <?php foreach ($areas as $ar): ?>
        <a class="chip <?php echo $area===$ar ? 'active' : ''; ?>"
           href="<?php echo '/blog.php?' . http_build_query(array_filter(['q'=>$q,'area'=>$ar])); ?>">
          <?php echo h($ar); ?>
        </a>
        <?php endforeach; ?>
      </div>
    </form>

    <!-- Grid de artículos -->
    <div class="cards">
      <?php if (!empty($articulos)): ?>
        <?php foreach ($articulos as $a):
          $autor = trim(h($a['autor_nombre']) . ' ' . h($a['autor_apellido']));
          $url   = '/blog/' . h($a['slug']);
          $img   = imagen_blog($a['imagen'] ?? '');
        ?>
        <article class="card">
          <a href="<?php echo $url; ?>" class="card__img">
            <img loading="lazy" src="<?php echo $img; ?>" alt="<?php echo h($a['titulo']); ?>">
          </a>
          <div class="card__body">
            <span class="card__cat"><?php echo h($a['area']); ?></span>
            <h3><a href="<?php echo $url; ?>"><?php echo h($a['titulo']); ?></a></h3>
            <?php if (!empty($a['texto_inicio'])): ?>
            <p class="card__excerpt"><?php echo h(mb_strimwidth($a['texto_inicio'], 0, 110, '…')); ?></p>
            <?php endif; ?>
            <div class="card__meta">
              <span><?php echo fecha_es($a['fecha']); ?></span>
              <span class="dot"></span>
              <span><?php echo $autor ?: 'Fredy A. Arenas'; ?></span>
              <?php if (!empty($a['vistas'])): ?>
              <span class="dot"></span>
              <span style="display:inline-flex;align-items:center;gap:.3rem">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                     stroke="var(--gold)" stroke-width="2">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <?php echo number_format($a['vistas'], 0, ',', '.'); ?>
              </span>
              <?php endif; ?>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="empty">
          <?php echo $q || $area
            ? 'No se encontraron artículos para tu búsqueda.'
            : 'Pronto publicaremos nuestros primeros artículos.'; ?>
        </p>
      <?php endif; ?>
    </div>

    <!-- Paginación -->
    <?php if ($total_pags > 1): ?>
    <nav class="paginacion" aria-label="Páginas">
      <?php if ($pag > 1): ?>
      <a class="pag-btn" href="<?php echo url_pag($pag-1, $q, $area); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M19 12H5M11 6l-6 6 6 6"/>
        </svg>
        Anterior
      </a>
      <?php endif; ?>
      <span class="pag-info">Página <?php echo $pag; ?> de <?php echo $total_pags; ?></span>
      <?php if ($pag < $total_pags): ?>
      <a class="pag-btn" href="<?php echo url_pag($pag+1, $q, $area); ?>">
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
