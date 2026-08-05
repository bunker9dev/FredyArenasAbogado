<?php
/**
 * helpers.php — Funciones de utilidad compartidas en todo el sitio.
 */


/* ── Formato de fecha ───────────────────────────────────── */

/**
 * Convierte 'YYYY-MM-DD' → '28 jul 2026'.
 */
function fecha_es(string $fecha): string
{
    static $meses = [
        1=>'ene',2=>'feb',3=>'mar',4=>'abr',5=>'may',6=>'jun',
        7=>'jul',8=>'ago',9=>'sep',10=>'oct',11=>'nov',12=>'dic',
    ];
    $t = strtotime($fecha);
    if (!$t) return htmlspecialchars($fecha, ENT_QUOTES);
    return date('j', $t) . ' ' . $meses[(int)date('n', $t)] . ' ' . date('Y', $t);
}


/* ── Seguridad / texto ──────────────────────────────────── */

/** Escapa para HTML. Atajo de htmlspecialchars con ENT_QUOTES. */
function h(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

/** Quita saltos de línea de un valor (anti-inyección de cabeceras de correo). */
function limpiar_cabecera(string $v): string
{
    return trim(str_replace(["\r", "\n", "%0a", "%0d"], '', $v));
}

/** Genera un slug URL-amigable desde un título. */
function slugify(string $texto): string
{
    $mapa = [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'ñ'=>'n','ü'=>'u','Á'=>'a','É'=>'e','Í'=>'i',
        'Ó'=>'o','Ú'=>'u','Ñ'=>'n',
    ];
    $texto = strtr(mb_strtolower(trim($texto)), $mapa);
    $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
    $texto = preg_replace('/[\s-]+/', '-', $texto);
    return trim($texto, '-');
}


/* ── Imágenes ───────────────────────────────────────────── */

/** Devuelve la ruta a la imagen de un artículo (o la imagen por defecto). */
function imagen_blog(string $nombre = '', string $default = '/assets/img/og-default.webp'): string
{
    if ($nombre === '') return $default;
    $ruta = '/imagenes/' . rawurlencode($nombre);
    return $ruta;
}


/* ── Vistas ──────────────────────────────────────────────── */

/**
 * Registra una vista única por artículo (una por IP cada 4 horas).
 * Si ya existe el registro, simplemente no hace nada.
 */
function registrar_vista(int $articulo_id): void
{
    $db = db_connect();
    if (!$db) return;

    $ip_hash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
    $ventana = date('Y-m-d H:i:s', strtotime('-4 hours'));

    // ¿Ya visitó en las últimas 4 horas?
    $check = $db->prepare(
        "SELECT id FROM vistas_log
         WHERE articulo_id = ? AND ip_hash = ? AND created_at > ?"
    );
    $check->bind_param('iss', $articulo_id, $ip_hash, $ventana);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        // Registrar visita
        $ins = $db->prepare("INSERT INTO vistas_log (articulo_id, ip_hash) VALUES (?, ?)");
        $ins->bind_param('is', $articulo_id, $ip_hash);
        $ins->execute();

        // Incrementar contador en el artículo
        $upd = $db->prepare("UPDATE blog SET vistas = vistas + 1 WHERE id = ?");
        $upd->bind_param('i', $articulo_id);
        $upd->execute();
    }
}


/* ── Feedback ────────────────────────────────────────────── */

/**
 * Guarda el voto "útil" (si/no) del visitante.
 * Devuelve true si se guardó, false si ya había votado.
 */
function guardar_feedback(int $articulo_id, string $valor): bool
{
    if (!in_array($valor, ['si', 'no'], true)) return false;

    $db = db_connect();
    if (!$db) return false;

    $ip_hash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

    $stmt = $db->prepare(
        "INSERT IGNORE INTO feedback (articulo_id, valor, ip_hash) VALUES (?, ?, ?)"
    );
    $stmt->bind_param('iss', $articulo_id, $valor, $ip_hash);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}


/* ── WhatsApp ────────────────────────────────────────────── */

/** URL de WhatsApp con mensaje precompletado. */
function url_whatsapp(string $mensaje = 'Hola Fredy, me interesa una asesoría, mi nombre es: '): string
{
    return 'https://api.whatsapp.com/send?phone=573102225617&text=' . rawurlencode($mensaje);
}
