<?php $pageTitle='Notificaciones'; $active='notificaciones'; $role='admin'; $moduleKey='notificaciones'; $moduleTitle='Centro de notificaciones'; $moduleIcon='fa-bell'; $moduleHint='Avisos y recordatorios'; include __DIR__.'/../components/header.php'; ?>
<div class="layout">
  <?php include __DIR__.'/../components/sidebar.php'; ?>
  <main class="main">
    <?php include __DIR__.'/../components/topbar.php'; ?>
    <section class="metric-grid mb-3">
      <article class="metric"><i class="fa-solid fa-bell"></i><span>Pendientes</span><strong>3</strong><small>Por revisar</small></article>
      <article class="metric"><i class="fa-solid fa-calendar-check"></i><span>Citas</span><strong>2</strong><small>Relacionadas</small></article>
      <article class="metric"><i class="fa-solid fa-boxes-stacked"></i><span>Inventario</span><strong>1</strong><small>Alerta stock</small></article>
      <article class="metric"><i class="fa-solid fa-check"></i><span>Leidas</span><strong>1</strong><small>Hoy</small></article>
    </section>
    <section class="content-card">
      <div class="module-heading">
        <div><span class="eyebrow">Centro de avisos</span><h2>Notificaciones del sistema</h2></div>
        <button class="btn btn-olive" data-action="mark-notifications-read"><i class="fa-solid fa-check-double"></i> Marcar como leidas</button>
      </div>
      <p class="text-muted mb-0">Aqui se muestran recordatorios de citas, alertas de inventario, pagos pendientes y acciones importantes del salon.</p>
    </section>
    <?php $readOnly=true; include __DIR__.'/../components/module-table.php'; ?>
    <!-- CONECTAR con tabla recordatorios/notificaciones y filtrar por rol administrador -->
  </main>
</div>
<?php include __DIR__.'/../components/footer.php'; ?>
