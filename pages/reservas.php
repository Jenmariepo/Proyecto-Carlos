<?php $pageTitle='Reservas'; include __DIR__.'/../components/header.php'; include __DIR__.'/../components/public-nav.php'; ?>
<main class="section-pad" style="padding-top:7rem">
  <div class="container">
    <div class="section-title booking-title"><span class="eyebrow">Reservas online</span><h2>Agenda tu momento Mirror Glam</h2><p>Elige tu servicio, colaboradora y horario disponible en pocos pasos.</p></div>
    <div class="booking-layout booking-single">
      <aside class="content-card">
        <h2>Detalles de reserva</h2>
        <form id="bookingForm">
          <label class="form-label">Cliente</label>
          <input class="form-control mb-3" name="cliente" required placeholder="Nombre del cliente">
          <label class="form-label">Telefono</label>
          <input class="form-control mb-3" name="telefono" required placeholder="809-000-0000">
          <label class="form-label">Fecha</label>
          <input class="form-control mb-3" name="fecha" id="bookingDate" type="date" required value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
          <label class="form-label">Servicio</label>
          <select class="form-select mb-3" name="servicio" data-booking-step="service"><option>Peinado glam</option><option>Manicure gel</option><option>Maquillaje social</option><option>Tratamiento capilar</option></select>
          <label class="form-label">Empleado</label>
          <select class="form-select mb-3" name="empleado" id="bookingEmployee" data-booking-step="employee"><option>Lia Santos</option><option>Nora Diaz</option><option>Eva Rojas</option></select>
          <label class="form-label">Horario disponible</label>
          <input type="hidden" name="hora" id="bookingTime" required>
          <div class="booking-slots mb-3" id="bookingSlots"></div>
          <small class="booking-help">Selecciona una fecha y una especialista para ver las horas disponibles.</small>
          <button class="btn btn-olive w-100" type="submit">Confirmar reserva</button>
        </form>
        <!--
          SISTEMA DE RESERVAS:
          - Consultar servicios desde tabla servicios
          - Consultar empleados disponibles desde empleados + horarios
          - LLAMAR PROCEDIMIENTO ALMACENADO para horarios disponibles
          - INSERTAR en citas y detalle_citas
          - Crear recordatorio en recordatorios
        -->
      </aside>
    </div>
  </div>
</main>
<?php include __DIR__.'/../components/footer.php'; ?>
