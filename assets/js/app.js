document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => document.getElementById('appLoader')?.classList.add('loaded'), 350);
  applySavedTheme();

  document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-open');
    document.body.classList.toggle('collapsed');
  });

  document.getElementById('themeToggle')?.addEventListener('click', () => {
    const html = document.documentElement;
    const nextTheme = html.dataset.theme === 'dark' ? 'light' : 'dark';
    html.dataset.theme = nextTheme;
    localStorage.setItem('mirror_glam_theme', nextTheme);
    updateThemeIcon();
  });

  document.querySelector('[data-action="notify"]')?.addEventListener('click', () => {
    Swal.fire({ title: 'Notificaciones', text: 'Tienes 3 citas pendientes por confirmar.', icon: 'info', confirmButtonColor: '#788260' });
  });

  document.querySelectorAll('[data-module-table]').forEach(async table => {
    const key = table.dataset.moduleTable;
    renderTable(table, getScopedTableData(table, await MirrorGlamAPI.list(key)));
  });

  document.querySelectorAll('[data-action="export"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const table = btn.closest('.content-card')?.querySelector('[data-module-table]');
      if (!table) {
        Swal.fire('Exportacion lista', 'Aqui conectas PDF/Excel desde el controlador de reportes.', 'success');
        return;
      }
      exportTableToCsv(table, `${table.dataset.moduleTable}-mirror-glam.csv`);
    });
  });

  document.querySelectorAll('[data-table-search]').forEach(input => {
    input.addEventListener('input', () => filterTable(input.dataset.tableSearch, input.value));
  });

  document.querySelectorAll('[data-table-filter], [data-table-date]').forEach(input => {
    input.addEventListener('input', () => applyTableFilters(input.dataset.tableFilter || input.dataset.tableDate));
    input.addEventListener('change', () => applyTableFilters(input.dataset.tableFilter || input.dataset.tableDate));
  });

  document.getElementById('loginForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const credentials = Object.fromEntries(new FormData(e.target).entries());
    const result = await MirrorGlamAPI.login(credentials);
    if (result.ok) {
      window.location.href = result.role === 'employee' ? '/empleados/dashboard.php' : '/admin/dashboard.php';
      return;
    }
    Swal.fire('Acceso no valido', result.message || 'Revisa tus credenciales.', 'error');
  });

  document.addEventListener('click', event => {
    const newRecord = event.target.closest('[data-action="new-record"]');
    if (newRecord) {
      openCrudModal(newRecord.dataset.module);
      return;
    }

    const editRecord = event.target.closest('[data-action="edit-record"]');
    if (editRecord) {
      openCrudModal(editRecord.dataset.module, Number(editRecord.dataset.index), Number(editRecord.dataset.id));
      return;
    }

    const deleteRecord = event.target.closest('[data-action="delete-record"]');
    if (deleteRecord) {
      confirmDelete(deleteRecord.dataset.module, Number(deleteRecord.dataset.index), Number(deleteRecord.dataset.id));
      return;
    }

    const bookingDay = event.target.closest('.calendar-day[data-booking-step]');
    if (!bookingDay) return;
    document.querySelectorAll('.calendar-day[data-booking-step]').forEach(item => item.classList.remove('active'));
    bookingDay.classList.add('active');
    Swal.fire({ title: 'Fecha seleccionada', text: 'Revisa los horarios disponibles para completar la reserva.', icon: 'success', timer: 1300, showConfirmButton: false });
  });

  initCharts();
  renderCalendar();
  initReports();
  initEmployeeContext();
  initNotifications();
  initCrudForms();
  initBookingAvailability();
  initBookingCatalog();
});

function applySavedTheme() {
  const theme = localStorage.getItem('mirror_glam_theme') || document.documentElement.dataset.theme || 'light';
  document.documentElement.dataset.theme = theme;
  updateThemeIcon();
}

function updateThemeIcon() {
  const icon = document.querySelector('#themeToggle i');
  if (!icon) return;
  const isDark = document.documentElement.dataset.theme === 'dark';
  icon.className = `fa-solid ${isDark ? 'fa-sun' : 'fa-moon'}`;
}

window.MirrorGlamTableCache = {};

function renderTable(table, data) {
  const isReadOnly = table.dataset.readonly === 'true';
  window.MirrorGlamTableCache[table.dataset.moduleTable] = data;
  table.querySelector('thead').innerHTML = `<tr>${data.columns.map(col => `<th>${col}</th>`).join('')}${isReadOnly ? '' : '<th>Acciones</th>'}</tr>`;
  table.querySelector('tbody').innerHTML = data.rows.length ? data.rows.map((row, index) => `
    <tr>
      ${row.map((cell, i) => i === row.length - 1 ? `<td>${statusPill(cell)}</td>` : `<td>${cell}</td>`).join('')}
      ${isReadOnly ? '' : `
      <td>
        <button class="icon-btn" data-action="edit-record" data-module="${table.dataset.moduleTable}" data-index="${index}" data-id="${data.ids?.[index] || 0}" aria-label="Editar"><i class="fa-regular fa-pen-to-square"></i></button>
        <button class="icon-btn" data-action="delete-record" data-module="${table.dataset.moduleTable}" data-index="${index}" data-id="${data.ids?.[index] || 0}" aria-label="Eliminar"><i class="fa-regular fa-trash-can"></i></button>
      </td>`}
    </tr>`).join('') : `<tr><td colspan="${data.columns.length + (isReadOnly ? 0 : 1)}">No hay registros para mostrar.</td></tr>`;
  updateTableStatus(table, data);
}

function updateTableStatus(table, data) {
  const status = document.querySelector(`[data-table-status="${table.dataset.moduleTable}"]`);
  if (!status) return;
  const count = data.rows?.length || 0;
  status.textContent = data.source === 'mysql'
    ? `${count} registro${count === 1 ? '' : 's'} cargado${count === 1 ? '' : 's'} correctamente.`
    : `${count} registro${count === 1 ? '' : 's'} en modo local. Revisa la conexion.`;
}

