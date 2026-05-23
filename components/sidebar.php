<?php
$role = $role ?? 'admin';
$active = $active ?? 'dashboard';
$adminItems = [
  ['dashboard', 'Dashboard', 'fa-chart-line', '/admin/dashboard.php'],
  ['clientes', 'Clientes', 'fa-user-group', '/admin/clientes.php'],
  ['empleados', 'Empleados', 'fa-id-badge', '/admin/empleados.php'],
  ['servicios', 'Servicios', 'fa-wand-magic-sparkles', '/admin/servicios.php'],
  ['citas', 'Citas', 'fa-calendar-check', '/admin/citas.php'],
  ['pagos', 'Pagos', 'fa-credit-card', '/admin/pagos.php'],
  ['facturacion', 'Facturacion', 'fa-file-invoice-dollar', '/admin/facturacion.php'],
  ['inventario', 'Inventario', 'fa-boxes-stacked', '/admin/inventario.php'],
  ['notificaciones', 'Notificaciones', 'fa-bell', '/admin/notificaciones.php'],
  ['usuarios', 'Usuarios', 'fa-users-gear', '/admin/usuarios.php'],
  ['reportes', 'Reportes', 'fa-chart-pie', '/admin/reportes.php'],
];
$employeeItems = [
  ['dashboard', 'Mi agenda', 'fa-calendar-day', '/empleados/dashboard.php'],
  ['citas', 'Citas asignadas', 'fa-calendar-check', '/empleados/citas.php'],
  ['notificaciones', 'Notificaciones', 'fa-bell', '/empleados/notificaciones.php'],
  ['productividad', 'Productividad', 'fa-chart-simple', '/empleados/productividad.php'],
  ['perfil', 'Perfil', 'fa-user', '/empleados/perfil.php'],
];
$items = $role === 'employee' ? $employeeItems : $adminItems;
?>
<aside class="app-sidebar" id="appSidebar">
  <a class="brand-lockup sidebar-brand" href="<?= $role === 'employee' ? '/empleados/dashboard.php' : '/admin/dashboard.php' ?>">
    <img class="brand-logo-img" src="/assets/img/mirror-glam-logo.svg" alt="Mirror Glam"><span>Mirror Glam</span>
  </a>
  <nav class="sidebar-menu">
    <?php foreach ($items as $item): ?>
      <a class="sidebar-link <?= $active === $item[0] ? 'active' : '' ?>" href="<?= $item[3] ?>">
        <i class="fa-solid <?= $item[2] ?>"></i><span><?= $item[1] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-note">
    <small><?= $role === 'employee' ? 'Acceso limitado de empleado' : 'Panel administrativo' ?></small>
  </div>
</aside>
