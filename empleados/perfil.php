<?php $pageTitle='Perfil personal'; $active='perfil'; $role='employee'; include __DIR__.'/../components/header.php'; ?>
<div class="layout">
  <?php include __DIR__.'/../components/sidebar.php'; ?>
  <main class="main" data-employee-area>
    <?php include __DIR__.'/../components/topbar.php'; ?>
    <?php include __DIR__.'/../components/employee-selector.php'; ?>
    <section class="content-card">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="soft-card text-center">
            <span class="brand-seal mb-3" data-employee-field="initials">LS</span>
            <h2 data-employee-field="name">Lia Santos</h2>
            <p data-employee-field="role">Makeup artist</p>
            <div class="employee-profile-stats">
              <span><strong data-employee-field="rating">4.9</strong> calificacion</span>
              <span><strong data-employee-field="productivity">88%</strong> productividad</span>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <form class="soft-card" id="employeeProfileForm">
            <label class="form-label">Nombre</label>
            <input class="form-control mb-3" data-employee-input="name" value="Lia Santos">
            <label class="form-label">Telefono</label>
            <input class="form-control mb-3" data-employee-input="phone" value="809-555-0101">
            <label class="form-label">Especialidad</label>
            <input class="form-control mb-3" data-employee-input="specialty" value="Maquillaje">
            <label class="form-label">Correo</label>
            <input class="form-control mb-3" data-employee-input="email" value="lia@mirrorglam.do">
            <button class="btn btn-olive">Guardar cambios</button>
          </form>
        </div>
      </div>
      <!-- SELECT * FROM empleados WHERE id = :empleado_id; actualizar solo el perfil que corresponde al empleado autenticado -->
    </section>
  </main>
</div>
<?php include __DIR__.'/../components/footer.php'; ?>
