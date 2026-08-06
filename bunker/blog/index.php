<?php
require_once __DIR__ . '/../../bunker/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';

/* ── Parámetros ──────────────────────────────────────────── */
$q      = trim($_GET['q']      ?? '');
$estado = trim($_GET['estado'] ?? '');
$pag    = max(1, (int)($_GET['p'] ?? 1));
$por_pag = 15;
$offset  = ($pag - 1) * $por_pag;

/* ── Consulta ────────────────────────────────────────────── */
$articulos = []; $total = 0;
$db = db_connect();
if ($db) {
    $where = []; $params = []; $tipos = '';

    if ($q !== '') {
        $where[] = "(titulo LIKE ? OR area LIKE ?)";
        $like    = '%'.$q.'%'; $params[] = $like; $params[] = $like; $tipos .= 'ss';
    }
    if (in_array($estado, ['publicado','borrador'], true)) {
        $where[] = "estado = ?"; $params[] = $estado; $tipos .= 's';
    }

    $sw = $where ? 'WHERE '.implode(' AND ',$where) : '';

    $st = $db->prepare("SELECT COUNT(*) FROM blog $sw");
    if ($params) $st->bind_param($tipos, ...$params);
    $st->execute(); $st->bind_result($total); $st->fetch(); $st->close();

    $st2 = $db->prepare(
        "SELECT id, titulo, slug, area, fecha, estado, vistas, imagen
         FROM blog $sw ORDER BY fecha DESC LIMIT ? OFFSET ?");
    $p2 = array_merge($params, [$por_pag, $offset]);
    $st2->bind_param($tipos.'ii', ...$p2);
    $st2->execute();
    $res = $st2->get_result();
    while ($row = $res->fetch_assoc()) $articulos[] = $row;
}
$total_pags = max(1, (int)ceil($total / $por_pag));

/* ── Flash message ───────────────────────────────────────── */
$flash = $_GET['ok'] ?? '';

$adm_title      = 'Artículos';
$adm_nav_active = 'articulos';
require __DIR__ . '/../../bunker/includes/admin-head.php';
require __DIR__ . '/../../bunker/includes/admin-layout.php';
?>

<?php if ($flash === 'creado'): ?>
  <div class="adm-alert adm-alert--success">Artículo creado correctamente.</div>
<?php elseif ($flash === 'editado'): ?>
  <div class="adm-alert adm-alert--success">Artículo actualizado correctamente.</div>
<?php elseif ($flash === 'borrado'): ?>
  <div class="adm-alert adm-alert--success">Artículo eliminado.</div>
<?php endif; ?>

<div class="adm-page-head">
  <div>
    <h1>Artículos</h1>
    <p><?php echo $total; ?> artículo<?php echo $total!==1?'s':''; ?> en total</p>
  </div>
  <a class="adm-btn adm-btn--primary" href="/bunker/blog/crear.php">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
    Nuevo artículo
  </a>
</div>

<div class="adm-table-wrap">
  <!-- Búsqueda y filtros -->
  <div class="adm-table-top">
    <form method="GET" action="/bunker/blog/" style="display:flex;gap:.8rem;flex-wrap:wrap;align-items:center">
      <div class="adm-search">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke-width="2">
          <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3" stroke-linecap="round"/>
        </svg>
        <input name="q" type="search" placeholder="Buscar…"
               value="<?php echo h($q); ?>">
      </div>
      <select name="estado" onchange="this.form.submit()"
              style="padding:.5rem .7rem;border:1px solid var(--line);border-radius:4px;font-size:.82rem;background:var(--paper);color:var(--ink)">
        <option value="">Todos los estados</option>
        <option value="publicado" <?php echo $estado==='publicado'?'selected':''; ?>>Publicados</option>
        <option value="borrador"  <?php echo $estado==='borrador' ?'selected':''; ?>>Borradores</option>
      </select>
      <?php if ($q||$estado): ?>
        <a class="adm-btn adm-btn--ghost adm-btn--sm" href="/bunker/blog/">Limpiar</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Tabla -->
  <?php if (!empty($articulos)): ?>
  <table class="adm-table">
    <thead><tr>
      <th style="width:56px"></th>
      <th>Título</th>
      <th>Área</th>
      <th>Fecha</th>
      <th>Vistas</th>
      <th>Estado</th>
      <th></th>
    </tr></thead>
    <tbody>
    <?php foreach ($articulos as $a): ?>
    <tr>
      <td>
        <?php if (!empty($a['imagen'])): ?>
        <img class="adm-table__img"
             src="<?php echo h(imagen_blog_url($a['imagen'])); ?>" alt="">
        <?php else: ?>
        <div class="adm-table__img" style="background:var(--paper-2);border-radius:2px"></div>
        <?php endif; ?>
      </td>
      <td><span class="adm-table__title"><?php echo h($a['titulo']); ?></span></td>
      <td><span class="adm-table__area"><?php echo h($a['area']); ?></span></td>
      <td><?php echo fecha_es($a['fecha']); ?></td>
      <td><?php echo number_format($a['vistas'],0,',','.'); ?></td>
      <td>
        <span class="adm-badge adm-badge--<?php echo $a['estado']==='publicado'?'pub':'bor'; ?>">
          <?php echo $a['estado']==='publicado' ? 'Publicado' : 'Borrador'; ?>
        </span>
      </td>
      <td>
        <div class="adm-table__actions">
          <a class="adm-btn adm-btn--ghost adm-btn--sm"
             href="/bunker/blog/editar.php?slug=<?php echo urlencode($a['slug']); ?>">Editar</a>
          <a class="adm-btn adm-btn--ghost adm-btn--sm"
             href="/blog/<?php echo h($a['slug']); ?>" target="_blank">Ver</a>
          <button class="adm-btn adm-btn--danger adm-btn--sm" type="button"
                  onclick="confirmarBorrar('/bunker/blog/borrar.php?id=<?php echo (int)$a['id']; ?>')">
            Borrar
          </button>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Paginación -->
  <?php if ($total_pags > 1): ?>
  <div style="display:flex;align-items:center;justify-content:center;gap:1rem;padding:1.2rem;border-top:1px solid var(--line)">
    <?php if ($pag > 1): ?>
      <a class="adm-btn adm-btn--ghost adm-btn--sm"
         href="?p=<?php echo $pag-1; ?>&q=<?php echo urlencode($q); ?>&estado=<?php echo urlencode($estado); ?>">← Anterior</a>
    <?php endif; ?>
    <span style="font-size:.82rem;color:var(--ink-soft)">Pág. <?php echo $pag; ?> / <?php echo $total_pags; ?></span>
    <?php if ($pag < $total_pags): ?>
      <a class="adm-btn adm-btn--ghost adm-btn--sm"
         href="?p=<?php echo $pag+1; ?>&q=<?php echo urlencode($q); ?>&estado=<?php echo urlencode($estado); ?>">Siguiente →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="adm-empty">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>
    <p><?php echo $q||$estado ? 'Sin resultados para tu búsqueda.' : 'Aún no hay artículos. <a href="/bunker/blog/crear.php" style="color:var(--gold)">Crea el primero.</a>'; ?></p>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../../bunker/includes/admin-footer.php'; ?>