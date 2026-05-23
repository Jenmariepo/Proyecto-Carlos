<?php $pageTitle='Mi horario'; $active='horario'; $role='employee'; $moduleKey='horarios'; $moduleTitle='Mi horario'; $moduleIcon='fa-clock'; $moduleHint='Turnos asignados'; include __DIR__.'/../components/header.php'; ?>
<div class="layout">
  <?php include __DIR__.'/../components/sidebar.php'; ?>
  <main class="main" data-employee-area>
    <?php include __DIR__.'/../components/topbar.php'; ?>
    <?php include __DIR__.'/../components/employee-selector.php'; ?>
    <?php $readOnly=true; include __DIR__.'/../components/module-table.php'; ?>
  </main>
</div>
<?php include __DIR__.'/../components/footer.php'; ?>
