<?php
require_once __DIR__ . '/includes/auth.php';
requiere_auth();
require_once __DIR__ . '/../includes/functions/helpers.php';

$CONFIG_ADMIN = require __DIR__ . '/../includes/config/admin.php';
$CONFIG_PATH  = __DIR__ . '/../includes/config/admin.php';

$errores = [];
$exito   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valido()) {
        $errores[] = 'La sesión expiró. Recarga la página e inténtalo de nuevo.';
    } else {
        $actual  = $_POST['actual']  ?? '';
        $nueva   = $_POST['nueva']   ?? '';
        $repetir = $_POST['repetir'] ?? '';

        /* ── Validaciones ──────────────────────────────────── */
        if (!password_verify($actual, $CONFIG_ADMIN['hash'])) {
            $errores[] = 'La contraseña actual no es correcta.';
        }
        if (strlen($nueva) < 12) {
            $errores[] = 'La nueva contraseña debe tener al menos 12 caracteres.';
        }
        if (!preg_match('/[A-Z]/', $nueva) || !preg_match('/[0-9]/', $nueva)) {
            $errores[] = 'La nueva contraseña debe incluir al menos una mayúscula y un número.';
        }
        if ($nueva !== $repetir) {
            $errores[] = 'La confirmación no coincide con la nueva contraseña.';
        }
        if ($nueva !== '' && password_verify($nueva, $CONFIG_ADMIN['hash'])) {
            $errores[] = 'La nueva contraseña debe ser distinta de la actual.';
        }

        /* ── Guardar ────────────────────────────────────────── */
        if (empty($errores)) {
            if (!is_writable($CONFIG_PATH)) {
                $errores[] = 'El archivo de configuración no tiene permisos de escritura. '
                           . 'Contacta a soporte técnico o cámbialo manualmente por SFTP.';
            } else {
                $nuevo_hash = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12]);

                // Backup de seguridad antes de tocar el archivo
                @copy($CONFIG_PATH, $CONFIG_PATH . '.bak-' . date('YmdHis'));

                $contenido = file_get_contents($CONFIG_PATH);
                $reemplazos = 0;
                $contenido_nuevo = preg_replace_callback(
                    "/'hash'\s*=>\s*'[^']*'/",
                    function ($m) use ($nuevo_hash, &$reemplazos) {
                        $reemplazos++;
                        return "'hash'    => '" . addslashes($nuevo_hash) . "'";
                    },
                    $contenido,
                    1
                );

                if ($reemplazos === 1 && $contenido_nuevo !== null) {
                    file_put_contents($CONFIG_PATH, $contenido_nuevo, LOCK_EX);

                    // Invalida cualquier otra sesión activa: solo queda la actual
                    // (se logra regenerando el ID; el hash cambiado invalida logins futuros)
                    session_regenerate_id(true);

                    $exito = true;
                } else {
                    $errores[] = 'No se pudo actualizar el archivo. Verifica su formato o cámbialo por SFTP.';
                }
            }
        }
    }
}

$adm_title      = 'Cambiar contraseña';
$adm_nav_active = 'password';
require __DIR__ . '/includes/admin-head.php';
require __DIR__ . '/includes/admin-layout.php';
?>

<div class="adm-page-head">
  <div>
    <h1>Cambiar contraseña</h1>
    <p>Actualiza tu contraseña de acceso al panel.</p>
  </div>
  <a class="adm-btn adm-btn--ghost" href="/bunker/">← Volver</a>
</div>

<?php if ($exito): ?>
  <div class="adm-alert adm-alert--success">
    Contraseña actualizada correctamente. Úsala la próxima vez que inicies sesión.
  </div>
<?php endif; ?>

<?php foreach ($errores as $e): ?>
  <div class="adm-alert adm-alert--error"><?php echo h($e); ?></div>
<?php endforeach; ?>

<div class="adm-form-wrap" style="max-width:480px">
  <form method="POST" action="/bunker/password.php" autocomplete="off">
    <?php echo csrf_campo(); ?>

    <div class="adm-field" style="margin-bottom:1.2rem">
      <label for="actual">Contraseña actual</label>
      <input id="actual" name="actual" type="password"
             autocomplete="current-password" required>
    </div>

    <div class="adm-field" style="margin-bottom:1.2rem">
      <label for="nueva">Nueva contraseña</label>
      <input id="nueva" name="nueva" type="password"
             autocomplete="new-password" required
             minlength="12" oninput="validarFuerza(this.value)">
      <span class="adm-field__hint">
        Mínimo 12 caracteres, con al menos una mayúscula y un número.
      </span>
      <div id="fuerza" style="margin-top:.5rem;font-size:.78rem"></div>
    </div>

    <div class="adm-field" style="margin-bottom:1.2rem">
      <label for="repetir">Confirmar nueva contraseña</label>
      <input id="repetir" name="repetir" type="password"
             autocomplete="new-password" required minlength="12">
    </div>

    <div class="adm-form-actions" style="border-top:0;padding-top:0;margin-top:.5rem">
      <button class="adm-btn adm-btn--primary" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
          <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
        </svg>
        Actualizar contraseña
      </button>
    </div>
  </form>
</div>

<script>
function validarFuerza(v) {
  const el = document.getElementById('fuerza');
  let puntos = 0;
  if (v.length >= 12) puntos++;
  if (/[A-Z]/.test(v)) puntos++;
  if (/[0-9]/.test(v)) puntos++;
  if (/[^A-Za-z0-9]/.test(v)) puntos++;

  const niveles = [
    {c:'#b3261e', t:'Muy débil'},
    {c:'#b3261e', t:'Débil'},
    {c:'#a8843e', t:'Aceptable'},
    {c:'#1a6b3c', t:'Buena'},
    {c:'#1a6b3c', t:'Muy segura'},
  ];
  const n = niveles[puntos];
  el.innerHTML = v ? `<span style="color:${n.c};font-weight:600">${n.t}</span>` : '';
}
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
