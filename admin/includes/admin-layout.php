<?php
/**
 * admin-layout.php — Topbar + Sidebar compartidos.
 * Incluir después de admin-head.php, antes del contenido.
 *
 * Variable opcional:
 *   $adm_nav_active  — 'dashboard'|'articulos'|'videos'|'contactos'
 */
$adm_nav_active = $adm_nav_active ?? 'dashboard';
function adm_active(string $key, string $current): string {
    return $key === $current ? ' class="active"' : '';
}
?>
<body>
<div class="adm-layout">

<!-- ── Topbar ──────────────────────────────────────────── -->
<header class="adm-topbar">
  <a class="adm-topbar__brand" href="/admin/">
    <img src="/assets/img/logo_fredyarenasabogado_412x366.webp"
         onerror="this.style.display='none'" alt="Logo">
    Panel de administración
  </a>
  <div class="adm-topbar__right">
    <span class="adm-topbar__user">
      <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'admin', ENT_QUOTES); ?>
    </span>
    <a class="adm-logout" href="/admin/logout.php">Cerrar sesión</a>
  </div>
</header>

<!-- ── Sidebar ─────────────────────────────────────────── -->
<aside class="adm-sidebar">
  <nav class="adm-nav">
    <div class="adm-nav__section">General</div>
    <a href="/admin/"<?php echo adm_active('dashboard', $adm_nav_active); ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
      </svg>
      Dashboard
    </a>

    <div class="adm-nav__section">Contenido</div>
    <a href="/admin/blog/"<?php echo adm_active('articulos', $adm_nav_active); ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
        <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
      </svg>
      Artículos
    </a>
    <a href="/admin/blog/crear.php"<?php echo adm_active('art-crear', $adm_nav_active); ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
        <path d="M12 5v14M5 12h14"/>
      </svg>
      Nuevo artículo
    </a>

    <div class="adm-nav__section">Videos</div>
    <a href="/admin/videos/"<?php echo adm_active('videos', $adm_nav_active); ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
        <rect x="2" y="4" width="20" height="16" rx="2"/>
        <path d="M10 9l5 3-5 3V9z"/>
      </svg>
      Videos
    </a>
    <a href="/admin/videos/crear.php"<?php echo adm_active('vid-crear', $adm_nav_active); ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
        <path d="M12 5v14M5 12h14"/>
      </svg>
      Nuevo video
    </a>

    <div class="adm-nav__section">Mensajes</div>
    <a href="/admin/contactos.php"<?php echo adm_active('contactos', $adm_nav_active); ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
        <path d="M22 6l-10 7L2 6"/>
      </svg>
      Mensajes de contacto
    </a>

    <div class="adm-nav__section">Sitio</div>
    <a href="/" target="_blank">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round">
        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
        <path d="M15 3h6v6M10 14L21 3"/>
      </svg>
      Ver sitio web
    </a>
  </nav>
</aside>

<!-- ── Contenido principal ─────────────────────────────── -->
<main class="adm-main">
