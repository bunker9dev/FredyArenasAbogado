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

    // Honeypot: si el campo oculto tiene valor, es un bot
    if (!empty($_POST['website'])) {
        header('Location: /index.php?ok=1#contacto');
        exit;
    }

    $nombre   = limpiar_cabecera($_POST['nombre']   ?? '');
    $email    = limpiar_cabecera($_POST['email']    ?? '');
    $celular  = limpiar_cabecera($_POST['celular']  ?? '');
    $asesoria = trim($_POST['asesoria']             ?? '');

    // Validaciones
    if (!$nombre)                                    $errores[] = 'El nombre es obligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
    if (!$celular)                                   $errores[] = 'El celular es obligatorio.';
    if (!$asesoria)                                  $errores[] = 'Cuéntanos el tema de la asesoría.';

    if (empty($errores)) {

        // Guardar en la BD (historial de contactos en el panel)
        $db = db_connect();
        if ($db) {
            $stmt = $db->prepare(
                "INSERT INTO contacto (nombre, email, celular, asesoria) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param('ssss', $nombre, $email, $celular, $asesoria);
            $stmt->execute();
        }

        // Enviar correo con cabeceras seguras (From fijo del dominio)
        $dest    = 'asesorias@fredyarenasabogado.com';
        $asunto  = 'Nueva solicitud de asesoría — fredyarenasabogado.com';
        $cuerpo  = "<h2>Solicitud de asesoría</h2>"
                 . "<p><strong>Nombre:</strong> "  . htmlspecialchars($nombre)   . "</p>"
                 . "<p><strong>Correo:</strong> "  . htmlspecialchars($email)    . "</p>"
                 . "<p><strong>Celular:</strong> " . htmlspecialchars($celular)  . "</p>"
                 . "<p><strong>Tema:</strong><br>" . nl2br(htmlspecialchars($asesoria)) . "</p>";

        $headers = "MIME-Version: 1.0\r\n"
                 . "Content-Type: text/html; charset=UTF-8\r\n"
                 . "From: Sitio Web <no-reply@fredyarenasabogado.com>\r\n"
                 . "Reply-To: " . $email . "\r\n";

        @mail($dest, $asunto, $cuerpo, $headers);

        header('Location: /index.php?ok=1#contacto');
        exit;
    }
}

/* ── 3) Variables para el <head> ──────────────────────── */
$page_title     = 'Fredy Arenas · Abogado en Neiva — Derecho administrativo, disciplinario y penal militar';
$page_desc      = 'Fredy Antonio Arenas Gutiérrez, abogado en Neiva (Huila). Especialista en derecho administrativo, disciplinario, constitucional y justicia penal militar.';
$page_canonical = 'https://fredyarenasabogado.com/';
$cargar_contacto_js = true;   // activa contacto.js en el head
$current_page   = 'home';

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
  <?php require __DIR__ . '/includes/home/contacto.php';  ?>
</main>

<?php require __DIR__ . '/includes/templates/footer.php'; ?>