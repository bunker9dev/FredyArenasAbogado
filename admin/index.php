<?php
require_once __DIR__ . '/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/functions/helpers.php';

/* ── Estadísticas rápidas ─────────────────────────────── */
$stats = ['articulos'=>0,'publicados'=>0,'videos'=>0,'mensajes'=>0,'mensajes_nuevos'=>0,'vistas'=>0];
$db = db_connect();
if ($db) {
    foreach ([
        'articulos'       => "SELECT COUNT(*) FROM blog",
        'publicados'      => "SELECT COUNT(*) FROM blog WHERE estado='publicado'",
        'videos'          => "SELECT COUNT(*) FROM videos",
        'mensajes'        => "SELECT COUNT(*) FROM contacto",
        'mensajes_nuevos' => "SELECT COUNT(*) FROM contacto WHERE leido=0",
        'vistas'          => "SELECT COALESCE(SUM(vistas),0) FROM blog",
    ] as $key => $sql) {
        $r = $db->query($sql);
        if ($r) { $r->bind_result($v); $r->fetch(); $stats[$key] = (int)$v; }
    }
}

/* ── Últimos artículos ────────────────────────────────── */
$ultimos = [];
$db2 = db_connect();
if ($db2) {
    $r = $db2->query("SELECT titulo, slug, area, fecha, estado, vistas FROM blog ORDER BY created_at DESC LIMIT 5");
    if ($r) while ($row = $r->fetch_assoc()) $ultimos[] = $row;
}

$adm_title      = 'Dashboard';
$adm_nav_active = 'dashboard';
require __DIR__ . '/includes/admin-head.php';
require __DIR__ . '/includes/admin-layout.php';
?>

<div class="adm-page-head">
  <div>
    <h1>Dashboard</h1>
    <p>Bienvenido, <?php echo h($_SESSION['usuario']); ?>. Aquí tienes un resumen del sitio.</p>
  </div>
  <a class="adm-btn adm-btn--primary" href="/admin/blog/crear.php">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
    Nuevo artículo
  </a>
</div>

<!-- Stats -->
<div class="adm-stats">
  <div class="adm-stat">
    <div class="adm-stat__icon">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
    </div>
    <div class="adm-stat__val"><?php echo $stats['publicados']; ?></div>
    <div class="adm-stat__lbl">Artículos publicados</div>
  </div>
  <div class="adm-stat">
    <div class="adm-stat__icon">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.5"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
    </div>
    <div class="adm-stat__val"><?php echo number_format($stats['vistas'],0,',','.'); ?></div>
    <div class="adm-stat__lbl">Vistas totales</div>
  </div>
  <div class="adm-stat">
    <div class="adm-stat__icon">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M10 9l5 3-5 3V9z"/></svg>
    </div>
    <div class="adm-stat__val"><?php echo $stats['videos']; ?></div>
    <div class="adm-stat__lbl">Videos</div>
  </div>
  <div class="adm-stat">
    <div class="adm-stat__icon">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
    </div>
    <div class="adm-stat__val">
      <?php echo $stats['mensajes']; ?>
      <?php if ($stats['mensajes_nuevos'] > 0): ?>
        <span class="adm-badge adm-badge--pub" style="font-size:.7rem;vertical-align:middle">
          <?php echo $stats['mensajes_nuevos']; ?> nuevos
        </span>
      <?php endif; ?>
    </div>
    <div class="adm-stat__lbl">Mensajes de contacto</div>
  </div>
</div>

<!-- Últimos artículos -->
<div class="adm-table-wrap">
  <div class="adm-table-top">
    <strong style="font-size:.9rem">Últimos artículos</strong>
    <a class="adm-btn adm-btn--ghost adm-btn--sm" href="/admin/blog/">Ver todos</a>
  </div>
  <?php if (!empty($ultimos)): ?>
  <table class="adm-table">
    <thead><tr>
      <th>Título</th><th>Área</th><th>Fecha</th><th>Vistas</th><th>Estado</th><th></th>
    </tr></thead>
    <tbody>
    <?php foreach ($ultimos as $a): ?>
    <tr>
      <td><span class="adm-table__title"><?php echo h($a['titulo']); ?></span></td>
      <td><span class="adm-table__area"><?php echo h($a['area']); ?></span></td>
      <td><?php echo fecha_es($a['fecha']); ?></td>
      <td><?php echo number_format($a['vistas'],0,',','.'); ?></td>
      <td><span class="adm-badge adm-badge--<?php echo $a['estado']==='publicado'?'pub':'bor'; ?>">
        <?php echo $a['estado']==='publicado' ? 'Publicado' : 'Borrador'; ?></span></td>
      <td><div class="adm-table__actions">
        <a class="adm-btn adm-btn--ghost adm-btn--sm"
           href="/admin/blog/editar.php?slug=<?php echo urlencode($a['slug']); ?>">Editar</a>
        <a class="adm-btn adm-btn--ghost adm-btn--sm"
           href="/blog/<?php echo h($a['slug']); ?>" target="_blank">Ver</a>
      </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="adm-empty">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>
    <p>Aún no hay artículos. <a href="/admin/blog/crear.php" style="color:var(--gold)">Crea el primero.</a></p>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
