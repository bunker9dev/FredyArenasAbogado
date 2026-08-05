/* ============================================================
   contacto.js — Validación del formulario de contacto
   ============================================================ */

(function () {
  'use strict';

  const form = document.getElementById('form-contacto');
  if (!form) return;

  /* ── Referencia a campos ─────────────────────────────── */
  const campos = {
    nombre:   form.querySelector('[name="nombre"]'),
    email:    form.querySelector('[name="email"]'),
    celular:  form.querySelector('[name="celular"]'),
    asesoria: form.querySelector('[name="asesoria"]'),
  };
  const contenedorAlertas = form.querySelector('.form-alertas');

  /* ── Utilidades ──────────────────────────────────────── */
  function limpiarAlertas() {
    if (contenedorAlertas) contenedorAlertas.innerHTML = '';
    Object.values(campos).forEach(c => c && c.classList.remove('campo-error'));
  }

  function mostrarError(msg, campo) {
    if (contenedorAlertas) {
      const div = document.createElement('div');
      div.className = 'alerta alerta--error';
      div.textContent = msg;
      contenedorAlertas.appendChild(div);
    }
    if (campo) campo.classList.add('campo-error');
  }

  function validarEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  }

  /* ── Validación al enviar ────────────────────────────── */
  form.addEventListener('submit', function (e) {
    limpiarAlertas();
    let valido = true;

    if (!campos.nombre || !campos.nombre.value.trim()) {
      mostrarError('El nombre es obligatorio.', campos.nombre);
      valido = false;
    }
    if (!campos.email || !validarEmail(campos.email.value.trim())) {
      mostrarError('Escribe un correo electrónico válido.', campos.email);
      valido = false;
    }
    if (!campos.celular || !campos.celular.value.trim()) {
      mostrarError('El celular es obligatorio.', campos.celular);
      valido = false;
    }
    if (!campos.asesoria || !campos.asesoria.value.trim()) {
      mostrarError('Cuéntanos el tema de la asesoría.', campos.asesoria);
      valido = false;
    }

    if (!valido) {
      e.preventDefault();
      // Scroll al primer error
      const primero = form.querySelector('.campo-error');
      if (primero) primero.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });

  /* ── Quitar estado de error al escribir ─────────────── */
  Object.values(campos).forEach(c => {
    if (!c) return;
    c.addEventListener('input', () => c.classList.remove('campo-error'));
  });
})();
