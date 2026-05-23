<section class="content-card no-print" data-employee-selector-card>
  <div class="module-heading mb-0">
    <div>
      <span class="eyebrow">Empleado activo</span>
      <h2 data-employee-name-heading>Seleccionar perfil</h2>
    </div>
    <div class="employee-picker">
      <label class="form-label mb-0" for="activeEmployeeSelect">Usando como</label>
      <select class="form-select soft-input" id="activeEmployeeSelect">
        <option value="lia">Lia Santos</option>
        <option value="nora">Nora Diaz</option>
        <option value="eva">Eva Rojas</option>
      </select>
    </div>
  </div>
  <!--
    PERFIL POR EMPLEADO:
    - En backend, este selector se reemplaza por la sesion real del usuario autenticado.
    - Filtrar citas con: WHERE citas.empleado_id = :empleado_id
    - Cargar perfil desde empleados + usuarios segun el usuario logueado.
  -->
</section>
