<?php $pageTitle='Notificaciones'; $active='notificaciones'; $role='employee'; $moduleKey='notificaciones'; $moduleTitle='Mis notificaciones'; $moduleIcon='fa-bell'; $moduleHint='Avisos asignados'; include __DIR__.'/../components/header.php'; ?>
<div class="layout">
  <?php include __DIR__.'/../components/sidebar.php'; ?>
  <main class="main" data-employee-area>
    <?php include __DIR__.'/../components/topbar.php'; ?>
    <?php include __DIR__.'/../components/employee-selector.php'; ?>
    <section class="content-card">
      <div class="module-heading">
        <div><span class="eyebrow">Avisos personales</span><h2>Notificaciones del empleado</h2></div>
        <button class="btn btn-olive" data-action="mark-notifications-read"><i class="fa-solid fa-check-double"></i> Marcar como leidas</button>
      </div>
      <p class="text-muted mb-0">Aqui veras unicamente los avisos asignados al perfil de empleado seleccionado.</p>
    </section>
    <?php $readOnly=true; include __DIR__.'/../components/module-table.php'; ?>
    <!-- SELECT * FROM recordatorios WHERE empleado_id = :empleado_id OR destino = 'Todos' -->
  </main>
</div>
<?php include __DIR__.'/../components/footer.php'; ?>
