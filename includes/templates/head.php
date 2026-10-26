<?php
/**
 * head.php — <head> compartido
 *
 * Las páginas que lo incluyen pueden definir antes de incluirlo:
 *   $page_title       — título de la página (opcional)
 *   $page_desc        — meta description (opcional)
 *   $page_canonical   — URL canónica (opcional)
 *   $page_og_image    — imagen Open Graph (opcional)
 */

$default_title    = 'Fredy Arenas · Abogado en Neiva — Derecho administrativo, disciplinario y penal militar';
$default_desc     = 'Fredy Antonio Arenas Gutiérrez, abogado en Neiva (Huila). Especialista en derecho administrativo, disciplinario, constitucional y justicia penal militar.';
$default_image    = 'https://fredyarenasabogado.com/assets/img/perfil-fredyarenas5.webp';
$default_canonical= 'https://fredyarenasabogado.com/';

// h() puede no estar cargada aún si helpers.php se carga después;
// definimos versión local segura para este archivo.
if (!function_exists('h')) {
    function h(?string $v): string {
        return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
    }
}

$title = isset($page_title)     ? h($page_title)     : $default_title;
$desc  = isset($page_desc)      ? h($page_desc)      : $default_desc;
$image = isset($page_og_image)  ? h($page_og_image)  : $default_image;
$canon = isset($page_canonical) ? h($page_canonical) : $default_canonical;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-VVWYYQJMMR"></script>
<script>
  window.dataLayer=window.dataLayer||[];
  function gtag(){dataLayer.push(arguments);}
  gtag('js',new Date());gtag('config','G-VVWYYQJMMR');
</script>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description"  content="<?php echo $desc; ?>">
<meta name="author"       content="Fredy Antonio Arenas Gutiérrez">
<link rel="canonical"     href="<?php echo $canon; ?>">

<!-- Open Graph -->
<meta property="og:type"        content="website">
<meta property="og:title"       content="<?php echo $title; ?>">
<meta property="og:description" content="<?php echo $desc; ?>">
<meta property="og:url"         content="<?php echo $canon; ?>">
<meta property="og:image"       content="<?php echo $image; ?>">

<title><?php echo $title; ?></title>

<link rel="shortcut icon" type="image/png"
      href="/assets/img/logo_fredyarenasabogado_412x377fondoblanco.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<?php if (!empty($preload_imagen)): ?>
<!-- Imagen principal: el navegador la pide de primera (mejora el LCP) -->
<link rel="preload" as="image" href="<?php echo h($preload_imagen); ?>" fetchpriority="high">
<?php endif; ?>

<!-- Fuentes de Google SIN bloquear: el texto se muestra de inmediato con la
     fuente del sistema y cambia a Fraunces/Inter cuando terminan de cargar -->
<?php $fuentes = 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500&family=Inter:wght@400;500;600&display=swap'; ?>
<link rel="preload" as="style" href="<?php echo $fuentes; ?>" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="<?php echo $fuentes; ?>"></noscript>

<!-- Hojas de estilo enlazadas directamente: se descargan EN PARALELO.
     (Antes app.css las encadenaba con @import, una detrás de otra.)
     app.css se conserva tal cual por si el panel lo usa. -->
<?php foreach (['variables','utilidades','botones','header','hero','servicios','blog','contacto','footer','articulo','responsive'] as $css): ?>
<link rel="stylesheet" href="/assets/css/<?php echo $css; ?>.css">
<?php endforeach; ?>

<!-- JS módulos (defer = no bloquean el render) -->
<script defer src="/assets/js/app.js"></script>
<script defer src="/assets/js/menu.js"></script>
<script defer src="/assets/js/scroll.js"></script>
<script defer src="/assets/js/videos.js"></script>
<?php if (!empty($cargar_contacto_js)): ?>
<script defer src="/assets/js/contacto.js"></script>
<?php endif; ?>
<?php if (!empty($cargar_articulo_js)): ?>
<script defer src="/assets/js/articulo.js"></script>
<?php endif; ?>

</head>