<?php $pageTitle='Historial'; $active='historial'; $role='employee'; $moduleKey='citas'; $moduleTitle='Historial de servicios realizados'; $moduleIcon='fa-clock-rotate-left'; $moduleHint='Servicios completados'; include __DIR__.'/../components/header.php'; ?>
<div class="layout">
  <?php include __DIR__.'/../components/sidebar.php'; ?>
  <main class="main" data-employee-area data-history-only="true">
    <?php include __DIR__.'/../components/topbar.php'; ?>
    <?php include __DIR__.'/../components/employee-selector.php'; ?>
    <?php $readOnly=true; include __DIR__.'/../components/module-table.php'; ?>
  </main>
</div>
<?php include __DIR__.'/../components/footer.php'; ?>
