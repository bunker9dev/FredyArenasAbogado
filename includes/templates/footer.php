<?php
/**
 * footer.php — Pie de página compartido.
 * Incluir al final de cada página, antes de </body>.
 */
?>
<footer class="site-footer">
  <div class="wrap">
    <div class="footer__grid">

      <div class="footer__info">
        <p class="name">Fredy Antonio Arenas Gutiérrez</p>
        <p>Calle 21 No 28-35, Barrio El Jardín, Neiva (Huila)</p>
        <p>+57 310 222 5617</p>
        <p>asesorias@fredyarenasabogado.com</p>
      </div>

      <div class="footer__social">
        <a href="https://www.facebook.com/FredyArenasG"
           target="_blank" rel="noopener" aria-label="Facebook">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="1.6"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M7 10v4h3v7h4v-7h3l1-4h-4v-2a1 1 0 0 1 1-1h3v-4h-3a5 5 0 0 0-5 5v2h-3"/>
          </svg>
        </a>
        <a href="https://www.instagram.com/fredyarenasgutierrez/"
           target="_blank" rel="noopener" aria-label="Instagram">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="1.6"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4m0 4a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4h-8a4 4 0 0 1-4-4z"/>
            <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0-6 0"/>
            <path d="M16.5 7.5l0 .01"/>
          </svg>
        </a>
        <a href="https://www.tiktok.com/@fredyarenas56"
           target="_blank" rel="noopener" aria-label="TikTok">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="1.6"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 7.917v4.034a9.948 9.948 0 0 1-5-1.951v4.5a6.5 6.5 0 1 1-8-6.326v4.326a2.5 2.5 0 1 0 4 2v-11.5h4.083a6.005 6.005 0 0 0 4.917 4.917z"/>
          </svg>
        </a>
        <a href="https://twitter.com/Fredy_Arenas8"
           target="_blank" rel="noopener" aria-label="Twitter / X">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="1.6"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 4.01c-1 .49-1.98.689-3 .99c-1.121-1.265-2.783-1.335-4.38-.737s-2.643 2.06-2.62 3.737v1c-3.245.083-6.135-1.395-8-4c0 0-4.182 7.433 4 11c-1.872 1.247-3.739 2.088-6 2c3.308 1.803 6.913 2.423 10.034 1.517c3.58-1.04 6.522-3.723 7.651-7.742a13.84 13.84 0 0 0 .497-3.753c0-.249 1.51-2.772 1.818-4.013z"/>
          </svg>
        </a>
      </div>
    </div>

    <div class="footer__bottom">
      <span>© <span id="footer-year"><?php echo date('Y'); ?></span>
        fredyarenasabogado.com
        · <a href="/privacidad.php">Política de privacidad</a>
      </span>
      <span>Desarrollado por
        <a href="https://ingarenas.com" target="_blank" rel="noopener">ingarenas.com</a>
      </span>
    </div>
  </div>
</footer>

<!-- Botón flotante WhatsApp -->
<a class="wa-float" href="<?php echo url_whatsapp(); ?>"
   target="_blank" rel="noopener" aria-label="Contactar por WhatsApp">
  <svg viewBox="0 0 24 24" fill="currentColor">
    <path d="M17.5 14.4c-.3-.15-1.7-.85-2-.95-.26-.1-.45-.15-.64.15-.19.28-.73.94-.9 1.13-.16.19-.33.21-.62.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.64-2.04-.17-.3-.02-.46.13-.6.13-.13.3-.34.44-.51.15-.17.2-.29.3-.48.1-.19.05-.36-.02-.5-.08-.15-.64-1.54-.88-2.11-.23-.55-.47-.48-.64-.49h-.55c-.19 0-.5.07-.76.36-.26.28-1 .98-1 2.39s1.02 2.77 1.17 2.96c.15.19 2.02 3.08 4.9 4.32.68.29 1.22.47 1.63.6.69.22 1.31.19 1.8.11.55-.08 1.7-.69 1.94-1.36.24-.67.24-1.24.17-1.36-.07-.12-.26-.19-.55-.34z"/>
    <path d="M12 2a10 10 0 00-8.6 15.06L2 22l5.06-1.33A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.18-1.14l-.3-.18-3 .79.8-2.92-.2-.31A8.2 8.2 0 1112 20.2z"/>
  </svg>
</a>

</body>
</html>
