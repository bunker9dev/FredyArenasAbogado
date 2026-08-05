<?php
/**
 * contacto.php — Sección de contacto de la home.
 * El procesamiento del formulario vive en index.php.
 * Las variables $errores, $enviado y los valores del formulario
 * deben estar declaradas antes de incluir este archivo.
 */
$errores  = $errores  ?? [];
$enviado  = $enviado  ?? false;
$nombre   = $nombre   ?? '';
$email    = $email    ?? '';
$celular  = $celular  ?? '';
$asesoria = $asesoria ?? '';
?>
<!-- ── Sección Contacto ────────────────────────────────── -->
<section class="blk blk--dark" id="contacto">
  <div class="wrap">
    <div class="sec-head rv">
      <p class="eyebrow eyebrow--light">Contacto</p>
      <h2>Hablemos de tu caso</h2>
    </div>

    <div class="contacto-grid">

      <!-- Información -->
      <div class="contacto-info rv">
        <p>¿Buscas asesoramiento legal y quieres encontrar la representación
           más adecuada para tu caso? Escríbeme y con gusto te oriento.</p>

        <p><span class="lbl">Dirección</span>
           <span class="val">Calle 21 No 28-35, Barrio El Jardín, Neiva (Huila)</span></p>

        <p><span class="lbl">Teléfono</span>
           <span class="val">+57 310 222 5617</span></p>

        <p><span class="lbl">Correo</span>
           <span class="val">asesorias@fredyarenasabogado.com</span></p>
      </div>

      <!-- Formulario -->
      <form id="form-contacto" class="contacto-form rv"
            method="POST" action="/index.php#contacto" autocomplete="off">

        <!-- Alertas JS + PHP -->
        <div class="form-alertas">
          <?php foreach ($errores as $e): ?>
            <div class="alerta alerta--error"><?php echo h($e); ?></div>
          <?php endforeach; ?>
          <?php if ($enviado): ?>
            <div class="alerta alerta--exito">
              Mensaje enviado correctamente. Te contactaremos pronto.
            </div>
          <?php endif; ?>
        </div>

        <label for="c-nombre">Nombre</label>
        <input id="c-nombre" name="nombre" type="text"
               placeholder="Tu nombre"
               value="<?php echo h($nombre); ?>" required>

        <label for="c-email">Correo electrónico</label>
        <input id="c-email" name="email" type="email"
               placeholder="tucorreo@ejemplo.com"
               value="<?php echo h($email); ?>" required>

        <label for="c-celular">Celular</label>
        <input id="c-celular" name="celular" type="tel"
               placeholder="Tu número de celular"
               value="<?php echo h($celular); ?>" required>

        <label for="c-asesoria">Tema de la asesoría</label>
        <textarea id="c-asesoria" name="asesoria"
                  placeholder="Cuéntame brevemente tu caso"
                  required><?php echo h($asesoria); ?></textarea>

        <!-- Honeypot anti-spam (campo invisible) -->
        <div class="hp" aria-hidden="true">
          <label>No llenar
            <input type="text" name="website" tabindex="-1" autocomplete="off">
          </label>
        </div>

        <button class="btn btn--gold" type="submit">Enviar solicitud</button>
        <p class="consent">
          Al enviar autorizas el tratamiento de tus datos conforme a la
          <a href="/privacidad.php" style="color:var(--gold-soft)">política de privacidad</a>.
        </p>
      </form>
    </div>
  </div>
</section>
