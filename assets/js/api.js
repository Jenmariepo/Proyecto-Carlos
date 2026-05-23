window.MirrorGlamAPI = {
  baseUrl: '/controllers/api.php',
  storageKey: 'mirror_glam_modules',

  getStore() {
    const saved = localStorage.getItem(this.storageKey);
    if (saved) {
      const store = JSON.parse(saved);
      this.normalizeStoreLabels(store);
      this.setStore(store);
      return store;
    }
    const copy = structuredClone(window.MirrorGlamData.modules);
    localStorage.setItem(this.storageKey, JSON.stringify(copy));
    return copy;
  },

  setStore(store) {
    localStorage.setItem(this.storageKey, JSON.stringify(store));
  },

  normalizeStoreLabels(store) {
    Object.values(store).forEach(moduleData => {
      moduleData.rows = moduleData.rows.map(row => row.map(cell => cell === 'Unas' ? 'U\u00f1as' : cell));
    });
    this.normalizeCitasEmployees(store);
  },

  normalizeCitasEmployees(store) {
    if (!store.citas) return;
    const employeeIndex = store.citas.columns.indexOf('Empleado');
    if (employeeIndex !== -1) return;

    store.citas.columns.splice(store.citas.columns.length - 1, 0, 'Empleado');
    const names = ['Lia Santos', 'Nora Diaz', 'Eva Rojas'];
    store.citas.rows = store.citas.rows.map((row, index) => {
      const copy = [...row];
      copy.splice(copy.length - 1, 0, names[index % names.length]);
      return copy;
    });
  },

  defaultEmployees: {
    lia: {
      id: 'lia',
      name: 'Lia Santos',
      initials: 'LS',
      role: 'Makeup artist',
      specialty: 'Maquillaje',
      phone: '809-555-0101',
      email: 'lia@mirrorglam.do',
      rating: '4.9',
      productivity: '88%'
    },
    nora: {
      id: 'nora',
      name: 'Nora Diaz',
      initials: 'ND',
      role: 'Nail artist',
      specialty: 'U\u00f1as',
      phone: '829-555-0102',
      email: 'nora@mirrorglam.do',
      rating: '4.8',
      productivity: '91%'
    },
    eva: {
      id: 'eva',
      name: 'Eva Rojas',
      initials: 'ER',
      role: 'Hair stylist',
      specialty: 'Cabello',
      phone: '849-555-0103',
      email: 'eva@mirrorglam.do',
      rating: '4.7',
      productivity: '84%'
    }
  },

  getEmployees() {
    const saved = localStorage.getItem('mirror_glam_employees');
    if (saved) return JSON.parse(saved);
    const copy = structuredClone(this.defaultEmployees);
    localStorage.setItem('mirror_glam_employees', JSON.stringify(copy));
    return copy;
  },

  updateEmployee(id, payload) {
    const employees = this.getEmployees();
    employees[id] = { ...employees[id], ...payload };
    employees[id].initials = employees[id].name.split(' ').map(part => part[0]).join('').slice(0, 2).toUpperCase();
    localStorage.setItem('mirror_glam_employees', JSON.stringify(employees));
    return employees[id];
  },

  getActiveEmployee() {
    const id = localStorage.getItem('mirror_glam_active_employee') || 'lia';
    const employees = this.getEmployees();
    return employees[id] || employees.lia;
  },

  setActiveEmployee(id) {
    localStorage.setItem('mirror_glam_active_employee', id);
    return this.getActiveEmployee();
  },

  async list(moduleName) {
    try {
      const response = await fetch(`${this.baseUrl}?resource=${moduleName}`);
      const result = await response.json();
      if (result.ok && result.data) return { ...result.data, source: 'mysql' };
    } catch (error) {
      console.warn('Usando datos locales porque MySQL/API no esta disponible', error);
    }
    return { ...(this.getStore()[moduleName] || { columns: [], rows: [], ids: [] }), source: 'local' };
  },

  async dashboard() {
    try {
      const response = await fetch(`${this.baseUrl}?resource=dashboard`);
      const result = await response.json();
      if (result.ok && result.data) return { ...result.data, source: 'mysql' };
    } catch (error) {
      console.warn('Usando metricas locales porque MySQL/API no esta disponible', error);
    }

    const store = this.getStore();
    return {
      source: 'local',
      metrics: {
        citasHoy: { value: String(store.citas?.rows.length || 0), note: 'Datos locales' },
        ingresosDia: { value: 'RD$ 0', note: 'Conecta facturas' },
        servicioTop: { value: store.servicios?.rows[0]?.[0] || 'Sin datos', note: 'Datos locales' },
        productividad: { value: '0%', note: 'Datos locales' }
      },
      sales: { labels: ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'], data: [0, 0, 0, 0, 0, 0] },
      services: { labels: store.servicios?.rows.map(row => row[1]) || [], data: store.servicios?.rows.map(() => 1) || [] },
      agenda: { rows: store.citas?.rows.slice(0, 8) || [] }
    };
  },

  async save(moduleName, payload) {
    try {
      const response = await fetch(`${this.baseUrl}?resource=${moduleName}`, {
        method: payload.dbId ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const result = await response.json();
      if (result.ok && result.data) return { ...result, data: { ...result.data, source: 'mysql' } };
      return { ok: false, message: result.error || result.message || 'No se pudo guardar el registro.' };
    } catch (error) {
      console.warn('Guardando en modo local porque MySQL/API no esta disponible', error);
    }

    const store = this.getStore();
    const moduleData = store[moduleName];
    if (!moduleData) return { ok: false };

    if (payload.id === null || payload.id === undefined) {
      moduleData.rows.unshift(payload.row);
    } else {
      moduleData.rows[payload.id] = payload.row;
    }

    this.setStore(store);
    return { ok: true, data: { ...moduleData, source: 'local' } };
  },

  async remove(moduleName, id) {
    try {
      const response = await fetch(`${this.baseUrl}?resource=${moduleName}&id=${id}`, { method: 'DELETE' });
      const result = await response.json();
      if (result.ok && result.data) return { ...result, data: { ...result.data, source: 'mysql' } };
      return { ok: false, message: result.error || result.message || 'No se pudo eliminar el registro.' };
    } catch (error) {
      console.warn('Eliminando en modo local porque MySQL/API no esta disponible', error);
    }

    const store = this.getStore();
    const moduleData = store[moduleName];
    if (!moduleData) return { ok: false };
    moduleData.rows.splice(id, 1);
    this.setStore(store);
    return { ok: true, data: { ...moduleData, source: 'local' } };
  },

  async saveEmployeeProfile(previousName, payload) {
    const empleados = await this.list('empleados');
    const index = empleados.rows.findIndex(row => row[0] === previousName);
    const dbId = index >= 0 ? empleados.ids?.[index] : null;
    return this.save('empleados', {
      id: index >= 0 ? index : null,
      dbId,
      row: [payload.name, payload.specialty, empleados.rows[index]?.[2] || 'Sin horario', 'Activo', payload.phone, payload.email]
    });
  },

  async login(credentials) {
    try {
      const response = await fetch(`${this.baseUrl}?resource=login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(credentials)
      });
      return await response.json();
    } catch (error) {
      return { ok: false, message: 'No se pudo iniciar sesion. Revisa la conexion.' };
    }
  },

  async recover(email) {
    try {
      const response = await fetch(`${this.baseUrl}?resource=recover`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });
      return await response.json();
    } catch (error) {
      return { ok: false, message: 'No se pudo registrar la solicitud.' };
    }
  }
};
