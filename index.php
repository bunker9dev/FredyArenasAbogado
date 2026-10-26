<?php
/**
 * index.php — Página principal
 * Fredy Antonio Arenas Gutiérrez · Abogado — Neiva (Huila)
 *
 * Solo orquesta: carga dependencias, procesa el formulario y
 * delega el HTML a los includes de cada sección.
 */

/* ── 1) Dependencias ──────────────────────────────────── */
require_once __DIR__ . '/includes/config/database.php';
require_once __DIR__ . '/includes/functions/helpers.php';

/* ── 2) Formulario de contacto (procesamiento seguro) ── */
$errores  = [];
$enviado  = isset($_GET['ok']);
$nombre = $email = $celular = $asesoria = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Honeypot: si el campo oculto tiene valor, es un bot.
    // No avisamos nada raro: simplemente lo tratamos como "enviado"
    // para no darle pistas al bot de que fue detectado.
    if (!empty($_POST['website'])) {
        header('Location: /index.php?ok=1#contacto');
        exit;
    }

    $nombre   = limpiar_cabecera($_POST['nombre']   ?? '');
    $email    = limpiar_cabecera($_POST['email']    ?? '');
    $celular  = limpiar_cabecera($_POST['celular']  ?? '');
    $asesoria = trim($_POST['asesoria']             ?? '');

    // Validaciones normales
    if (!$nombre)                                    $errores[] = 'El nombre es obligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
    if (!$celular)                                   $errores[] = 'El celular es obligatorio.';
    if (!$asesoria)                                  $errores[] = 'Cuéntanos el tema de la asesoría.';

    // Verificación humana (pregunta de suma + tiempo mínimo)
    $cap_a    = (int)($_POST['cap_a']    ?? 0);
    $cap_b    = (int)($_POST['cap_b']    ?? 0);
    $cap_ts   = (int)($_POST['cap_ts']   ?? 0);
    $cap_firma = $_POST['cap_firma']     ?? '';
    $cap_resp  = $_POST['cap_resp']      ?? '';

    $captcha = validar_captcha($cap_a, $cap_b, $cap_ts, $cap_firma, $cap_resp);
    if (!$captcha['ok']) $errores[] = $captcha['motivo'];

    // Límite de envíos por IP (evita ráfagas de spam)
    $ip_hash = ip_hash_visitante();
    if (empty($errores) && limite_contacto_superado($ip_hash)) {
        $errores[] = 'Has enviado varios mensajes seguidos. Espera un momento antes de intentar de nuevo.';
    }

    if (empty($errores)) {

        // Guardar en la BD (historial de contactos en el panel)
        $db = db_connect();
        if ($db) {
            $stmt = $db->prepare(
                "INSERT INTO contacto (nombre, email, celular, ip_hash, asesoria) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sssss', $nombre, $email, $celular, $ip_hash, $asesoria);
            $stmt->execute();
        }

        // Enviar correo con cabeceras seguras (From fijo del dominio).
        // Se envían DOS correos independientes (en vez de usar Cc:)
        // porque el hosting descarta el encabezado Cc en los envíos
        // a dominios externos (Gmail/Hotmail) — así cada uno llega
        // como un envío directo normal.
        $asunto  = 'Nueva solicitud de asesoría';
        $cuerpo  = "<h2>Solicitud de asesoría</h2>"
                 . "<p><strong>Nombre:</strong> "  . htmlspecialchars($nombre)   . "</p>"
                 . "<p><strong>Correo:</strong> "  . htmlspecialchars($email)    . "</p>"
                 . "<p><strong>Celular:</strong> " . htmlspecialchars($celular)  . "</p>"
                 . "<p><strong>Tema:</strong><br>" . nl2br(htmlspecialchars($asesoria)) . "</p>";

        $headers = "MIME-Version: 1.0\r\n"
                 . "Content-Type: text/html; charset=UTF-8\r\n"
                 . "From: Sitio Web <no-reply@fredyarenasabogado.com>\r\n"
                 . "Reply-To: " . $email . "\r\n";

        @mail('asesorias@fredyarenasabogado.com', $asunto, $cuerpo, $headers);
        @mail('fredy.arenas1972@hotmail.com',              $asunto, $cuerpo, $headers);

        header('Location: /index.php?ok=1#contacto');
        exit;
    }
}

// Captcha para el formulario que se va a mostrar (nuevo en cada carga,
// incluso si hubo errores — así no se reutiliza uno ya usado).
$captcha_form = generar_captcha();

/* ── 3) Variables para el <head> ──────────────────────── */
$page_title     = 'Fredy Arenas · Abogado en Neiva — Derecho administrativo, disciplinario y penal militar';
$page_desc      = 'Fredy Antonio Arenas Gutiérrez, abogado en Neiva (Huila). Especialista en derecho administrativo, disciplinario, constitucional y justicia penal militar.';
$page_canonical = 'https://fredyarenasabogado.com/';
$cargar_contacto_js = true;   // activa contacto.js en el head
$current_page   = 'home';
$preload_imagen = '/assets/img/perfil-fredyarenas5.webp';   // foto del hero (LCP)

/* ── 4) Salida HTML ───────────────────────────────────── */
require __DIR__ . '/includes/templates/head.php';
require __DIR__ . '/includes/templates/header.php';

?>

<?php
// Datos estructurados para SEO local
echo '<script type="application/ld+json">' . json_encode([
  '@context'   => 'https://schema.org',
  '@type'      => 'Attorney',
  'name'       => 'Fredy Antonio Arenas Gutiérrez',
  'image'      => 'https://fredyarenasabogado.com/assets/img/perfil-fredyarenas5.webp',
  'url'        => 'https://fredyarenasabogado.com/',
  'telephone'  => '+573102225617',
  'email'      => 'asesorias@fredyarenasabogado.com',
  'address'    => [
    '@type'          => 'PostalAddress',
    'streetAddress'  => 'Calle 21 No 28-35, Barrio El Jardín',
    'addressLocality'=> 'Neiva',
    'addressRegion'  => 'Huila',
    'addressCountry' => 'CO',
  ],
  'areaServed'  => 'Colombia',
  'knowsAbout'  => [
    'Derecho Administrativo','Derecho Disciplinario',
    'Justicia Penal Militar','Derecho Laboral y Pensional',
    'Responsabilidad Médica','Insolvencia de Persona Natural',
  ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
?>

<main>
  <?php require __DIR__ . '/includes/home/hero.php';      ?>
  <?php require __DIR__ . '/includes/home/perfil.php';    ?>
  <?php require __DIR__ . '/includes/home/servicios.php'; ?>
  <?php require __DIR__ . '/includes/home/blog.php';      ?>
  <?php require __DIR__ . '/includes/home/videos.php';    ?>
  <?php require __DIR__ . '/includes/home/contacto.php';  ?>
</main>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>