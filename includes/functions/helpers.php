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
function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
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

/**
 * Procesa UNA imagen subida y genera automáticamente dos versiones WebP:
 *   {base}-1200.webp  (escritorio, 1200×800, recorte 3:2)
 *   {base}-700.webp   (móvil,      700×467,  recorte 3:2)
 *
 * El editor solo sube un archivo; esta función crea las dos versiones
 * en el servidor y devuelve el nombre "base" (sin sufijo ni extensión)
 * que se guarda en la columna `imagen` de la BD.
 *
 * Devuelve '' si algo falla (el llamador debe seguir usando la imagen
 * anterior o el placeholder por defecto).
 */
function procesar_imagen_blog(string $tmp_path, string $mime_type, string $dir_destino): string
{
    if (!function_exists('imagecreatetruecolor')) return '';

    switch ($mime_type) {
        case 'image/jpeg': $origen = @imagecreatefromjpeg($tmp_path); break;
        case 'image/png':  $origen = @imagecreatefrompng($tmp_path);  break;
        case 'image/webp': $origen = @imagecreatefromwebp($tmp_path); break;
        default: return '';
    }
    if (!$origen) return '';

    $ancho_orig = imagesx($origen);
    $alto_orig  = imagesy($origen);

    /* Recorte centrado a proporción 3:2 (la que usa .card__img en blog.css) */
    $ratio_obj = 3 / 2;
    $ratio_act = $ancho_orig / $alto_orig;

    if ($ratio_act > $ratio_obj) {
        $alto_recorte  = $alto_orig;
        $ancho_recorte = (int) round($alto_orig * $ratio_obj);
        $x_origen      = (int) round(($ancho_orig - $ancho_recorte) / 2);
        $y_origen      = 0;
    } else {
        $ancho_recorte = $ancho_orig;
        $alto_recorte  = (int) round($ancho_orig / $ratio_obj);
        $x_origen      = 0;
        $y_origen      = (int) round(($alto_orig - $alto_recorte) / 2);
    }

    if (!is_dir($dir_destino)) mkdir($dir_destino, 0755, true);
    $base = bin2hex(random_bytes(12));

    $tamanos = ['1200' => 1200, '700' => 700];
    foreach ($tamanos as $sufijo => $ancho_final) {
        $alto_final = (int) round($ancho_final / $ratio_obj);
        $lienzo = imagecreatetruecolor($ancho_final, $alto_final);
        imagecopyresampled(
            $lienzo, $origen,
            0, 0, $x_origen, $y_origen,
            $ancho_final, $alto_final,
            $ancho_recorte, $alto_recorte
        );
        imagewebp($lienzo, "$dir_destino/{$base}-{$sufijo}.webp", 82);
        imagedestroy($lienzo);
    }

    imagedestroy($origen);
    return $base;
}

/** Borra ambos tamaños (1200/700) de una imagen de artículo. */
function borrar_imagen_blog(string $base, string $dir): void
{
    if ($base === '') return;
    foreach (['1200', '700'] as $sufijo) {
        $ruta = "$dir/{$base}-{$sufijo}.webp";
        if (is_file($ruta)) @unlink($ruta);
    }
}

/**
 * Devuelve el HTML <img> con srcset para servir la versión correcta
 * (700px en móvil, 1200px en escritorio) desde un solo nombre base.
 */
function imagen_blog(string $base = '', string $alt = '', string $clase = '', bool $lazy = true): string
{
    if ($base === '') {
        return '<img src="/assets/img/og-default.webp" alt="' . h($alt) . '"'
             . ($clase ? ' class="' . h($clase) . '"' : '')
             . ($lazy ? ' loading="lazy"' : '') . '>';
    }
    $src700  = '/imagenes/' . rawurlencode($base) . '-700.webp';
    $src1200 = '/imagenes/' . rawurlencode($base) . '-1200.webp';

    return '<img src="' . h($src1200) . '"'
         . ' srcset="' . h($src700) . ' 700w, ' . h($src1200) . ' 1200w"'
         . ' sizes="(max-width: 640px) 100vw, 33vw"'
         . ' alt="' . h($alt) . '"'
         . ($clase ? ' class="' . h($clase) . '"' : '')
         . ($lazy ? ' loading="lazy"' : '') . '>';
}

/** Devuelve solo la URL de la versión grande (1200px) — para og:image, etc. */
function imagen_blog_url(string $base = '', string $default = '/assets/img/og-default.webp'): string
{
    if ($base === '') return $default;
    return '/imagenes/' . rawurlencode($base) . '-1200.webp';
}


/* ── Videos ─────────────────────────────────────────────── */

/**
 * Consulta la API pública de oEmbed de TikTok para obtener la miniatura
 * real del video (el fotograma con el que arranca). Se usa al crear o
 * editar un video en el panel, así la tarjeta pública muestra el inicio
 * real del video en vez de un logo genérico.
 *
 * Devuelve la URL de la miniatura, o '' si no se pudo obtener
 * (la tarjeta simplemente usa el logo del despacho como respaldo).
 */
