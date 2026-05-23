<?php $pageTitle = 'Dashboard'; $active = 'dashboard'; $role = 'admin'; include __DIR__ . '/../components/header.php'; ?>
<div class="layout">
  <?php include __DIR__ . '/../components/sidebar.php'; ?>
  <main class="main">
    <?php include __DIR__ . '/../components/topbar.php'; ?>
    <section class="metric-grid mb-3">
      <?php foreach ([['citasHoy','Citas de hoy','0','fa-calendar-check','Agenda del dia'],['ingresosDia','Ingresos del dia','RD$ 0','fa-sack-dollar','Facturas de hoy'],['servicioTop','Servicios top','Sin datos','fa-wand-magic-sparkles','Solicitudes'],['productividad','Productividad','0%','fa-chart-line','Citas completadas']] as $m): ?>
      <article class="metric"><i class="fa-solid <?= $m[3] ?>"></i><span><?= $m[1] ?></span><strong data-dashboard-metric="<?= $m[0] ?>"><?= $m[2] ?></strong><small data-dashboard-note="<?= $m[0] ?>"><?= $m[4] ?></small></article>
      <?php endforeach; ?>
    </section>
    <section class="dashboard-grid">
      <div class="content-card"><div class="module-heading"><div><span class="eyebrow">Estadisticas</span><h2>Ingresos semanales</h2></div></div><canvas id="salesChart" height="120"></canvas></div>
      <div class="content-card"><div class="module-heading"><div><span class="eyebrow">Servicios</span><h2>Mas solicitados</h2></div></div><canvas id="servicesChart"></canvas></div>
    </section>
    <section class="content-card">
      <div class="module-heading"><div><span class="eyebrow">Actividad de agenda</span><h2>Citas recientes</h2></div><a class="btn btn-olive" href="/admin/citas.php">Gestionar citas</a></div>
      <div class="table-responsive"><table class="table app-table align-middle"><thead><tr><th>Hora</th><th>Cliente</th><th>Servicio</th><th>Empleado</th><th>Estado</th></tr></thead><tbody data-dashboard-agenda><tr><td colspan="5">Cargando agenda...</td></tr></tbody></table></div>
      <!-- IMPLEMENTAR AQUI CONSULTA MYSQL para vistas SQL de agenda diaria, ingresos, clientes frecuentes y productividad por empleado -->
    </section>
  </main>
</div>
<?php include __DIR__ . '/../components/footer.php'; ?>