function getScopedTableData(table, data) {
  if (table.closest('[data-employee-area]') && table.dataset.moduleTable === 'notificaciones') {
    const employee = MirrorGlamAPI.getActiveEmployee();
    return { ...data, rows: data.rows.filter(row => row[1] === employee.name || row[1] === 'Todos') };
  }

  if (table.closest('[data-employee-area]') && table.dataset.moduleTable === 'horarios') {
    const employee = MirrorGlamAPI.getActiveEmployee();
    const employeeIndex = data.columns.indexOf('Empleado');
    if (employeeIndex === -1) return data;
    const scoped = data.rows
      .map((row, index) => ({ row, id: data.ids?.[index] }))
      .filter(item => item.row[employeeIndex] === employee.name);
    return {
      ...data,
      columns: data.columns.filter((_, index) => index !== employeeIndex),
      rows: scoped.map(item => item.row.filter((_, index) => index !== employeeIndex)),
      ids: scoped.map(item => item.id)
    };
  }

  if (!table.closest('[data-employee-area]') || table.dataset.moduleTable !== 'citas') return data;

  const employee = MirrorGlamAPI.getActiveEmployee();
  const employeeIndex = data.columns.indexOf('Empleado');
  if (employeeIndex === -1) return data;

  const historyOnly = table.closest('[data-history-only="true"]');
  const statusIndex = data.columns.indexOf('Estado');
  const scoped = data.rows
    .map((row, index) => ({ row, id: data.ids?.[index] }))
    .filter(item => item.row[employeeIndex] === employee.name)
    .filter(item => !historyOnly || item.row[statusIndex] === 'Completado');

  const rows = scoped.map(item => item.row.filter((_, index) => index !== employeeIndex));

  const ids = scoped.map(item => item.id);

  const columns = data.columns.filter((_, index) => index !== employeeIndex);
  return { ...data, columns, rows, ids };
}

function initNotifications() {
  document.querySelectorAll('[data-action="mark-notifications-read"]').forEach(button => {
    button.addEventListener('click', async () => {
      const employeeArea = document.querySelector('[data-employee-area]');
      const employee = employeeArea ? MirrorGlamAPI.getActiveEmployee() : null;
      const data = await MirrorGlamAPI.list('notificaciones');
      const updates = data.rows
        .map((row, index) => ({ row, dbId: data.ids?.[index], index }))
        .filter(item => !employee || item.row[1] === employee.name || item.row[1] === 'Todos' || item.row[1] === 'Admin')
        .filter(item => item.row[item.row.length - 1] === 'Pendiente');
      await Promise.all(updates.map(item => {
        const next = [...item.row];
        next[next.length - 1] = 'Enviado';
        return MirrorGlamAPI.save('notificaciones', { id: item.index, dbId: item.dbId, row: next });
      }));
      document.querySelectorAll('[data-module-table="notificaciones"]').forEach(table => {
        refreshModuleTable('notificaciones');
      });
      Swal.fire('Listo', 'Las notificaciones pendientes fueron marcadas como leidas.', 'success');
    });
  });
}

function initCrudForms() {
  document.getElementById('bookingForm')?.addEventListener('submit', async event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.target).entries());
    if (!data.hora) {
      Swal.fire('Selecciona un horario', 'Elige una hora disponible para completar la reserva.', 'info');
      return;
    }
    const row = [`${data.fecha} ${data.hora}`, data.cliente, data.servicio, data.empleado, 'Pendiente'];
    const response = await MirrorGlamAPI.save('citas', { id: null, row, extra: { telefono: data.telefono } });
    refreshModuleTable('citas', response.data);
    event.target.reset();
    initBookingAvailability();
    Swal.fire('Reserva creada', 'Tu cita fue registrada correctamente.', 'success');
  });

  document.getElementById('employeeProfileForm')?.addEventListener('submit', async event => {
    event.preventDefault();
    const employee = MirrorGlamAPI.getActiveEmployee();
    const payload = {
      name: document.querySelector('[data-employee-input="name"]')?.value.trim(),
      phone: document.querySelector('[data-employee-input="phone"]')?.value.trim(),
      specialty: document.querySelector('[data-employee-input="specialty"]')?.value.trim(),
      email: document.querySelector('[data-employee-input="email"]')?.value.trim()
    };
    const response = await MirrorGlamAPI.saveEmployeeProfile(employee.name, payload);
    if (!response.ok) {
      Swal.fire('No se pudo guardar', response.message || 'Revisa los datos del perfil.', 'error');
      return;
    }
    const updated = MirrorGlamAPI.updateEmployee(employee.id, payload);
    updateEmployeeNameInAppointments(employee.name, updated.name);
    applyEmployeeContext();
    Swal.fire('Perfil actualizado', 'Los datos del empleado fueron actualizados correctamente.', 'success');
  });

  document.querySelector('[data-action="complete-employee-appointment"]')?.addEventListener('click', async () => {
    const employee = MirrorGlamAPI.getActiveEmployee();
    const citas = await MirrorGlamAPI.list('citas');
    const employeeIndex = citas.columns.indexOf('Empleado');
    const statusIndex = citas.columns.indexOf('Estado');
    const rowIndex = citas.rows.findIndex(row =>
      row[employeeIndex] === employee.name &&
      ['Pendiente', 'Confirmada', 'Activo'].includes(row[statusIndex])
    );

    if (rowIndex === -1) {
      Swal.fire('Sin citas pendientes', 'No hay citas pendientes para marcar como completadas.', 'info');
      return;
    }

    const row = [...citas.rows[rowIndex]];
    row[statusIndex] = 'Completado';
    const response = await MirrorGlamAPI.save('citas', {
      id: rowIndex,
      dbId: citas.ids?.[rowIndex],
      row
    });

    if (!response.ok) {
      Swal.fire('No se pudo actualizar', response.message || 'Intenta nuevamente.', 'error');
      return;
    }

    refreshEmployeeTables();
    Swal.fire('Cita completada', 'La cita fue marcada como completada correctamente.', 'success');
  });

  document.getElementById('recoverForm')?.addEventListener('submit', event => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(event.target).entries());
    MirrorGlamAPI.recover(data.email).then(response => {
      if (!response.ok) {
        Swal.fire('No se pudo registrar', response.message || 'Revisa el correo ingresado.', 'error');
        return;
      }
      event.target.reset();
      Swal.fire('Solicitud enviada', response.message || 'La recuperacion fue registrada correctamente.', 'success');
    });
  });
}

function initBookingAvailability() {
  const form = document.getElementById('bookingForm');
  const dateInput = document.getElementById('bookingDate');
  const employeeInput = document.getElementById('bookingEmployee');
  if (!form || !dateInput || !employeeInput) return;

  dateInput.removeEventListener('change', renderBookingSlots);
  employeeInput.removeEventListener('change', renderBookingSlots);
  dateInput.addEventListener('change', renderBookingSlots);
  employeeInput.addEventListener('change', renderBookingSlots);
  renderBookingSlots();
}

