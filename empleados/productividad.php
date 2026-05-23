<?php $pageTitle='Productividad'; $active='productividad'; $role='employee'; include __DIR__.'/../components/header.php'; ?>
<div class="layout">
  <?php include __DIR__.'/../components/sidebar.php'; ?>
  <main class="main" data-employee-area>
    <?php include __DIR__.'/../components/topbar.php'; ?>
    <?php include __DIR__.'/../components/employee-selector.php'; ?>
    <section class="metric-grid mb-3" data-employee-metrics>
      <article class="metric"><i class="fa-solid fa-check"></i><span>Servicios</span><strong data-employee-metric="assigned">0</strong><small>Asignados</small></article>
      <article class="metric"><i class="fa-solid fa-calendar-check"></i><span>Completados</span><strong data-employee-metric="completed">0</strong><small>Periodo actual</small></article>
      <article class="metric"><i class="fa-solid fa-star"></i><span>Calificacion</span><strong data-employee-metric="rating">0.0</strong><small>Promedio</small></article>
      <article class="metric"><i class="fa-solid fa-chart-line"></i><span>Productividad</span><strong data-employee-metric="productivity">0%</strong><small>Semana</small></article>
    </section>
    <section class="dashboard-grid">
      <div class="content-card"><h2>Servicios realizados</h2><canvas id="salesChart" height="120"></canvas></div>
      <div class="content-card"><h2>Mix de servicios</h2><canvas id="servicesChart"></canvas></div>
    </section>
    <!-- Consultar vista SQL de productividad por empleado filtrada por usuario autenticado -->
  </main>
</div>
<?php include __DIR__.'/../components/footer.php'; ?>
