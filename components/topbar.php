<?php
$crumb = $crumb ?? $pageTitle ?? 'Panel';
$isEmployeeTopbar = ($role ?? 'admin') === 'employee';
?>
<header class="app-topbar">
  <div class="d-flex align-items-center gap-2">
    <button class="icon-btn" id="sidebarToggle" aria-label="Colapsar menu"><i class="fa-solid fa-bars"></i></button>
    <div>
      <span class="breadcrumb-soft">Mirror Glam / <?= htmlspecialchars($crumb) ?></span>
      <h1><?= htmlspecialchars($pageTitle ?? 'Panel') ?></h1>
    </div>
  </div>
  <div class="topbar-actions">
    <button class="icon-btn" id="themeToggle" aria-label="Cambiar tema"><i class="fa-solid fa-moon"></i></button>
    <a class="icon-btn notification-dot" href="<?= $isEmployeeTopbar ? '/empleados/notificaciones.php' : '/admin/notificaciones.php' ?>" aria-label="Notificaciones"><i class="fa-regular fa-bell"></i></a>
    <div class="dropdown">
      <button class="profile-pill dropdown-toggle" data-bs-toggle="dropdown">
        <span class="avatar" <?= $isEmployeeTopbar ? 'data-employee-field="initials"' : '' ?>><?= $isEmployeeTopbar ? 'LS' : 'MG' ?></span>
        <span <?= $isEmployeeTopbar ? 'data-employee-field="name"' : '' ?>><?= $isEmployeeTopbar ? 'Lia Santos' : 'Admin' ?></span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end soft-dropdown">
        <li><a class="dropdown-item" href="<?= $isEmployeeTopbar ? '/empleados/perfil.php' : '/admin/usuarios.php' ?>">Perfil</a></li>
        <li><a class="dropdown-item" href="/auth/login.php">Cerrar sesion</a></li>
      </ul>
    </div>
  </div>
</header>
