<?php
/**
 * header.php — Navegación principal del sitio.
 * Incluir DESPUÉS de head.php y ANTES del contenido.
 *
 * La variable $current_page puede definirse en cada página
 * para resaltar el ítem activo en el menú.
 * Valores posibles: 'home' | 'blog' | 'servicios' | 'contacto'
 */
$current_page = $current_page ?? 'home';

function nav_active(string $page, string $current): string {
    return $page === $current ? ' class="active"' : '';
}
?>
<body>

<header class="site-header">
  <div class="wrap nav">

    <!-- Marca / Logo -->
    <a class="nav__brand" href="/">
      <picture>
        <source srcset="/assets/img/logo_fredyarenasabogado_412x366.webp" type="image/webp">
        <img src="/assets/img/logo_fredyarenasabogado_412x366.png"
             width="34" height="34" alt="Logo Fredy Arenas">
      </picture>
      Fredy Arenas <span>·</span> Abogado
    </a>

    <!-- Menú desktop -->
    <nav aria-label="Menú principal">
      <ul class="nav__menu">
        <li><a href="/#perfil"<?php echo nav_active('perfil',    $current_page); ?>>Perfil</a></li>
        <li><a href="/#servicios"<?php echo nav_active('servicios', $current_page); ?>>Servicios</a></li>
        <li><a href="/blog.php"<?php echo nav_active('blog',      $current_page); ?>>Blog</a></li>
        <li><a href="/videos.php"<?php echo nav_active('videos',    $current_page); ?>>Videos</a></li>
        <li><a href="/#contacto"<?php echo nav_active('contacto', $current_page); ?>>Contacto</a></li>
      </ul>
    </nav>

    <a class="nav-cta" href="/#contacto">Agenda una cita</a>

    <!-- Hamburguesa (móvil) -->
    <button class="nav__burger" aria-label="Abrir menú" aria-expanded="false">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
           stroke-width="2" stroke-linecap="round">
        <path d="M4 7h16M4 12h16M4 17h16"/>
      </svg>
    </button>
  </div>

  <!-- Menú móvil (oculto por defecto) -->
  <nav class="nav__mobile" aria-label="Menú móvil">
    <a href="/#perfil">Perfil</a>
    <a href="/#servicios">Servicios</a>
    <a href="/blog.php">Blog</a>
    <a href="/videos.php">Videos</a>
    <a href="/#contacto">Contacto</a>
    <a href="/#contacto" style="color:var(--gold)">Agenda una cita →</a>
  </nav>
</header>