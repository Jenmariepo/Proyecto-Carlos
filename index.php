<?php $pageTitle = 'Inicio'; include __DIR__ . '/components/header.php'; include __DIR__ . '/components/public-nav.php'; ?>
<main>
  <section class="hero">
    <div class="container">
      <span class="eyebrow">Salon premium y agenda inteligente</span>
      <img class="hero-logo" src="/assets/img/mirror-glam-logo.svg" alt="Logo Mirror Glam">
      <h1>Mirror Glam</h1>
      <p>Belleza elegante, atencion precisa y reservas sin friccion para una experiencia moderna desde el primer clic hasta el ultimo detalle.</p>
      <div class="hero-actions">
        <a class="btn btn-olive btn-lg" href="/pages/reservas.php"><i class="fa-solid fa-calendar-plus"></i> Reservar ahora</a>
        <a class="btn btn-light-soft btn-lg" href="/auth/login.php"><i class="fa-solid fa-user-lock"></i> Acceso al sistema</a>
      </div>
      <div class="hero-stats">
        <div class="stat-chip"><strong>4.9/5</strong><span>Experiencia cliente</span></div>
        <div class="stat-chip"><strong>18+</strong><span>Servicios glam</span></div>
        <div class="stat-chip"><strong>24h</strong><span>Reservas online</span></div>
      </div>
    </div>
  </section>

  <section class="section-pad" id="nosotros">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-5">
          <div class="section-title mb-0">
            <span class="eyebrow">Quienes somos</span>
            <h2>Un salon pensado para verse y sentirse impecable</h2>
            <p>Mirror Glam nace para ofrecer una experiencia de belleza organizada, calida y elegante. Cuidamos cada cita como un momento personal: escuchamos lo que buscas, recomendamos lo que favorece tu estilo y dejamos registro de tus preferencias para que cada visita sea mas precisa.</p>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="about-grid">
            <?php foreach ([['Atencion personalizada','Cada clienta recibe orientacion segun su ocasion, estilo y rutina.','fa-heart'],['Gestion ordenada','Usamos agenda, historial y seguimiento para evitar esperas y confusiones.','fa-calendar-check'],['Ambiente premium','Espacio delicado, limpio y femenino para disfrutar el proceso.','fa-spa'],['Resultados consistentes','Trabajamos con protocolos claros, productos cuidados y revision final.','fa-star']] as $item): ?>
            <article class="soft-card">
              <div class="icon-bubble"><i class="fa-solid <?= $item[2] ?>"></i></div>
              <h3><?= $item[0] ?></h3>
              <p><?= $item[1] ?></p>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-pad soft-band">
    <div class="container">
      <div class="section-title">
        <span class="eyebrow">Como nos manejamos</span>
        <h2>Un proceso claro desde la reserva hasta el resultado final</h2>
      </div>
      <div class="process-grid">
        <?php foreach ([['1','Reservas organizadas','Seleccionas servicio, colaboradora y horario disponible.'],['2','Confirmacion y seguimiento','Validamos tu cita y dejamos todo listo antes de tu llegada.'],['3','Servicio con historial','Consultamos preferencias, notas y visitas anteriores.'],['4','Cierre profesional','Registramos pago, factura y recomendaciones para tu proxima visita.']] as $step): ?>
        <article class="process-step">
          <span><?= $step[0] ?></span>
          <h3><?= $step[1] ?></h3>
          <p><?= $step[2] ?></p>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section-pad" id="beneficios">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6">
          <div class="section-title mb-0">
            <span class="eyebrow">Beneficios</span>
            <h2>Un salon organizado se siente mas lujoso</h2>
            <p>Mirror Glam combina trato humano, seguimiento de citas, historial de clientes y una administracion visual pensada para equipos de belleza premium.</p>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="row g-3">
            <?php foreach ([['Reservas online','Agenda tu cita sin llamadas innecesarias.'],['Recordatorios','Recibe seguimiento para no perder tu turno.'],['Facturacion clara','Pagos y comprobantes ordenados.'],['Inventario visual','Productos controlados para mantener calidad.'],['Historial de clientas','Preferencias y servicios anteriores disponibles.'],['Atencion puntual','Horarios y agenda coordinados.']] as $b): ?>
            <div class="col-sm-6">
              <div class="soft-card benefit-card">
                <i class="fa-solid fa-check"></i>
                <h3><?= $b[0] ?></h3>
                <p><?= $b[1] ?></p>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-pad" id="contacto">
    <div class="container">
      <div class="content-card">
        <div class="row g-4 align-items-center">
          <div class="col-lg-8">
            <span class="eyebrow">Contacto</span>
            <h2>Lista para brillar esta semana</h2>
            <p>Agenda tu cita o entra al sistema administrativo para gestionar la operacion.</p>
          </div>
          <div class="col-lg-4 text-lg-end">
            <a class="btn btn-olive btn-lg" href="/pages/reservas.php">Reservar</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<footer class="footer-public">
  <div class="container d-flex flex-wrap justify-content-between gap-3">
    <div class="brand-lockup"><img class="brand-logo-img" src="/assets/img/mirror-glam-logo.svg" alt="Mirror Glam"><span>Mirror Glam</span></div>
    <span>Salon premium &middot; Santo Domingo &middot; 2026</span>
  </div>
</footer>
<?php include __DIR__ . '/components/footer.php'; ?>
