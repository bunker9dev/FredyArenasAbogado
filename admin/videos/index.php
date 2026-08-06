<?php
require_once __DIR__ . '/../../admin/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/functions/helpers.php';

$videos = []; $db = db_connect();
if ($db) {
    $res = $db->query("SELECT id, titulo, plataforma, video_id, area, estado, created_at FROM videos ORDER BY created_at DESC");
    if ($res) while ($r = $res->fetch_assoc()) $videos[] = $r;
}

$flash = $_GET['ok'] ?? '';
$adm_title = 'Videos'; $adm_nav_active = 'videos';
require __DIR__ . '/../../admin/includes/admin-head.php';
require __DIR__ . '/../../admin/includes/admin-layout.php';
?>
<?php if ($flash === 'creado'): ?><div class="adm-alert adm-alert--success">Video agregado.</div><?php endif; ?>
<?php if ($flash === 'editado'): ?><div class="adm-alert adm-alert--success">Video actualizado.</div><?php endif; ?>
<?php if ($flash === 'borrado'): ?><div class="adm-alert adm-alert--success">Video eliminado.</div><?php endif; ?>

<div class="adm-page-head">
  <div><h1>Videos</h1><p><?php echo count($videos); ?> video<?php echo count($videos)!==1?'s':''; ?></p></div>
  <a class="adm-btn adm-btn--primary" href="/admin/videos/crear.php">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
    Nuevo video
  </a>
</div>
<div class="adm-table-wrap">
<?php if (!empty($videos)): ?>
<table class="adm-table">
  <thead><tr><th>Miniatura</th><th>Título</th><th>Plataforma</th><th>Área</th><th>Estado</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($videos as $v):
    $isYT  = $v['plataforma'] === 'youtube';
    $thumb = $isYT ? "https://img.youtube.com/vi/{$v['video_id']}/mqdefault.jpg" : '/assets/img/logo_fredyarenasabogado_412x366.webp';
  ?>
  <tr>
    <td><img class="adm-table__img" src="<?php echo h($thumb); ?>" alt="" onerror="this.src='/assets/img/logo_fredyarenasabogado_412x366.webp'"></td>
    <td><span class="adm-table__title"><?php echo h($v['titulo']); ?></span></td>
    <td><span class="adm-badge adm-badge--<?php echo $isYT?'yt':'tt'; ?>"><?php echo $isYT?'YouTube':'TikTok'; ?></span></td>
    <td><span class="adm-table__area"><?php echo h($v['area']); ?></span></td>
    <td><span class="adm-badge adm-badge--<?php echo $v['estado']==='publicado'?'pub':'bor'; ?>"><?php echo $v['estado']==='publicado'?'Publicado':'Borrador'; ?></span></td>
    <td><div class="adm-table__actions">
      <a class="adm-btn adm-btn--ghost adm-btn--sm" href="/admin/videos/editar.php?id=<?php echo (int)$v['id']; ?>">Editar</a>
      <button class="adm-btn adm-btn--danger adm-btn--sm" type="button" onclick="confirmarBorrar('/admin/videos/borrar.php?id=<?php echo (int)$v['id']; ?>')">Borrar</button>
    </div></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<div class="adm-empty"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M10 9l5 3-5 3V9z"/></svg><p>Aún no hay videos. <a href="/admin/videos/crear.php" style="color:var(--gold)">Agrega el primero.</a></p></div>
<?php endif; ?>
</div>
<?php require __DIR__ . '/../../admin/includes/admin-footer.php'; ?>