function obtener_miniatura_tiktok(string $url_original): string
{
    if ($url_original === '' || !function_exists('curl_init')) return '';

    $endpoint = 'https://www.tiktok.com/oembed?url=' . urlencode($url_original);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; FredyArenasBot/1.0)',
    ]);
    $respuesta = curl_exec($ch);
    $codigo    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($codigo !== 200 || !$respuesta) return '';

    $datos = json_decode($respuesta, true);
    return $datos['thumbnail_url'] ?? '';
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


/**
 * Registra una vista real de un video (cuando el usuario le da clic para
 * reproducirlo), una por IP cada 4 horas — mismo criterio que los artículos.
 */
function registrar_vista_video(int $video_id): void
{
    $db = db_connect();
    if (!$db) return;

    $ip_hash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
    $ventana = date('Y-m-d H:i:s', strtotime('-4 hours'));

    $check = $db->prepare(
        "SELECT id FROM vistas_log_videos
         WHERE video_id = ? AND ip_hash = ? AND created_at > ?"
    );
    $check->bind_param('iss', $video_id, $ip_hash, $ventana);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $ins = $db->prepare("INSERT INTO vistas_log_videos (video_id, ip_hash) VALUES (?, ?)");
        $ins->bind_param('is', $video_id, $ip_hash);
        $ins->execute();

        $upd = $db->prepare("UPDATE videos SET vistas = vistas + 1 WHERE id = ?");
        $upd->bind_param('i', $video_id);
        $upd->execute();
    }
}


/* ── Antispam del formulario de contacto ───────────────────── */

/**
 * Genera una pregunta de suma simple ("¿Cuánto es 4 + 3?"), firmada
 * con SITE_SECRET para que no se pueda falsificar la respuesta sin
 * conocer la clave del servidor, y con marca de tiempo para detectar
 * envíos "demasiado rápidos" (típico de bots).
 */
function generar_captcha(): array
{
    $a  = random_int(1, 9);
    $b  = random_int(1, 9);
    $ts = time();
    $firma = hash_hmac('sha256', "$a|$b|$ts", SITE_SECRET);
    return compact('a', 'b', 'ts', 'firma');
}

/**
 * Valida la respuesta del captcha. Devuelve ['ok'=>bool,'motivo'=>string].
 * Rechaza: firma alterada, respuesta incorrecta, envío en menos de 3
 * segundos (imposible para un humano) o formulario con más de 1 hora
 * (evita reintentos con una página vieja guardada en caché).
 */
function validar_captcha(int $a, int $b, int $ts, string $firma, $respuesta): array
{
    $esperada = hash_hmac('sha256', "$a|$b|$ts", SITE_SECRET);
    if (!hash_equals($esperada, $firma)) {
        return ['ok' => false, 'motivo' => 'Verificación inválida, recarga la página e inténtalo de nuevo.'];
    }
    $transcurrido = time() - $ts;
    if ($transcurrido < 3) {
        return ['ok' => false, 'motivo' => 'Envío demasiado rápido. Inténtalo de nuevo.'];
    }
    if ($transcurrido > 3600) {
        return ['ok' => false, 'motivo' => 'El formulario expiró, recarga la página e inténtalo de nuevo.'];
    }
    if ((int)$respuesta !== ($a + $b)) {
        return ['ok' => false, 'motivo' => 'La respuesta de verificación no es correcta.'];
    }
    return ['ok' => true, 'motivo' => ''];
}

/** Hash de la IP del visitante (no guardamos la IP real, solo su huella). */
function ip_hash_visitante(): string
{
    return hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
}

/**
 * true si esta IP ya envió demasiados mensajes de contacto en la
 * última hora (por defecto, más de 3).
 */
function limite_contacto_superado(string $ip_hash, int $max = 3, int $ventana_seg = 3600): bool
{
    $db = db_connect();
    if (!$db) return false;

    $desde = date('Y-m-d H:i:s', time() - $ventana_seg);
    $st = $db->prepare("SELECT COUNT(*) FROM contacto WHERE ip_hash = ? AND created_at > ?");
    $st->bind_param('ss', $ip_hash, $desde);
    $st->execute();
    $st->bind_result($n);
    $st->fetch();
    $st->close();

    return $n >= $max;
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


/* ── Recortar texto de forma segura ──────────────────────── */
/**
 * Recorta un texto a N caracteres añadiendo '…'.
 * Alternativa segura a mb_strimwidth por si mbstring no está.
 */
function recortar(?string $texto, int $max = 120, string $sufijo = '…'): string
{
    $texto = trim(strip_tags($texto ?? ''));
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($texto, 0, $max, $sufijo);
    }
    return strlen($texto) > $max ? substr($texto, 0, $max - 1) . $sufijo : $texto;
}