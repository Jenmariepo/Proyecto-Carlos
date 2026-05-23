<?php $pageTitle='Citas asignadas'; $active='citas'; $role='employee'; $moduleKey='citas'; $moduleTitle='Citas asignadas'; $moduleIcon='fa-calendar-check'; $moduleHint='Cambiar estados'; include __DIR__.'/../components/header.php'; ?>
<div class="layout">
  <?php include __DIR__.'/../components/sidebar.php'; ?>
  <main class="main" data-employee-area>
    <?php include __DIR__.'/../components/topbar.php'; ?>
    <?php include __DIR__.'/../components/employee-selector.php'; ?>
    <?php $readOnly=true; include __DIR__.'/../components/module-table.php'; ?>
    <section class="content-card">
      <button class="btn btn-olive" data-action="complete-employee-appointment">Marcar cita como completada</button>
      <!-- UPDATE citas SET estado = ? WHERE id = ? AND empleado_id = SESSION empleado -->
    </section>
  </main>
</div>
<?php include __DIR__.'/../components/footer.php'; ?>
