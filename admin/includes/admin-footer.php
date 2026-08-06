
</main><!-- /adm-main -->
</div><!-- /adm-layout -->

<!-- Modal de confirmación de borrado -->
<div class="adm-overlay" id="modal-borrar">
  <div class="adm-modal">
    <h3>¿Eliminar este elemento?</h3>
    <p>Esta acción no se puede deshacer.</p>
    <div class="adm-modal__actions">
      <button class="adm-btn adm-btn--ghost" onclick="cerrarModal()">Cancelar</button>
      <form id="form-borrar" method="POST">
        <?php echo csrf_campo(); ?>
        <input type="hidden" name="_action" value="borrar">
        <button class="adm-btn adm-btn--danger" type="submit">Sí, eliminar</button>
      </form>
    </div>
  </div>
</div>

<script>
function confirmarBorrar(action) {
  document.getElementById('form-borrar').action = action;
  document.getElementById('modal-borrar').classList.add('open');
}
function cerrarModal() {
  document.getElementById('modal-borrar').classList.remove('open');
}
document.getElementById('modal-borrar').addEventListener('click', function(e) {
  if (e.target === this) cerrarModal();
});

/* Preview de imagen antes de subir */
function previewImagen(input, previewId) {
  const preview = document.getElementById(previewId);
  if (!preview) return;
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  }
}

/* Preview de video YouTube/TikTok */
function previewVideo(urlInput) {
  const url     = urlInput.value.trim();
  const box     = document.getElementById('video-preview');
  const iframe  = document.getElementById('video-iframe');
  if (!box || !iframe) return;

  let embedUrl = '';
  const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
  const ttMatch = url.match(/tiktok\.com\/@[^/]+\/video\/(\d+)/);

  if (ytMatch)      embedUrl = 'https://www.youtube.com/embed/' + ytMatch[1];
  else if (ttMatch) embedUrl = 'https://www.tiktok.com/embed/v2/' + ttMatch[1];

  if (embedUrl) {
    iframe.src         = embedUrl;
    box.style.display  = 'block';
    // Auto-completar el campo video_id si existe
    const idField = document.getElementById('video_id');
    if (idField) idField.value = ytMatch ? ytMatch[1] : (ttMatch ? ttMatch[1] : '');
    const platField = document.getElementById('plataforma');
    if (platField) platField.value = ytMatch ? 'youtube' : 'tiktok';
  } else {
    box.style.display  = 'none';
    iframe.src         = '';
  }
}
</script>
</body>
</html>
