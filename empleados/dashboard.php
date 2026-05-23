<?php $pageTitle='Mi agenda'; $active='dashboard'; $role='employee'; include __DIR__.'/../components/header.php'; ?>
<div class="layout">
  <?php include __DIR__.'/../components/sidebar.php'; ?>
  <main class="main" data-employee-area>
    <?php include __DIR__.'/../components/topbar.php'; ?>
    <?php include __DIR__.'/../components/employee-selector.php'; ?>
    <section class="metric-grid mb-3" data-employee-metrics>
      <article class="metric"><i class="fa-solid fa-calendar-day"></i><span>Citas asignadas</span><strong data-employee-metric="assigned">0</strong><small>Segun empleado</small></article>
      <article class="metric"><i class="fa-solid fa-check"></i><span>Completadas</span><strong data-employee-metric="completed">0</strong><small>Turno actual</small></article>
      <article class="metric"><i class="fa-solid fa-star"></i><span>Calificacion</span><strong data-employee-metric="rating">0.0</strong><small>Promedio</small></article>
      <article class="metric"><i class="fa-solid fa-chart-line"></i><span>Productividad</span><strong data-employee-metric="productivity">0%</strong><small>Semana</small></article>
    </section>
    <?php $moduleKey='citas'; $moduleTitle='Mis citas asignadas'; $moduleIcon='fa-calendar-check'; $moduleHint='Acceso limitado'; $readOnly=true; include __DIR__.'/../components/module-table.php'; ?>
    <div class="content-card"><strong>Acceso limitado:</strong> este panel muestra solo la informacion del empleado seleccionado.</div>
  </main>
</div>
<?php include __DIR__.'/../components/footer.php'; ?>