async function initBookingCatalog() {
  const serviceInput = document.querySelector('[name="servicio"][data-booking-step="service"]');
  const employeeInput = document.getElementById('bookingEmployee');
  const dateInput = document.getElementById('bookingDate');
  if (!serviceInput || !employeeInput) return;

  if (dateInput && !dateInput.value) {
    dateInput.value = new Date().toISOString().slice(0, 10);
  }

  const [servicios, empleados] = await Promise.all([
    MirrorGlamAPI.list('servicios'),
    MirrorGlamAPI.list('empleados')
  ]);

  const activeServices = servicios.rows.filter(row => row[row.length - 1] === 'Activo');
  const activeEmployees = empleados.rows.filter(row => row[row.length - 1] === 'Activo');

  if (activeServices.length) {
    serviceInput.innerHTML = activeServices.map(row => `<option>${escapeHtml(row[0])}</option>`).join('');
  }
  if (activeEmployees.length) {
    employeeInput.innerHTML = activeEmployees.map(row => `<option>${escapeHtml(row[0])}</option>`).join('');
  }

  renderBookingSlots();
}

async function renderBookingSlots() {
  const date = document.getElementById('bookingDate')?.value;
  const employee = document.getElementById('bookingEmployee')?.value;
  const hiddenTime = document.getElementById('bookingTime');
  const slotsContainer = document.getElementById('bookingSlots');
  if (!date || !employee || !hiddenTime || !slotsContainer) return;

  const slots = ['9:00 AM', '10:30 AM', '12:00 PM', '2:00 PM', '4:00 PM'];
  const citas = await MirrorGlamAPI.list('citas');
  const busySlots = citas.rows
    .filter(row => String(row[0]).startsWith(date) && row[3] === employee && !['Cancelado', 'No asistio'].includes(row[row.length - 1]))
    .map(row => row[0].replace(`${date} `, '').trim());

  hiddenTime.value = '';
  slotsContainer.innerHTML = slots.map(slot => {
    const busy = busySlots.includes(slot);
    return `<button class="slot-btn ${busy ? 'busy' : 'available'}" type="button" data-slot="${slot}" ${busy ? 'disabled' : ''}>
      <span>${slot}</span><small>${busy ? 'Ocupado' : 'Disponible'}</small>
    </button>`;
  }).join('');

  slotsContainer.querySelectorAll('.slot-btn.available').forEach(button => {
    button.addEventListener('click', () => {
      slotsContainer.querySelectorAll('.slot-btn').forEach(item => item.classList.remove('active'));
      button.classList.add('active');
      hiddenTime.value = button.dataset.slot;
    });
  });
}

function refreshModuleTable(moduleName, data = null) {
  document.querySelectorAll(`[data-module-table="${moduleName}"]`).forEach(async table => {
    const moduleData = data || await MirrorGlamAPI.list(moduleName);
    renderTable(table, getScopedTableData(table, moduleData));
  });
}

function updateEmployeeNameInAppointments(previousName, nextName) {
  const store = MirrorGlamAPI.getStore();
  store.citas.rows = store.citas.rows.map(row => row[3] === previousName ? [row[0], row[1], row[2], nextName, row[4]] : row);
  store.notificaciones.rows = store.notificaciones.rows.map(row => row[1] === previousName ? [row[0], nextName, row[2], row[3]] : row);
  MirrorGlamAPI.setStore(store);
}

async function refreshEmployeeTables() {
  document.querySelectorAll('[data-employee-area] [data-module-table]').forEach(async table => {
    const key = table.dataset.moduleTable;
    renderTable(table, getScopedTableData(table, await MirrorGlamAPI.list(key)));
  });
}

function statusPill(status) {
  const cls = status.toLowerCase().replaceAll(' ', '-');
  return `<span class="status-pill status-${cls}"><i class="fa-solid fa-circle"></i>${status}</span>`;
}

function filterTable(moduleName, term) {
  applyTableFilters(moduleName, term);
}

function applyTableFilters(moduleName, explicitSearch = null) {
  const table = document.querySelector(`[data-module-table="${moduleName}"]`);
  if (!table) return;
  const search = explicitSearch ?? document.querySelector(`[data-table-search="${moduleName}"]`)?.value ?? '';
  const state = document.querySelector(`[data-table-filter="${moduleName}"]`)?.value ?? '';
  const date = document.querySelector(`[data-table-date="${moduleName}"]`)?.value ?? '';
  const needle = search.toLowerCase();

  table?.querySelectorAll('tbody tr').forEach(row => {
    const text = row.textContent.toLowerCase();
    const cells = Array.from(row.children).map(cell => cell.textContent.trim());
    const matchesSearch = !needle || text.includes(needle);
    const matchesState = !state || cells[cells.length - 2]?.includes(state) || cells[cells.length - 1]?.includes(state);
    const matchesDate = !date || cells.some(cell => cell.startsWith(date));
    row.style.display = matchesSearch && matchesState && matchesDate ? '' : 'none';
  });
}

