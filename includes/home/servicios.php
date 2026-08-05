<?php
$servicios = [
    ['num'=>'01','titulo'=>'Derecho Administrativo',
     'desc'=>'Controversias con entidades del Estado, nulidad y restablecimiento del derecho, contratación estatal.'],
    ['num'=>'02','titulo'=>'Derecho Disciplinario',
     'desc'=>'Defensa de servidores públicos en procesos ante la Procuraduría y las oficinas de control interno.'],
    ['num'=>'03','titulo'=>'Justicia Penal Militar',
     'desc'=>'Representación de miembros de la Fuerza Pública ante la jurisdicción penal militar.'],
    ['num'=>'04','titulo'=>'Derecho Laboral · Pensión',
     'desc'=>'Reconocimiento y reliquidación de pensiones, prestaciones y controversias laborales.'],
    ['num'=>'05','titulo'=>'Responsabilidad Médica',
     'desc'=>'Reclamaciones por fallas en la prestación del servicio de salud.'],
    ['num'=>'06','titulo'=>'Insolvencia de Persona Natural',
     'desc'=>'Reorganización de deudas y acuerdos de pago para personas no comerciantes.'],
];
?>
<!-- ── Sección Servicios ────────────────────────────────── -->
<section class="blk blk--alt" id="servicios">
  <div class="wrap">
    <div class="sec-head rv">
      <p class="eyebrow">Áreas de práctica</p>
      <h2>Servicios</h2>
    </div>
    <div class="serv-grid rv">
      <?php foreach ($servicios as $s): ?>
      <div class="serv">
        <span class="serv__idx"><?php echo $s['num']; ?></span>
        <div class="serv__body">
          <h3><?php echo h($s['titulo']); ?></h3>
          <p><?php echo h($s['desc']); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
