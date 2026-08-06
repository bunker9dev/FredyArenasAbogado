<?php
require_once __DIR__ . '/../admin/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/functions/helpers.php';

$db = db_connect();

// Marcar como leído
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $mid = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($mid && $db) {
        $st = $db->prepare("UPDATE contacto SET leido=1 WHERE id=?");
        $st->bind_param('i', $mid); $st->execute();
    }
    header('Location: /admin/contactos.php'); exit;
}

$mensajes = [];
if ($db) {
    $res = $db->query("SELECT * FROM contacto ORDER BY created_at DESC");
    if ($res) while ($r = $res->fetch_assoc()) $mensajes[] = $r;
}

$adm_title = 'Mensajes'; $adm_nav_active = 'contactos';
require __DIR__ . '/../admin/includes/admin-head.php';
require __DIR__ . '/../admin/includes/admin-layout.php';
?>

<div class="adm-page-head">
  <div><h1>Mensajes de contacto</h1><p><?php echo count($mensajes); ?> mensaje<?php echo count($mensajes)!==1?'s':''; ?></p></div>
</div>

<div class="adm-table-wrap">
<?php if (!empty($mensajes)): ?>
<table class="adm-table">
  <thead><tr><th>Estado</th><th>Nombre</th><th>Correo</th><th>Celular</th><th>Tema</th><th>Fecha</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($mensajes as $m): ?>
  <tr style="<?php echo !$m['leido'] ? 'background:var(--paper)' : ''; ?>">
    <td>
      <?php if (!$m['leido']): ?>
        <span class="adm-badge adm-badge--pub">Nuevo</span>
      <?php else: ?>
        <span class="adm-badge adm-badge--bor">Leído</span>
      <?php endif; ?>
    </td>
    <td><strong><?php echo h($m['nombre']); ?></strong></td>
    <td><a href="mailto:<?php echo h($m['email']); ?>" style="color:var(--gold)"><?php echo h($m['email']); ?></a></td>
    <td><?php echo h($m['celular']); ?></td>
    <td style="max-width:220px;white-space:normal;font-size:.82rem;color:var(--ink-soft)"><?php echo h(mb_strimwidth($m['asesoria'],0,120,'…')); ?></td>
    <td style="white-space:nowrap;font-size:.8rem;color:var(--ink-soft)"><?php echo date('d/m/Y H:i', strtotime($m['created_at'])); ?></td>
    <td>
      <div class="adm-table__actions">
        <a class="adm-btn adm-btn--ghost adm-btn--sm"
           href="https://api.whatsapp.com/send?phone=<?php echo preg_replace('/\D/','',$m['celular']); ?>&text=Hola+<?php echo urlencode($m['nombre']); ?>,"
           target="_blank">WhatsApp</a>
        <?php if (!$m['leido']): ?>
        <form method="POST" style="display:inline">
          <?php echo csrf_campo(); ?>
          <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
          <button class="adm-btn adm-btn--ghost adm-btn--sm" type="submit">Marcar leído</button>
        </form>
        <?php endif; ?>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<div class="adm-empty">
  <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
  <p>No hay mensajes de contacto todavía.</p>
</div>
<?php endif; ?>
</div>

<?php require __DIR__ . '/../admin/includes/admin-footer.php'; ?>
