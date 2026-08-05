<?php
/**
 * blog.php — Sección blog de la home.
 * Carga los últimos 6 artículos publicados desde la BD.
 */

$articulos_home = [];
$db_home = db_connect();

if ($db_home) {
    $stmt = $db_home->prepare(
        "SELECT titulo, slug, area, fecha, autor_nombre, autor_apellido, imagen
         FROM blog
         WHERE estado = 'publicado'
         ORDER BY fecha DESC
         LIMIT 6"
    );
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $articulos_home[] = $row;
}
?>
<!-- ── Sección Blog ────────────────────────────────────── -->
<section class="blk" id="blog">
  <div class="wrap">
    <div class="blog-top rv">
      <div>
        <p class="eyebrow">Publicaciones recientes</p>
        <h2 style="margin-top:1rem">Blog</h2>
      </div>
      <a class="see-all" href="/blog.php">
        Ver todos los artículos
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M5 12h14M13 6l6 6-6 6"/>
        </svg>
      </a>
    </div>

    <?php if (!empty($articulos_home)): ?>
    <div class="cards rv">
      <?php foreach ($articulos_home as $a):
        $autor = trim(h($a['autor_nombre']) . ' ' . h($a['autor_apellido']));
        $url   = '/blog/' . h($a['slug']);
        $img   = imagen_blog($a['imagen'] ?? '');
      ?>
      <article class="card">
        <a href="<?php echo $url; ?>" class="card__img">
          <img loading="lazy" src="<?php echo $img; ?>" alt="<?php echo h($a['titulo']); ?>">
        </a>
        <div class="card__body">
          <span class="card__cat"><?php echo h($a['area']); ?></span>
          <h3><a href="<?php echo $url; ?>"><?php echo h($a['titulo']); ?></a></h3>
          <div class="card__meta">
            <span><?php echo fecha_es($a['fecha']); ?></span>
            <span class="dot"></span>
            <span><?php echo $autor ?: 'Fredy A. Arenas'; ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="blog-empty rv">Pronto publicaremos nuestros primeros artículos.</p>
    <?php endif; ?>
  </div>
</section>