function openCrudModal(moduleName, id = null, dbId = null) {
  const moduleData = window.MirrorGlamTableCache[moduleName] || MirrorGlamAPI.getStore()[moduleName];
  const columns = moduleData?.columns || [];
  const row = id === null ? [] : moduleData.rows[id];
  const inputs = columns.map((column, index) => {
    const value = normalizeFieldValue(row?.[index] || defaultFieldValue(moduleName, column), moduleName, column);
    if (column.toLowerCase() === 'estado') {
      const options = statusOptions(moduleName, value);
      return `
        <label class="swal-label">${column}</label>
        <select class="swal2-select" id="crud-field-${index}">
          ${options.map(option => `<option ${value === option ? 'selected' : ''}>${option}</option>`).join('')}
        </select>`;
    }
    const dateType = dateFieldType(moduleName, column);
    if (dateType) {
      return `
        <label class="swal-label">${column}</label>
        <input class="swal2-input" id="crud-field-${index}" type="${dateType}" value="${escapeHtml(value)}" placeholder="${column}">`;
    }
    if (timeRangeField(moduleName, column)) {
      const [start, end] = splitTimeRange(value);
      return `
        <label class="swal-label">${column}</label>
        <div class="swal-time-range" data-time-range="${index}">
          <input class="swal2-input" id="crud-field-${index}-start" type="time" value="${escapeHtml(start)}" aria-label="${column} inicio">
          <input class="swal2-input" id="crud-field-${index}-end" type="time" value="${escapeHtml(end)}" aria-label="${column} fin">
        </div>
        <input id="crud-field-${index}" type="hidden" value="${escapeHtml(value)}">`;
    }
    if (timeField(moduleName, column)) {
      return `
        <label class="swal-label">${column}</label>
        <input class="swal2-input" id="crud-field-${index}" type="time" value="${escapeHtml(value)}" placeholder="${column}">`;
    }
    return `
      <label class="swal-label">${column}</label>
      <input class="swal2-input" id="crud-field-${index}" value="${escapeHtml(value)}" placeholder="${column}">`;
  }).join('');

  Swal.fire({
    title: id === null ? `Nuevo registro` : `Editar registro`,
    html: `<div class="crud-form-grid">
      ${inputs}
      <small>Formulario operativo. Los cambios se reflejan al guardar.</small>
    </div>`,
    showCancelButton: true,
    confirmButtonText: 'Guardar',
    confirmButtonColor: '#788260',
    cancelButtonText: 'Cancelar',
    preConfirm: () => {
      const values = columns.map((column, index) => {
        if (timeRangeField(moduleName, column)) {
          const start = document.getElementById(`crud-field-${index}-start`)?.value || '';
          const end = document.getElementById(`crud-field-${index}-end`)?.value || '';
          return start && end ? `${start} - ${end}` : '';
        }
        return document.getElementById(`crud-field-${index}`)?.value.trim() || '';
      });
      if (values.some((value, index) => !value && !isOptionalField(moduleName, columns[index]))) {
        Swal.showValidationMessage('Completa todos los campos del registro.');
        return false;
      }
      return values;
    }
  }).then(async result => {
    if (result.isConfirmed) {
      const response = await MirrorGlamAPI.save(moduleName, { id, dbId, row: result.value });
      if (!response.ok) {
        Swal.fire('No se pudo guardar', response.message || 'Revisa la conexion o las reglas de la base de datos.', 'error');
        return;
      }
      const table = document.querySelector(`[data-module-table="${moduleName}"]`);
      if (table) renderTable(table, response.data);
      const message = response.data?.source === 'mysql'
        ? 'Registro guardado correctamente.'
        : 'Registro guardado en modo local. Revisa la conexion.';
      Swal.fire('Guardado', message, 'success');
    }
  });
}

function statusOptions(moduleName, current = '') {
  const map = {
    clientes: ['Activo', 'Inactivo', 'Pendiente'],
    empleados: ['Activo', 'Inactivo', 'Vacaciones', 'Pendiente'],
    servicios: ['Activo', 'Inactivo'],
    categorias: ['Activo', 'Inactivo'],
    citas: ['Pendiente', 'Confirmada', 'Activo', 'Completado', 'Cancelado', 'No asistio'],
    pagos: ['Pendiente', 'Pagado', 'Rechazado', 'Reembolsado'],
    facturacion: ['Pendiente', 'Pagada', 'Anulada'],
    cancelaciones: ['Cancelado'],
    inventario: ['Activo', 'Inactivo', 'Bajo stock', 'Agotado'],
    horarios: ['Activo', 'Inactivo'],
    usuarios: ['Activo', 'Inactivo', 'Bloqueado', 'Pendiente'],
    notificaciones: ['Pendiente', 'Enviado', 'Fallido', 'Cancelado'],
    reportes: ['Pendiente', 'Procesado', 'Fallido', 'Cancelado'],
    auditoria: ['Activo']
  };
  const options = map[moduleName] || ['Activo', 'Pendiente', 'Completado', 'Cancelado'];
  return current && !options.includes(current) ? [current, ...options] : options;
}

function defaultFieldValue(moduleName, column) {
  const key = column.toLowerCase();
  if (key.includes('ultima visita')) return '';
  if (key.includes('ultimo acceso')) return 'Sin acceso';
  if (key === 'horario') return '9:00 AM - 5:00 PM';
  if (moduleName === 'citas' && key === 'fecha') return localDateTimeValue();
  if (key === 'fecha') return new Date().toISOString().slice(0, 10);
  if (key === 'periodo') return new Date().toISOString().slice(0, 7);
  if (key === 'total' || key === 'precio') return 'RD$ 0';
  if (key === 'stock' || key === 'servicios') return '0';
  if (key === 'dia') return 'Lunes';
  if (key === 'entrada') return '09:00';
  if (moduleName === 'pagos' && key === 'metodo') return 'Efectivo';
  return '';
}

function dateFieldType(moduleName, column) {
  const key = column.toLowerCase();
  if (moduleName === 'clientes' && key.includes('ultima visita')) return 'date';
  if (moduleName === 'citas' && key === 'fecha') return 'datetime-local';
  if (moduleName === 'cancelaciones' && key === 'cita') return null;
  if (moduleName === 'facturacion' && key === 'fecha') return 'date';
  if (moduleName === 'usuarios' && key.includes('ultimo acceso')) return 'date';
  if (moduleName === 'reportes' && key === 'periodo') return 'month';
  if (moduleName === 'notificaciones' && key === 'fecha') return 'datetime-local';
  return null;
}

function normalizeFieldValue(value, moduleName, column) {
  const type = dateFieldType(moduleName, column);
  if (!type) return value;
  if (!value || value === 'Sin visitas' || value === 'Sin acceso') return '';
  const text = String(value).replace(' ', 'T');
  if (type === 'date') return text.slice(0, 10);
  if (type === 'datetime-local') return text.length >= 16 ? text.slice(0, 16) : '';
  if (type === 'month') {
    const month = monthToInputValue(String(value));
    return month || new Date().toISOString().slice(0, 7);
  }
  return value;
}

function isOptionalField(moduleName, column) {
  const key = column.toLowerCase();
  return (moduleName === 'clientes' && key.includes('ultima visita')) ||
    (moduleName === 'usuarios' && key.includes('ultimo acceso'));
}

function timeRangeField(moduleName, column) {
  return moduleName === 'empleados' && column.toLowerCase() === 'horario';
}

function timeField(moduleName, column) {
  return moduleName === 'horarios' && column.toLowerCase() === 'entrada';
}

function splitTimeRange(value) {
  const [start, end] = String(value || '').split(/\s*-\s*/);
  return [toTimeInputValue(start) || '09:00', toTimeInputValue(end) || '17:00'];
}

function toTimeInputValue(value) {
  const text = String(value || '').trim();
  if (/^\d{2}:\d{2}$/.test(text)) return text;
  const match = text.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/i);
  if (!match) return '';
  let hours = Number(match[1]);
  const minutes = match[2];
  const suffix = match[3]?.toUpperCase();
  if (suffix === 'PM' && hours < 12) hours += 12;
  if (suffix === 'AM' && hours === 12) hours = 0;
  return `${String(hours).padStart(2, '0')}:${minutes}`;
}

