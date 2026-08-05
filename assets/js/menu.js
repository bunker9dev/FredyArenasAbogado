/* ============================================================
   menu.js — Menú móvil (hamburguesa) y link activo
   ============================================================ */

(function () {
  'use strict';

  const burger  = document.querySelector('.nav__burger');
  const mobile  = document.querySelector('.nav__mobile');
  const menuLinks = document.querySelectorAll('.nav__menu a, .nav__mobile a');

  /* ── Abrir / cerrar menú móvil ─────────────────────────── */
  if (burger && mobile) {
    burger.addEventListener('click', () => {
      const abierto = mobile.classList.toggle('open');
      burger.setAttribute('aria-expanded', abierto);
    });

    // Cerrar al hacer clic en un enlace del menú móvil
    mobile.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        mobile.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
      });
    });

    // Cerrar si el usuario hace clic fuera del header
    document.addEventListener('click', e => {
      if (!e.target.closest('.site-header')) {
        mobile.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ── Marcar link activo según la sección visible ──────── */
  const sections = Array.from(document.querySelectorAll('main section[id]'));
  if (!sections.length) return;

  function marcarActivo() {
    const scrollY  = window.scrollY;
    let activo = sections[0].id;

    sections.forEach(sec => {
      if (sec.offsetTop - 120 <= scrollY) activo = sec.id;
    });

    menuLinks.forEach(a => {
      const href = a.getAttribute('href') || '';
      a.classList.toggle('active', href === '#' + activo || href.endsWith('/' + activo));
    });
  }

  window.addEventListener('scroll', marcarActivo, { passive: true });
  marcarActivo();
})();
