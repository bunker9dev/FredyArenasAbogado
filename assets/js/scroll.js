/* ============================================================
   scroll.js — Animaciones de entrada al hacer scroll
   Usa IntersectionObserver. Respeta prefers-reduced-motion.
   ============================================================ */

(function () {
  'use strict';

  // Si el usuario prefiere sin animación, la clase .rv ya
  // queda visible vía CSS (responsive.css / prefers-reduced-motion).
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const observer = new IntersectionObserver(
    entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12 }
  );

  document.querySelectorAll('.rv').forEach(el => observer.observe(el));
})();