function monthToInputValue(value) {
  if (/^\d{4}-\d{2}$/.test(value)) return value;
  const months = {
    enero: '01', febrero: '02', marzo: '03', abril: '04', mayo: '05', junio: '06',
    julio: '07', agosto: '08', septiembre: '09', octubre: '10', noviembre: '11', diciembre: '12'
  };
  const parts = value.toLowerCase().split(/\s+/);
  const year = parts.find(part => /^\d{4}$/.test(part));
  const monthName = parts.find(part => months[part]);
  return year && monthName ? `${year}-${months[monthName]}` : '';
}

function localDateTimeValue() {
  const date = new Date();
  date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
  return date.toISOString().slice(0, 16);
}

function confirmDelete(moduleName, id, dbId = null) {
  Swal.fire({
    title: 'Confirmar eliminacion',
    text: 'Esta accion eliminara el registro del modulo seleccionado.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#9d4f50',
    confirmButtonText: 'Eliminar',
    cancelButtonText: 'Cancelar'
  }).then(async result => {
    if (result.isConfirmed) {
      const response = await MirrorGlamAPI.remove(moduleName, dbId || id);
      if (!response.ok) {
        Swal.fire('No se pudo eliminar', response.message || 'Puede que el registro tenga datos relacionados.', 'error');
        return;
      }
      const table = document.querySelector(`[data-module-table="${moduleName}"]`);
      if (table) renderTable(table, response.data);
      const message = response.data?.source === 'mysql'
        ? 'Registro eliminado correctamente.'
        : 'Registro eliminado en modo local. Revisa la conexion.';
      Swal.fire('Eliminado', message, 'success');
    }
  });
}

