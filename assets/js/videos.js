/**
 * videos.js — Reproducción bajo demanda de las tarjetas de video.
 * Cada tarjeta muestra una miniatura + botón de play (facade).
 * El iframe/embed real solo se inserta cuando el usuario hace clic,
 * así ningún video se reproduce solo y solo hay uno activo a la vez.
 *
 * También registra la vista (una vez por sesión por video) y maneja
 * los botones de compartir (WhatsApp / copiar enlace) de cada tarjeta.
 */
(function () {
  var tiktokScriptCargado = false;

  /* ── Toast simple (no depende de que exista #toast en la página) ── */
  var toastEl = null;
  function showToast(msg) {
    if (!toastEl) {
      toastEl = document.createElement('div');
      toastEl.className = 'toast';
      document.body.appendChild(toastEl);
    }
    toastEl.textContent = msg;
    toastEl.classList.add('show');
    clearTimeout(toastEl._t);
    toastEl._t = setTimeout(function () { toastEl.classList.remove('show'); }, 2000);
  }

  function cargarTikTokScript() {
    if (tiktokScriptCargado) {
      if (window.tiktokEmbed && typeof window.tiktokEmbed.lib.render === 'function') {
        window.tiktokEmbed.lib.render();
      }
      return;
    }
    var s = document.createElement('script');
    s.src = 'https://www.tiktok.com/embed.js';
    s.async = true;
    document.body.appendChild(s);
    tiktokScriptCargado = true;
  }

  /* ── Registrar vista (una vez por sesión por video) ──────────── */
  function registrarVista(dbId) {
    if (!dbId || dbId === '0') return;
    var key = 'vista_video_' + dbId;
    if (sessionStorage.getItem(key)) return;
    sessionStorage.setItem(key, '1');
    fetch('/api/vistas_video.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ video_id: dbId })
    }).catch(function () {});
  }

  function reproducir(contenedor) {
    var plataforma = contenedor.getAttribute('data-platform');
    var id         = contenedor.getAttribute('data-id');
    var cite       = contenedor.getAttribute('data-cite');
    var titulo     = contenedor.getAttribute('data-title');
    var dbId       = contenedor.getAttribute('data-db-id');

    registrarVista(dbId);
    contenedor.innerHTML = '';

    if (plataforma === 'youtube') {
      var iframe = document.createElement('iframe');
      iframe.src = 'https://www.youtube.com/embed/' + encodeURIComponent(id) + '?autoplay=1&rel=0';
      iframe.title = titulo;
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframe.allowFullscreen = true;
      contenedor.appendChild(iframe);
    } else {
      var bq = document.createElement('blockquote');
      bq.className = 'tiktok-embed';
      bq.setAttribute('cite', cite);
      bq.setAttribute('data-video-id', id);
      bq.style.maxWidth = '100%';
      bq.style.minWidth = '100%';
      bq.style.margin = '0';
      var section = document.createElement('section');
      bq.appendChild(section);
      contenedor.appendChild(bq);
      cargarTikTokScript();

      // TikTok reproduce sin sonido por defecto (política de su propio
      // reproductor, no depende de nuestro código). Mostramos un aviso
      // breve para que el visitante sepa que debe activarlo.
      var aviso = document.createElement('span');
      aviso.className = 'video-card__aviso-audio';
      aviso.textContent = 'Toca el ícono de sonido en el video para activar el audio';
      contenedor.parentNode.insertBefore(aviso, contenedor.nextSibling);
      setTimeout(function () { aviso.classList.add('video-card__aviso-audio--oculto'); }, 6000);
    }
  }

  /* ── Compartir por WhatsApp ───────────────────────────────────── */
  function compartirWhatsApp(url, titulo) {
    var texto = encodeURIComponent(titulo + ' — ' + url);
    window.open('https://api.whatsapp.com/send?text=' + texto, '_blank');
  }

  /* ── Copiar enlace ────────────────────────────────────────────── */
  async function copiarEnlace(url, btn) {
    try {
      await navigator.clipboard.writeText(url);
    } catch (e) {
      var ta = document.createElement('textarea');
      ta.value = url;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
    }
    var span = btn.querySelector('span');
    if (span) {
      var original = span.textContent;
      span.textContent = '¡Copiado!';
      setTimeout(function () { span.textContent = original; }, 2200);
    }
    showToast('Enlace copiado al portapapeles');
  }

  document.addEventListener('click', function (e) {
    var facade = e.target.closest('.video-card__facade');
    if (facade) {
      var contenedor = facade.closest('.video-card__player');
      if (contenedor) reproducir(contenedor);
      return;
    }

    var btnWA = e.target.closest('.video-share-wa');
    if (btnWA) {
      var url = btnWA.getAttribute('data-url');
      var titulo = btnWA.getAttribute('data-title');
      if (url) compartirWhatsApp(url, titulo || 'Video');
      return;
    }

    var btnLink = e.target.closest('.video-share-link');
    if (btnLink) {
      var url2 = btnLink.getAttribute('data-url');
      if (url2) copiarEnlace(url2, btnLink);
      return;
    }
  });
})();