/* ============================================================
   app.js — Punto de entrada JS
   Enlaza todos los módulos. Se carga con defer en el <head>.
   ============================================================ */

/* Los scripts individuales se cargan vía <script defer> en
   el template head.php, en este orden:
     1. menu.js
     2. scroll.js
     3. contacto.js   (solo en páginas con formulario)

   Este archivo puede usarse para lógica global adicional:
   por ejemplo, el año dinámico del footer.                    */

document.addEventListener('DOMContentLoaded', () => {

  /* Año dinámico en el footer */
  const anio = document.getElementById('footer-year');
  if (anio) anio.textContent = new Date().getFullYear();

});
