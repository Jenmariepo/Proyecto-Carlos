window.MirrorGlamData = {
  metrics: [
    ['Citas de hoy', '24', 'fa-calendar-check', '+12% vs ayer'],
    ['Ingresos del dia', 'RD$ 86,450', 'fa-sack-dollar', '+18% semanal'],
    ['Clientes frecuentes', '142', 'fa-heart', 'Top 30 dias'],
    ['Productividad', '91%', 'fa-chart-line', 'Equipo activo'],
  ],
  agenda: [
    ['9:00 AM', 'Valentina Perez', 'Peinado glam', 'Lia Santos', 'Pendiente'],
    ['10:30 AM', 'Camila Ruiz', 'Manicure gel', 'Nora Diaz', 'Completado'],
    ['12:00 PM', 'Laura Mendez', 'Maquillaje social', 'Lia Santos', 'Activo'],
    ['2:00 PM', 'Sofia Alba', 'Tratamiento capilar', 'Eva Rojas', 'Pendiente'],
  ],
  modules: {
    clientes: {
      columns: ['Cliente', 'Telefono', 'Ultima visita', 'Estado'],
      rows: [
        ['Valentina Perez', '809-555-0141', '2026-05-14', 'Activo'],
        ['Camila Ruiz', '829-555-0122', '2026-05-12', 'Activo'],
        ['Laura Mendez', '849-555-0118', '2026-05-09', 'Pendiente'],
      ]
    },
    empleados: {
      columns: ['Empleado', 'Especialidad', 'Horario', 'Estado'],
      rows: [
        ['Lia Santos', 'Maquillaje', '9:00 AM - 5:00 PM', 'Activo'],
        ['Nora Diaz', 'U\u00f1as', '10:00 AM - 6:00 PM', 'Activo'],
        ['Eva Rojas', 'Cabello', '8:00 AM - 4:00 PM', 'Pendiente'],
      ]
    },
    servicios: {
      columns: ['Servicio', 'Categoria', 'Precio', 'Estado'],
      rows: [
        ['Peinado glam', 'Cabello', 'RD$ 2,800', 'Activo'],
        ['Manicure gel', 'U\u00f1as', 'RD$ 1,600', 'Activo'],
        ['Maquillaje social', 'Makeup', 'RD$ 4,200', 'Activo'],
      ]
    },
    categorias: {
      columns: ['Categoria', 'Servicios', 'Color', 'Estado'],
      rows: [['Cabello', '12', 'Oliva', 'Activo'], ['U\u00f1as', '9', 'Rosa palo', 'Activo'], ['Makeup', '7', 'Nude', 'Activo']]
    },
    citas: {
      columns: ['Fecha', 'Cliente', 'Servicio', 'Empleado', 'Estado'],
      rows: [
        ['2026-05-15 9:00', 'Valentina Perez', 'Peinado glam', 'Lia Santos', 'Pendiente'],
        ['2026-05-15 10:30', 'Camila Ruiz', 'Manicure gel', 'Nora Diaz', 'Completado'],
        ['2026-05-16 2:00', 'Sofia Alba', 'Tratamiento capilar', 'Eva Rojas', 'Activo'],
        ['2026-05-16 4:00', 'Laura Mendez', 'Maquillaje social', 'Lia Santos', 'Completado'],
        ['2026-05-17 11:00', 'Mia Leon', 'Manicure gel', 'Nora Diaz', 'Pendiente']
      ]
    },
    pagos: {
      columns: ['Factura', 'Cliente', 'Metodo', 'Estado'],
      rows: [['F-1042', 'Camila Ruiz', 'Tarjeta', 'Pagado'], ['F-1043', 'Laura Mendez', 'Transferencia', 'Pendiente'], ['F-1044', 'Sofia Alba', 'Efectivo', 'Pagado']]
    },
    facturacion: {
      columns: ['Factura', 'Fecha', 'Total', 'Estado'],
      rows: [['F-1042', '2026-05-15', 'RD$ 1,600', 'Pagado'], ['F-1043', '2026-05-15', 'RD$ 4,200', 'Pendiente'], ['F-1044', '2026-05-14', 'RD$ 3,100', 'Pagado']]
    },
    cancelaciones: {
      columns: ['Cita', 'Cliente', 'Motivo', 'Estado'],
      rows: [['C-220', 'Daniela Cruz', 'Cambio de horario', 'Cancelado'], ['C-221', 'Rosa Pena', 'No asistio', 'Cancelado'], ['C-222', 'Mia Leon', 'Solicitud cliente', 'Cancelado']]
    },
    inventario: {
      columns: ['Producto', 'Stock', 'Proveedor', 'Estado'],
      rows: [['Shampoo argan', '8', 'Beauty Pro', 'Bajo stock'], ['Base HD', '24', 'Glam Supply', 'Activo'], ['Esmalte nude', '36', 'Nail House', 'Activo']]
    },
    horarios: {
      columns: ['Empleado', 'Dia', 'Entrada', 'Estado'],
      rows: [['Lia Santos', 'Lunes a viernes', '9:00 AM', 'Activo'], ['Nora Diaz', 'Martes a sabado', '10:00 AM', 'Activo'], ['Eva Rojas', 'Lunes a jueves', '8:00 AM', 'Pendiente']]
    },
    usuarios: {
      columns: ['Usuario', 'Rol', 'Ultimo acceso', 'Estado'],
      rows: [['admin@mirrorglam.do', 'Administrador', '2026-05-15', 'Activo'], ['lia@mirrorglam.do', 'Empleado', '2026-05-15', 'Activo'], ['recepcion@mirrorglam.do', 'Recepcion', '2026-05-14', 'Pendiente']]
    },
    auditoria: {
      columns: ['Accion', 'Usuario', 'Modulo', 'Estado'],
      rows: [['Crear cita', 'recepcion', 'citas', 'Activo'], ['Editar pago', 'admin', 'pagos', 'Activo'], ['Cancelar cita', 'lia', 'cancelaciones', 'Pendiente']]
    },
    reportes: {
      columns: ['Reporte', 'Periodo', 'Resultado', 'Estado'],
      rows: [['Ingresos', 'Mayo 2026', 'RD$ 820,400', 'Activo'], ['Servicios top', '30 dias', 'Peinado glam', 'Activo'], ['Clientes frecuentes', 'Trimestre', '142', 'Activo']]
    },
    notificaciones: {
      columns: ['Fecha', 'Para', 'Mensaje', 'Estado'],
      rows: [
        ['2026-05-20 09:15', 'Admin', 'Hay 3 citas pendientes por confirmar.', 'Pendiente'],
        ['2026-05-20 10:00', 'Lia Santos', 'Valentina Perez llega a las 9:00 AM para peinado glam.', 'Pendiente'],
        ['2026-05-20 10:30', 'Nora Diaz', 'Camila Ruiz completo manicure gel. Registrar cierre del servicio.', 'Completado'],
        ['2026-05-20 11:00', 'Eva Rojas', 'Sofia Alba tiene tratamiento capilar pendiente de preparacion.', 'Activo'],
        ['2026-05-20 12:20', 'Admin', 'Producto Shampoo argan en bajo stock.', 'Bajo stock']
      ]
    }
  }
};