async function initCharts() {
  const dashboardData = document.querySelector('[data-dashboard-metric]')
    ? await MirrorGlamAPI.dashboard()
    : null;

  if (dashboardData) renderDashboard(dashboardData);

  const sales = document.getElementById('salesChart');
  if (sales) new Chart(sales, { type: 'line', data: { labels: dashboardData?.sales.labels?.length ? dashboardData.sales.labels : ['Sin datos'], datasets: [{ label: 'Ingresos', data: dashboardData?.sales.data?.length ? dashboardData.sales.data : [0], borderColor: '#788260', backgroundColor: 'rgba(120,130,96,.16)', fill: true, tension: .42 }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
  const services = document.getElementById('servicesChart');
  if (services) new Chart(services, { type: 'doughnut', data: { labels: dashboardData?.services.labels?.length ? dashboardData.services.labels : ['Sin datos'], datasets: [{ data: dashboardData?.services.data?.length ? dashboardData.services.data : [1], backgroundColor: ['#788260','#d9a7a2','#d8c5ad','#9a735d', '#596044'] }] }, options: { plugins: { legend: { position: 'bottom' } } } });
}

function renderDashboard(data) {
  Object.entries(data.metrics || {}).forEach(([key, metric]) => {
    setText(`[data-dashboard-metric="${key}"]`, metric.value);
    setText(`[data-dashboard-note="${key}"]`, metric.note);
  });

  const agenda = document.querySelector('[data-dashboard-agenda]');
  if (!agenda) return;
  const rows = data.agenda?.rows || [];
  agenda.innerHTML = rows.length
    ? rows.map(row => `<tr>${row.map((cell, index) => `<td>${index === row.length - 1 ? statusPill(cell) : cell}</td>`).join('')}</tr>`).join('')
    : '<tr><td colspan="5">No hay citas registradas para hoy.</td></tr>';
}

function renderEmployeeCharts(citas) {
  const employeeArea = document.querySelector('[data-employee-area]');
  const sales = document.getElementById('salesChart');
  const services = document.getElementById('servicesChart');
  if (!employeeArea || (!sales && !services)) return;

  const byDate = citas.reduce((acc, row) => {
    const date = String(row[0]).slice(0, 10);
    acc[date] = (acc[date] || 0) + 1;
    return acc;
  }, {});
  const byService = citas.reduce((acc, row) => {
    const service = row[2] || 'Servicio';
    acc[service] = (acc[service] || 0) + 1;
    return acc;
  }, {});

  if (sales) {
    if (window.employeeSalesChart) window.employeeSalesChart.destroy();
    window.employeeSalesChart = new Chart(sales, {
      type: 'line',
      data: {
        labels: Object.keys(byDate).length ? Object.keys(byDate) : ['Sin citas'],
        datasets: [{ label: 'Citas', data: Object.values(byDate).length ? Object.values(byDate) : [0], borderColor: '#788260', backgroundColor: 'rgba(120,130,96,.16)', fill: true, tension: .42 }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  if (services) {
    if (window.employeeServicesChart) window.employeeServicesChart.destroy();
    window.employeeServicesChart = new Chart(services, {
      type: 'doughnut',
      data: {
        labels: Object.keys(byService).length ? Object.keys(byService) : ['Sin servicios'],
        datasets: [{ data: Object.values(byService).length ? Object.values(byService) : [1], backgroundColor: ['#788260', '#d9a7a2', '#d8c5ad', '#9a735d'] }]
      },
      options: { plugins: { legend: { position: 'bottom' } } }
    });
  }
}

function initReports() {
  const button = document.getElementById('generateReportBtn');
  if (!button) return;

  button.addEventListener('click', () => generateReport({ notify: true }));
  document.querySelectorAll('[data-report-export]').forEach(exportButton => {
    exportButton.addEventListener('click', () => exportGeneratedReport(exportButton.dataset.reportExport));
  });
  generateReport({ notify: false });
}

function initEmployeeContext() {
  const employeeArea = document.querySelector('[data-employee-area]');
  if (!employeeArea) return;

  const selector = document.getElementById('activeEmployeeSelect');
  const active = MirrorGlamAPI.getActiveEmployee();
  if (selector) {
    selector.value = active.id;
    selector.addEventListener('change', () => {
      MirrorGlamAPI.setActiveEmployee(selector.value);
      applyEmployeeContext();
    });
  }

  applyEmployeeContext();
}

async function applyEmployeeContext() {
  const employee = MirrorGlamAPI.getActiveEmployee();
  const employees = MirrorGlamAPI.getEmployees();
  const citasData = await MirrorGlamAPI.list('citas');
  const employeeIndex = citasData.columns.indexOf('Empleado');
  const citas = employeeIndex === -1 ? [] : citasData.rows.filter(row => row[employeeIndex] === employee.name);
  const completed = citas.filter(row => row[row.length - 1] === 'Completado').length;
  const productivity = citas.length ? `${Math.round((completed / citas.length) * 100)}%` : '0%';

  const selector = document.getElementById('activeEmployeeSelect');
  if (selector) {
    Array.from(selector.options).forEach(option => {
      if (employees[option.value]) option.textContent = employees[option.value].name;
    });
  }

  document.querySelectorAll('[data-employee-name-heading]').forEach(el => { el.textContent = employee.name; });
  setText('[data-employee-field="initials"]', employee.initials);
  setText('[data-employee-field="name"]', employee.name);
  setText('[data-employee-field="role"]', employee.role);
  setText('[data-employee-field="rating"]', employee.rating);
  setText('[data-employee-field="productivity"]', productivity);
  setValue('[data-employee-input="name"]', employee.name);
  setValue('[data-employee-input="phone"]', employee.phone);
  setValue('[data-employee-input="specialty"]', employee.specialty);
  setValue('[data-employee-input="email"]', employee.email);
  setText('[data-employee-metric="assigned"]', String(citas.length));
  setText('[data-employee-metric="completed"]', String(completed));
  setText('[data-employee-metric="rating"]', employee.rating);
  setText('[data-employee-metric="productivity"]', productivity);

  refreshEmployeeTables();
  renderEmployeeCharts(citas);
}

function setText(selector, value) {
  document.querySelectorAll(selector).forEach(el => { el.textContent = value; });
}

function setValue(selector, value) {
  document.querySelectorAll(selector).forEach(el => { el.value = value; });
}

async function generateReport(options = { notify: true }) {
  const type = document.getElementById('reportType')?.value || 'ingresos';
  const from = document.getElementById('reportFrom')?.value || '2026-05-01';
  const to = document.getElementById('reportTo')?.value || '2026-05-15';
  const report = await buildReportData(type, from, to);

  document.getElementById('reportTitle').textContent = report.title;
  document.getElementById('reportRange').textContent = `${from} al ${to}`;
  document.getElementById('reportMetrics').innerHTML = report.metrics.map(metric => `
    <article class="metric">
      <i class="fa-solid ${metric.icon}"></i>
      <span>${metric.label}</span>
      <strong>${metric.value}</strong>
      <small>${metric.note}</small>
    </article>`).join('');
  document.getElementById('reportInsights').innerHTML = report.insights.map(item => `<li>${item}</li>`).join('');

  const table = document.getElementById('generatedReportTable');
  table.querySelector('thead').innerHTML = `<tr>${report.columns.map(col => `<th>${col}</th>`).join('')}</tr>`;
  table.querySelector('tbody').innerHTML = report.rows.map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('');

  renderReportChart(report);
  if (options.notify) {
    Swal.fire({ title: 'Reporte generado', text: `${report.title} listo para exportar.`, icon: 'success', timer: 1300, showConfirmButton: false });
  }
}

async function buildReportData(type, from, to) {
  const dynamicReport = await buildDynamicReportData(type, from, to);
  if (dynamicReport) return dynamicReport;

  const store = MirrorGlamAPI.getStore();
  const reports = {
    ingresos: {
      title: 'Ingresos',
      columns: ['Fecha', 'Factura', 'Cliente', 'Metodo', 'Total'],
      rows: [
        ['2026-05-15', 'F-1042', 'Camila Ruiz', 'Tarjeta', 'RD$ 1,600'],
        ['2026-05-15', 'F-1043', 'Laura Mendez', 'Transferencia', 'RD$ 4,200'],
        ['2026-05-14', 'F-1044', 'Sofia Alba', 'Efectivo', 'RD$ 3,100'],
        ['2026-05-13', 'F-1045', 'Valentina Perez', 'Tarjeta', 'RD$ 2,800']
      ],
      chart: [16, 42, 31, 28, 48, 64],
      labels: ['10 May', '11 May', '12 May', '13 May', '14 May', '15 May'],
      metrics: [
        ['Total generado', 'RD$ 11,700', 'fa-sack-dollar', 'Periodo seleccionado'],
        ['Facturas', '4', 'fa-file-invoice-dollar', 'Emitidas'],
        ['Pago principal', 'Tarjeta', 'fa-credit-card', 'Mayor uso'],
        ['Promedio', 'RD$ 2,925', 'fa-chart-line', 'Por factura']
      ],
      insights: ['Los ingresos se mantienen en crecimiento durante el periodo.', 'El metodo tarjeta concentra la mayor actividad.', 'Este reporte se alimenta desde facturas y pagos.']
    },
    servicios: {
      title: 'Servicios',
      columns: ['Servicio', 'Categoria', 'Solicitudes', 'Ingreso estimado'],
      rows: [['Peinado glam', 'Cabello', '34', 'RD$ 95,200'], ['Manicure gel', 'U\u00f1as', '26', 'RD$ 41,600'], ['Maquillaje social', 'Makeup', '24', 'RD$ 100,800'], ['Tratamiento capilar', 'Cabello', '16', 'RD$ 49,600']],
      chart: [34, 26, 24, 16],
      labels: ['Cabello', 'U\u00f1as', 'Makeup', 'Spa'],
      metrics: [['Servicio top', 'Peinado glam', 'fa-wand-magic-sparkles', '34 solicitudes'], ['Categorias', '4', 'fa-tags', 'Activas'], ['Ingresos', 'RD$ 287K', 'fa-sack-dollar', 'Estimado'], ['Demanda', '+18%', 'fa-chart-line', 'Vs periodo anterior']],
      insights: ['Peinado glam lidera el volumen de servicios.', 'U\u00f1as conserva una demanda estable y recurrente.', 'Conectar con servicios, categorias_servicios y detalle_citas.']
    },
    empleados: {
      title: 'Empleados',
      columns: ['Empleado', 'Especialidad', 'Servicios', 'Productividad'],
      rows: [['Lia Santos', 'Maquillaje', '42', '94%'], ['Nora Diaz', 'U\u00f1as', '38', '89%'], ['Eva Rojas', 'Cabello', '31', '84%']],
      chart: [94, 89, 84],
      labels: ['Lia', 'Nora', 'Eva'],
      metrics: [['Promedio', '89%', 'fa-chart-simple', 'Productividad'], ['Servicios', '111', 'fa-check', 'Realizados'], ['Top empleado', 'Lia Santos', 'fa-star', 'Mayor rendimiento'], ['Equipo activo', '3', 'fa-id-badge', 'Especialistas']],
      insights: ['La productividad general esta por encima del objetivo operativo.', 'Lia Santos lidera el periodo.', 'Filtrar por empleado autenticado en panel de empleados.']
    },
    clientes: {
      title: 'Clientes frecuentes',
      columns: ['Cliente', 'Visitas', 'Ultimo servicio', 'Valor estimado'],
      rows: [['Valentina Perez', '8', 'Peinado glam', 'RD$ 22,400'], ['Camila Ruiz', '6', 'Manicure gel', 'RD$ 9,600'], ['Laura Mendez', '5', 'Maquillaje social', 'RD$ 21,000']],
      chart: [8, 6, 5],
      labels: ['Valentina', 'Camila', 'Laura'],
      metrics: [['Clientes top', '3', 'fa-heart', 'Frecuentes'], ['Visitas', '19', 'fa-user-check', 'Acumuladas'], ['Ticket alto', 'Valentina', 'fa-crown', 'Mayor recurrencia'], ['Retencion', '76%', 'fa-rotate', 'Estimada']],
      insights: ['Los clientes frecuentes concentran oportunidades de membresia.', 'Conviene activar recordatorios personalizados.', 'Conectar con clientes, citas y facturas.']
    },
    inventario: {
      title: 'Inventario',
      columns: ['Producto', 'Stock', 'Proveedor', 'Estado'],
      rows: store.inventario.rows,
      chart: store.inventario.rows.map(row => Number(row[1]) || 0),
      labels: store.inventario.rows.map(row => row[0]),
      metrics: [['Productos', String(store.inventario.rows.length), 'fa-boxes-stacked', 'Registrados'], ['Stock bajo', String(store.inventario.rows.filter(row => row[3] === 'Bajo stock').length), 'fa-triangle-exclamation', 'Alertas'], ['Proveedor top', 'Glam Supply', 'fa-truck', 'Simulado'], ['Valor', 'RD$ 310K', 'fa-sack-dollar', 'Estimado']],
      insights: ['Los productos en bajo stock deben priorizarse para compra.', 'Este reporte resume el inventario y sus proveedores.', 'Puedes activar recordatorios automaticos para stock bajo.']
    },
    citas: {
      title: 'Citas',
      columns: store.citas.columns,
      rows: store.citas.rows,
      chart: [store.citas.rows.length, 2, 1, 0],
      labels: ['Total', 'Pendientes', 'Activas', 'Canceladas'],
      metrics: [['Citas', String(store.citas.rows.length), 'fa-calendar-check', 'Periodo'], ['Pendientes', String(store.citas.rows.filter(row => row[row.length - 1] === 'Pendiente').length), 'fa-clock', 'Por confirmar'], ['Completadas', String(store.citas.rows.filter(row => row[row.length - 1] === 'Completado').length), 'fa-check', 'Finalizadas'], ['Activas', String(store.citas.rows.filter(row => row[row.length - 1] === 'Activo').length), 'fa-calendar-day', 'En agenda']],
      insights: ['La agenda puede filtrarse por empleado y servicio.', 'Conectar con citas, detalle_citas, empleados y horarios.', 'Las cancelaciones deben cruzarse con la tabla cancelaciones.']
    }
  };

  const report = reports[type] || reports.ingresos;
  const rows = filterReportRowsByDate(report.rows, from, to);
  return {
    ...report,
    rows,
    metrics: report.metrics.map(([label, value, icon, note]) => ({ label, value, icon, note }))
  };
}

async function buildDynamicReportData(type, from, to) {
  const resourceMap = {
    ingresos: 'facturacion',
    servicios: 'servicios',
    empleados: 'empleados',
    clientes: 'clientes',
    inventario: 'inventario',
    citas: 'citas'
  };
  const resource = resourceMap[type];
  if (!resource) return null;

  const data = await MirrorGlamAPI.list(resource);
  if (!data.rows?.length) return null;

  const rows = filterRowsByReportDate(data.rows, data.columns, from, to);
  const sourceNote = data.source === 'mysql' ? 'Base de datos' : 'Datos locales';
  const titles = {
    ingresos: 'Ingresos',
    servicios: 'Servicios',
    empleados: 'Empleados',
    clientes: 'Clientes frecuentes',
    inventario: 'Inventario',
    citas: 'Citas'
  };

  if (type === 'ingresos') {
    const total = rows.reduce((sum, row) => sum + parseMoney(row[2]), 0);
    return normalizeReport({
      title: titles[type],
      columns: data.columns,
      rows,
      labels: rows.map(row => row[1]),
      chart: rows.map(row => parseMoney(row[2])),
      metrics: [
        ['Total generado', formatMoney(total), 'fa-sack-dollar', sourceNote],
        ['Facturas', String(rows.length), 'fa-file-invoice-dollar', 'Periodo seleccionado'],
        ['Pagadas', String(rows.filter(row => row[row.length - 1] === 'Pagada').length), 'fa-circle-check', 'Estado'],
        ['Promedio', formatMoney(rows.length ? total / rows.length : 0), 'fa-chart-line', 'Por factura']
      ],
      insights: ['Reporte construido con facturacion desde la API.', 'Exporta este resultado a PDF o Excel.', 'Puedes cambiar el rango de fechas para recalcular.']
    });
  }

  if (type === 'servicios') {
    const grouped = groupCount(rows, 1);
    return normalizeReport({
      title: titles[type],
      columns: data.columns,
      rows,
      labels: grouped.labels,
      chart: grouped.data,
      metrics: [
        ['Servicios', String(rows.length), 'fa-wand-magic-sparkles', sourceNote],
        ['Categorias', String(new Set(rows.map(row => row[1])).size), 'fa-tags', 'Activas'],
        ['Activos', String(rows.filter(row => row[row.length - 1] === 'Activo').length), 'fa-circle-check', 'Disponibles'],
        ['Precio mayor', formatMoney(Math.max(...rows.map(row => parseMoney(row[2])), 0)), 'fa-sack-dollar', 'Catalogo']
      ],
      insights: ['Grafica agrupada por categoria de servicio.', 'Los precios salen del catalogo actual.', 'Conecta detalle_citas para demanda historica avanzada.']
    });
  }

  if (type === 'empleados') {
    const grouped = groupCount(rows, 1);
    return normalizeReport({
      title: titles[type],
      columns: data.columns,
      rows,
      labels: grouped.labels,
      chart: grouped.data,
      metrics: [
        ['Empleados', String(rows.length), 'fa-id-badge', sourceNote],
        ['Activos', String(rows.filter(row => row[row.length - 1] === 'Activo').length), 'fa-user-check', 'Disponibles'],
        ['Especialidades', String(grouped.labels.length), 'fa-sparkles', 'Registradas'],
        ['Horarios', String(rows.filter(row => row[2] !== 'Sin horario').length), 'fa-clock', 'Configurados']
      ],
      insights: ['Grafica agrupada por especialidad.', 'El panel de empleados filtra por el perfil seleccionado.', 'Puedes ampliar este reporte con servicios completados por empleado.']
    });
  }

  if (type === 'clientes') {
    const grouped = groupCount(rows, rows[0]?.length - 1);
    return normalizeReport({
      title: titles[type],
      columns: data.columns,
      rows,
      labels: grouped.labels,
      chart: grouped.data,
      metrics: [
        ['Clientes', String(rows.length), 'fa-users', sourceNote],
        ['Activos', String(rows.filter(row => row[row.length - 1] === 'Activo').length), 'fa-heart', 'Vigentes'],
        ['Con visitas', String(rows.filter(row => row[2] !== 'Sin visitas').length), 'fa-calendar-check', 'Historial'],
        ['Estados', String(grouped.labels.length), 'fa-chart-pie', 'Segmentos']
      ],
      insights: ['Grafica agrupada por estado del cliente.', 'La ultima visita sale de la tabla citas.', 'Este reporte ayuda a detectar clientes recurrentes.']
    });
  }

  if (type === 'inventario') {
    return normalizeReport({
      title: titles[type],
      columns: data.columns,
      rows,
      labels: rows.map(row => row[0]),
      chart: rows.map(row => Number(row[1]) || 0),
      metrics: [
        ['Productos', String(rows.length), 'fa-boxes-stacked', sourceNote],
        ['Stock bajo', String(rows.filter(row => row[row.length - 1] === 'Bajo stock').length), 'fa-triangle-exclamation', 'Alertas'],
        ['Stock total', String(rows.reduce((sum, row) => sum + (Number(row[1]) || 0), 0)), 'fa-box', 'Unidades'],
        ['Proveedores', String(new Set(rows.map(row => row[2])).size), 'fa-truck', 'Registrados']
      ],
      insights: ['Grafica por stock actual de cada producto.', 'Los estados salen del inventario registrado.', 'Puedes activar recordatorios automaticos para stock bajo.']
    });
  }

  const grouped = groupCount(rows, rows[0]?.length - 1);
  return normalizeReport({
    title: titles[type],
    columns: data.columns,
    rows,
    labels: grouped.labels,
    chart: grouped.data,
    metrics: [
      ['Citas', String(rows.length), 'fa-calendar-check', sourceNote],
      ['Pendientes', String(rows.filter(row => row[row.length - 1] === 'Pendiente').length), 'fa-clock', 'Por confirmar'],
      ['Completadas', String(rows.filter(row => row[row.length - 1] === 'Completado').length), 'fa-check', 'Finalizadas'],
      ['Estados', String(grouped.labels.length), 'fa-chart-pie', 'Agenda']
    ],
    insights: ['Grafica agrupada por estado de cita.', 'Los datos salen del modulo citas.', 'Puedes filtrar por fechas para revisar agenda historica.']
  });
}

function normalizeReport(report) {
  return {
    ...report,
    rows: report.rows.length ? report.rows : [['Sin datos']],
    labels: report.labels.length ? report.labels : ['Sin datos'],
    chart: report.chart.length ? report.chart : [0],
    metrics: report.metrics.map(([label, value, icon, note]) => ({ label, value, icon, note }))
  };
}

function filterRowsByReportDate(rows, columns, from, to) {
  const dateIndex = columns.findIndex(column => ['fecha', 'ultima visita'].includes(column.toLowerCase()));
  if (dateIndex === -1) return rows;
  return rows.filter(row => {
    const date = String(row[dateIndex]).slice(0, 10);
    if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) return true;
    return date >= from && date <= to;
  });
}

function groupCount(rows, index) {
  const grouped = rows.reduce((acc, row) => {
    const key = row[index] || 'Sin datos';
    acc[key] = (acc[key] || 0) + 1;
    return acc;
  }, {});
  return { labels: Object.keys(grouped), data: Object.values(grouped) };
}

function parseMoney(value) {
  return Number(String(value).replace(/[^0-9.]/g, '')) || 0;
}

function formatMoney(value) {
  return `RD$ ${Math.round(value).toLocaleString('en-US')}`;
}

function filterReportRowsByDate(rows, from, to) {
  return rows.filter(row => {
    const date = String(row[0]).slice(0, 10);
    if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) return true;
    return date >= from && date <= to;
  });
}

function renderReportChart(report) {
  const canvas = document.getElementById('reportChart');
  if (!canvas) return;
  if (window.reportChartInstance) window.reportChartInstance.destroy();
  window.reportChartInstance = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: report.labels,
      datasets: [{ label: report.title, data: report.chart, backgroundColor: ['#788260', '#d9a7a2', '#d8c5ad', '#9a735d', '#596044', '#e6c7b9'], borderRadius: 10 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });
}

function exportGeneratedReport(type) {
  const table = document.getElementById('generatedReportTable');
  const title = document.getElementById('reportTitle')?.textContent || 'reporte';
  const fileBase = `mirror-glam-${slugify(title)}-${new Date().toISOString().slice(0, 10)}`;
  if (type === 'pdf') {
    window.print();
    return;
  }
  if (type === 'excel') {
    exportTableToExcel(table, `${fileBase}.xls`);
    return;
  }
  exportTableToCsv(table, `${fileBase}.csv`);
}

function exportTableToCsv(table, filename) {
  const rows = Array.from(table.querySelectorAll('tr')).map(row =>
    Array.from(row.children)
      .slice(0, table.dataset.moduleTable ? -1 : undefined)
      .map(cell => `"${cell.textContent.trim().replaceAll('"', '""')}"`)
      .join(',')
  );
  downloadFile(rows.join('\n'), filename, 'text/csv;charset=utf-8;');
}

function exportTableToExcel(table, filename) {
  const html = `\uFEFF<html><head><meta charset="utf-8"></head><body>${table.outerHTML}</body></html>`;
  downloadFile(html, filename, 'application/vnd.ms-excel;charset=utf-8;');
}

function downloadFile(content, filename, mimeType) {
  const blob = new Blob([content], { type: mimeType });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

function slugify(text) {
  return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
}

function renderCalendar() {
  const calendar = document.getElementById('bookingCalendar');
  if (!calendar) return;
  calendar.innerHTML = Array.from({ length: 21 }, (_, i) => {
    const day = i + 1;
    return `<button class="calendar-day" data-booking-step="day"><strong>${day}</strong><br><small>${i % 3 === 0 ? '4 horarios' : 'Disponible'}</small></button>`;
  }).join('');
}

function capitalize(text) { return text.charAt(0).toUpperCase() + text.slice(1); }

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}
