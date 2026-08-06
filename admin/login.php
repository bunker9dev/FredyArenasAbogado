<?php
require_once __DIR__ . '/includes/auth.php';

if (esta_autenticado()) { header('Location: /admin/'); exit; }

$CONFIG_ADMIN = require __DIR__ . '/../includes/config/admin.php';
$error = ''; $bloqueo = segundos_bloqueo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($bloqueo > 0) {
        $error = 'Demasiados intentos. Espera ' . ceil($bloqueo/60) . ' min.';
    } elseif (!csrf_valido()) {
        $error = 'Sesión expirada. Recarga la página.';
    } else {
        $usuario = trim($_POST['usuario'] ?? '');
        $clave   = $_POST['clave'] ?? '';
        if (hash_equals($CONFIG_ADMIN['usuario'], $usuario) && password_verify($clave, $CONFIG_ADMIN['hash'])) {
            limpiar_fallos();
            iniciar_sesion($CONFIG_ADMIN['usuario']);
            $destino = $_SESSION['destino'] ?? '/admin/';
            unset($_SESSION['destino']);
            header('Location: ' . $destino); exit;
        }
        registrar_fallo();
        $bloqueo = segundos_bloqueo();
        $error = $bloqueo > 0 ? 'Demasiados intentos. Espera ' . ceil($bloqueo/60) . ' min.' : 'Usuario o contraseña incorrectos.';
    }
}
$expirado = isset($_GET['expirado']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Acceso · Panel Fredy Arenas</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css">
<style>
  body{display:grid;place-items:center;min-height:100vh;background:var(--paper)}
  .login-card{width:min(380px,90vw);background:#fff;border:1px solid var(--line);border-radius:6px;padding:2.4rem 2.2rem;box-shadow:0 18px 50px rgba(23,26,32,.07)}
  .login-brand{display:flex;align-items:center;justify-content:center;gap:.7rem;font-family:var(--font-serif);font-size:1.1rem;margin-bottom:1.6rem}
  .login-brand img{width:40px;height:40px;object-fit:contain;border-radius:50%}
  .login-card h1{font-size:1.4rem;text-align:center;margin-bottom:.3rem}
  .login-card .sub{text-align:center;color:var(--ink-soft);font-size:.84rem;margin-bottom:1.6rem}
  .login-card .adm-field{margin-bottom:1rem}
  .login-card .adm-btn{width:100%;justify-content:center;margin-top:.5rem;padding:.75rem}
  .login-foot{text-align:center;margin-top:1.4rem;font-size:.75rem;color:var(--ink-soft)}
  .login-foot a{color:var(--gold)}
</style>
</head>
<body>
<form class="login-card" method="POST" action="/admin/login.php" autocomplete="off">
  <div class="login-brand">
    <img src="/assets/img/logo_fredyarenasabogado_412x366.webp" onerror="this.style.display='none'" alt="">
    Fredy Arenas
  </div>
  <h1>Panel de administración</h1>
  <p class="sub">Acceso exclusivo del administrador</p>

  <?php if ($expirado): ?><div class="adm-alert adm-alert--warn">Tu sesión expiró. Inicia de nuevo.</div><?php endif; ?>
  <?php if ($error): ?><div class="adm-alert adm-alert--error"><?php echo htmlspecialchars($error,ENT_QUOTES); ?></div><?php endif; ?>

  <?php echo csrf_campo(); ?>

  <div class="adm-field">
    <label for="usuario">Usuario</label>
    <input id="usuario" name="usuario" type="text" autocomplete="username" autofocus required>
  </div>
  <div class="adm-field">
    <label for="clave">Contraseña</label>
    <input id="clave" name="clave" type="password" autocomplete="current-password" required>
  </div>
  <button class="adm-btn adm-btn--primary" type="submit" <?php echo $bloqueo>0?'disabled':''; ?>>
    Ingresar
  </button>
  <p class="login-foot">← <a href="/">Volver al sitio</a></p>
</form>
</body>
</html>
