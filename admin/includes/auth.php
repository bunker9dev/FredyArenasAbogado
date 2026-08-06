<?php

/**
 * auth.php — Núcleo de autenticación del panel de administración.
 *
 * Incluir SIEMPRE al inicio de cualquier archivo bajo /admin, ANTES de imprimir
 * nada al navegador:
 *
 *     require_once __DIR__ . '/../includes/auth.php';   // desde /admin
 *     requiere_auth();                                   // protege la página
 *
 * Usa __DIR__ para que las rutas funcionen sin importar desde qué carpeta se llame.
 */

// ─────────────────────────────────────────────────────────────
//  Configuración segura de la sesión (antes de session_start)
// ─────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {

    $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443);

    session_name('FA_ADMIN');
    session_set_cookie_params([
        'lifetime' => 0,            // la cookie muere al cerrar el navegador
        'path'     => '/',
        'httponly' => true,         // JavaScript no puede leer la cookie
        'secure'   => $esHttps,     // solo por HTTPS cuando esté disponible
        'samesite' => 'Strict',     // no se envía en peticiones de otros sitios
    ]);

    session_start();
}

$CONFIG_ADMIN = require __DIR__ . '/config/admin.php';


// ─────────────────────────────────────────────────────────────
//  Estado de sesión
// ─────────────────────────────────────────────────────────────

/** ¿Hay un administrador con sesión iniciada? */
function esta_autenticado(): bool
{
    return !empty($_SESSION['auth'])
        && $_SESSION['auth'] === true
        && !empty($_SESSION['usuario']);
}

/**
 * Protege una página: si no hay sesión válida, redirige al login.
 * Llamar al inicio de cada archivo del panel.
 */
function requiere_auth(): void
{
    if (!esta_autenticado()) {
        // Recuerda a dónde quería ir para volver tras el login
        $_SESSION['destino'] = $_SERVER['REQUEST_URI'] ?? '/admin/';
        header('Location: /admin/login.php');
        exit;
    }

    // Expira la sesión tras 2 horas de inactividad
    $limite = 2 * 60 * 60;
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $limite) {
        cerrar_sesion();
        header('Location: /admin/login.php?expirado=1');
        exit;
    }
    $_SESSION['ultimo_acceso'] = time();
}

/** Marca la sesión como autenticada (tras verificar credenciales). */
function iniciar_sesion(string $usuario): void
{
    // Regenera el ID para evitar fijación de sesión
    session_regenerate_id(true);
    $_SESSION['auth']          = true;
    $_SESSION['usuario']       = $usuario;
    $_SESSION['ultimo_acceso'] = time();
}

/** Destruye por completo la sesión. */
function cerrar_sesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}


// ─────────────────────────────────────────────────────────────
//  Protección CSRF
// ─────────────────────────────────────────────────────────────

/** Devuelve (creando si hace falta) el token CSRF de la sesión. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo oculto listo para pegar dentro de un <form>. */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/** Valida el token recibido por POST (comparación en tiempo constante). */
function csrf_valido(): bool
{
    return !empty($_POST['csrf'])
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}


// ─────────────────────────────────────────────────────────────
//  Bloqueo por intentos fallidos (anti fuerza bruta), por IP
// ─────────────────────────────────────────────────────────────

function _archivo_intentos(): string
{
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $hash = hash('sha256', $ip);
    return sys_get_temp_dir() . '/fa_login_' . $hash . '.json';
}

/** ¿La IP actual está bloqueada? Devuelve segundos restantes (0 si no). */
function segundos_bloqueo(): int
{
    global $CONFIG_ADMIN;
    $f = _archivo_intentos();
    if (!is_file($f)) return 0;

    $d = json_decode(@file_get_contents($f), true) ?: [];
    $intentos = $d['intentos'] ?? 0;
    $ultimo   = $d['ultimo']   ?? 0;

    if ($intentos >= ($CONFIG_ADMIN['max_intentos'] ?? 5)) {
        $restante = ($ultimo + ($CONFIG_ADMIN['bloqueo_segundos'] ?? 900)) - time();
        return $restante > 0 ? $restante : 0;
    }
    return 0;
}

/** Registra un intento fallido. */
function registrar_fallo(): void
{
    $f = _archivo_intentos();
    $d = is_file($f) ? (json_decode(@file_get_contents($f), true) ?: []) : [];

    // Si el bloqueo ya expiró, reinicia el contador
    if (segundos_bloqueo() === 0 && ($d['intentos'] ?? 0) >= 1) {
        global $CONFIG_ADMIN;
        $ultimo = $d['ultimo'] ?? 0;
        if ((time() - $ultimo) > ($CONFIG_ADMIN['bloqueo_segundos'] ?? 900)) {
            $d = [];
        }
    }

    $d['intentos'] = ($d['intentos'] ?? 0) + 1;
    $d['ultimo']   = time();
    @file_put_contents($f, json_encode($d), LOCK_EX);
}

/** Limpia el registro de intentos (tras un login correcto). */
function limpiar_fallos(): void
{
    $f = _archivo_intentos();
    if (is_file($f)) @unlink($f);
}
