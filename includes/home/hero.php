<?php
/**
 * hero.php — Sección hero de la home.
 * Requiere que url_whatsapp() esté disponible (helpers.php).
 */
$wsp = url_whatsapp();
?>
<section class="hero" id="inicio">
  <div class="wrap hero__grid">

    <!-- Texto -->
    <!-- Sin la clase "rv": lo primero que se ve no debe esperar a JavaScript -->
    <div class="hero__text">
      <p class="eyebrow">Abogado · Neiva, Huila</p>
      <h1>Asesoría legal <em>especializada</em>, con criterio.</h1>
      <p class="hero__sub">
        <strong>Fredy Antonio Arenas Gutiérrez.</strong>
        Especialista en derecho administrativo, disciplinario, constitucional y
        justicia penal militar. Acompañamiento cercano y defensa técnica en
        cada etapa del proceso.
      </p>
      <div class="btns">
        <a class="btn btn--primary" href="<?php echo $wsp; ?>"
           target="_blank" rel="noopener">
          Agenda una cita
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M5 12h14M13 6l6 6-6 6"/>
          </svg>
        </a>
        <a class="btn btn--ghost" href="#servicios">Ver áreas de práctica</a>
      </div>
    </div>

    <!-- Retrato -->
    <div class="hero__portrait">
      <picture>
        <img src="/assets/img/perfil-fredyarenas5.webp"
             width="490" height="569"
             alt="Fredy Antonio Arenas Gutiérrez, abogado"
             fetchpriority="high">
      </picture>
    </div>
  </div>
</section>

<!-- Franja de credenciales -->
<div class="creds">
  <div class="wrap creds__inner">
    <span>Universidad Externado de Colombia</span>
    <span>Especialista en Derecho Disciplinario</span>
    <span>Seguridad y Defensa Nacional</span>
    <span>Ciencias Militares</span>
  </div>
</div>
