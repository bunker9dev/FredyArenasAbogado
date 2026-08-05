/* ============================================================
   articulo.js — Lógica de la página de artículo individual
   - Compartir: WhatsApp + copiar enlace (toast)
   - ¿Te resultó útil? Sí / No → POST → "Gracias"
   - Contador de vistas: POST al cargar (una vez por sesión)
   ============================================================ */

(function () {
  'use strict';

  /* ── Toast ───────────────────────────────────────────── */
  const toast = document.getElementById('toast');
  function showToast(msg) {
    if (!toast) return;
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2000);
  }

  /* ── Compartir WhatsApp ──────────────────────────────── */
  const btnWA = document.getElementById('share-wa');
  if (btnWA) {
    btnWA.addEventListener('click', () => {
      const url  = encodeURIComponent(location.href);
      const text = encodeURIComponent(document.title + ' — ' + location.href);
      window.open('https://api.whatsapp.com/send?text=' + text, '_blank');
    });
  }

  /* ── Copiar enlace ───────────────────────────────────── */
  const btnLink = document.getElementById('share-link');
  if (btnLink) {
    btnLink.addEventListener('click', async () => {
      try { await navigator.clipboard.writeText(location.href); }
      catch (e) {
        // Fallback para navegadores sin clipboard API
        const ta = document.createElement('textarea');
        ta.value = location.href;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        ta.remove();
      }
      const span = btnLink.querySelector('span');
      if (span) { span.textContent = '¡Copiado!'; setTimeout(() => span.textContent = 'Copiar enlace', 2200); }
      showToast('Enlace copiado al portapapeles');
    });
  }

  /* ── ¿Te resultó útil? ───────────────────────────────── */
  const usefulBox = document.getElementById('useful');
  if (usefulBox) {
    usefulBox.querySelectorAll('.useful-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const valor      = btn.dataset.v;          // 'si' | 'no'
        const artId      = usefulBox.dataset.id;   // id del artículo

        // Mostrar gracias de inmediato (UX)
        usefulBox.classList.add('done');

        // POST al endpoint (best-effort; si falla no rompe nada)
        fetch('/api/feedback.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ articulo_id: artId, valor })
        }).catch(() => {});  // silencioso
      });
    });
  }

  /* ── Contador de vistas ──────────────────────────────── */
  const artId = usefulBox ? usefulBox.dataset.id : null;
  if (artId) {
    // Solo registra si no lo hizo ya en esta sesión de navegación
    const key = 'vista_art_' + artId;
    if (!sessionStorage.getItem(key)) {
      sessionStorage.setItem(key, '1');
      fetch('/api/vistas.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ articulo_id: artId })
      }).catch(() => {});
    }
  }

})();
