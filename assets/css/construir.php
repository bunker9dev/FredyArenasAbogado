<?php
/**
 * construir.php — Une y minifica las hojas de estilo del sitio público.
 *
 * Uso (desde la carpeta del sitio):   php assets/css/construir.php
 *
 * Genera assets/css/sitio.min.css, que es el ÚNICO CSS que carga la web.
 * Cada vez que edites un .css parcial (header.css, hero.css...), vuelve a
 * ejecutar este comando y sube sitio.min.css al servidor.
 * El panel (admin.css) no se incluye: tiene su propio <head>.
 */

$parciales = [
    'variables', 'utilidades', 'botones', 'header', 'hero', 'servicios',
    'blog', 'contacto', 'footer', 'articulo', 'responsive',
];

$css = '';
foreach ($parciales as $nombre) {
    $ruta = __DIR__ . "/{$nombre}.css";
    if (! is_file($ruta)) {
        fwrite(STDERR, "Falta {$nombre}.css\n");
        exit(1);
    }
    $css .= file_get_contents($ruta) . "\n";
}

// Minificar: quitar comentarios y espacios innecesarios
$css = preg_replace('!/\*.*?\*/!s', '', $css);            // comentarios
$css = preg_replace('/\s+/', ' ', $css);                  // espacios y saltos de línea
$css = preg_replace('/\s*([{}:;,>])\s*/', '$1', $css);    // espacios alrededor de símbolos
$css = str_replace(';}', '}', $css);
// Restaurar el espacio que exigen "and (" en media queries y calc()
$css = preg_replace('/\band\(/', 'and (', $css);
$css = trim($css);

file_put_contents(__DIR__ . '/sitio.min.css', $css);

printf("Listo: sitio.min.css (%s KB, %d archivos unidos)\n", number_format(strlen($css) / 1024, 1), count($parciales));
