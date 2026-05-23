<?php
$moduleKey = $moduleKey ?? 'clientes';
$moduleTitle = $moduleTitle ?? 'Modulo';
$moduleIcon = $moduleIcon ?? 'fa-table';
$moduleHint = $moduleHint ?? 'Gestion operativa';
$readOnly = $readOnly ?? false;
$statusOptionsByModule = [
  'clientes' => ['Activo', 'Inactivo', 'Pendiente'],
  'empleados' => ['Activo', 'Inactivo', 'Vacaciones', 'Pendiente'],
  'servicios' => ['Activo', 'Inactivo'],
  'categorias' => ['Activo', 'Inactivo'],
  'citas' => ['Pendiente', 'Confirmada', 'Activo', 'Completado', 'Cancelado', 'No asistio'],
  'pagos' => ['Pendiente', 'Pagado', 'Rechazado', 'Reembolsado'],
  'facturacion' => ['Pendiente', 'Pagada', 'Anulada'],
  'inventario' => ['Activo', 'Inactivo', 'Bajo stock', 'Agotado'],
  'horarios' => ['Activo', 'Inactivo'],
  'usuarios' => ['Activo', 'Inactivo', 'Bloqueado', 'Pendiente'],
  'notificaciones' => ['Pendiente', 'Enviado', 'Fallido', 'Cancelado'],
  'reportes' => ['Pendiente', 'Procesado', 'Fallido', 'Cancelado'],
];
$statusOptions = $statusOptionsByModule[$moduleKey] ?? ['Activo', 'Pendiente', 'Completado', 'Cancelado'];
?>
<section class="content-card">
  <div class="module-heading">
    <div>
      <span class="eyebrow"><i class="fa-solid <?= $moduleIcon ?>"></i> <?= htmlspecialchars($moduleHint) ?></span>
      <h2><?= htmlspecialchars($moduleTitle) ?></h2>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <button class="btn btn-light-soft" data-action="export"><i class="fa-solid fa-file-export"></i> Exportar</button>
      <?php if (!$readOnly): ?>
      <button class="btn btn-olive" data-module="<?= htmlspecialchars($moduleKey) ?>" data-action="new-record"><i class="fa-solid fa-plus"></i> Nuevo</button>
      <?php endif; ?>
    </div>
  </div>
  <div class="toolbar-grid">
    <div class="searchbox"><i class="fa-solid fa-magnifying-glass"></i><input data-table-search="<?= htmlspecialchars($moduleKey) ?>" placeholder="Buscar en <?= strtolower(htmlspecialchars($moduleTitle)) ?>"></div>
    <select class="form-select soft-input" data-table-filter="<?= htmlspecialchars($moduleKey) ?>">
      <option value="">Todos los estados</option>
      <?php foreach ($statusOptions as $option): ?>
        <option><?= htmlspecialchars($option) ?></option>
      <?php endforeach; ?>
    </select>
    <input class="form-control soft-input" type="date" data-table-date="<?= htmlspecialchars($moduleKey) ?>">
  </div>
  <div class="table-responsive">
    <table class="table app-table align-middle" data-module-table="<?= htmlspecialchars($moduleKey) ?>" <?= $readOnly ? 'data-readonly="true"' : '' ?>>
      <thead></thead>
      <tbody></tbody>
    </table>
  </div>
  <div class="pagination-row">
    <span data-table-status="<?= htmlspecialchars($moduleKey) ?>">Cargando registros...</span>
    <nav><ul class="pagination pagination-sm mb-0"><li class="page-item active"><a class="page-link" href="#">1</a></li><li class="page-item"><a class="page-link" href="#">2</a></li><li class="page-item"><a class="page-link" href="#">3</a></li></ul></nav>
  </div>
</section>

<!--
  CONECTAR CRUD <?= strtoupper($moduleKey) ?>
  - INSERTAR FETCH API en assets/js/api.js
  - IMPLEMENTAR AQUI CONSULTA MYSQL en controllers/<?= ucfirst($moduleKey) ?>Controller.php
  - LLAMAR PROCEDIMIENTO ALMACENADO si tu BD ya lo tiene definido
  - Validar permisos por rol antes de crear, editar o eliminar registros
-->
