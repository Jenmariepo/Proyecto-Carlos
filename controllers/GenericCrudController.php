<?php
declare(strict_types=1);

final class GenericCrudController
{
    private PDO $db;
    private string $resource;

    public function __construct(string $resource)
    {
        $this->db = db();
        $this->resource = $resource;
    }

    public function handle(string $method): array
    {
        return match ($method) {
            'GET' => $this->index(),
            'POST' => $this->storeOrUpdate($this->payload()),
            'PUT', 'PATCH' => $this->storeOrUpdate($this->payload()),
            'DELETE' => $this->destroy((int)($_GET['id'] ?? 0)),
            default => ['ok' => false, 'message' => 'Metodo no permitido'],
        };
    }

    private function payload(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function index(): array
    {
        return match ($this->resource) {
            'clientes' => $this->query('SELECT id, nombre, telefono, COALESCE((SELECT MAX(fecha) FROM citas WHERE cliente_id = clientes.id), "Sin visitas") ultima_visita, estado FROM clientes ORDER BY id DESC', ['Cliente','Telefono','Ultima visita','Estado']),
            'empleados' => $this->query('SELECT id, nombre, especialidad, COALESCE((SELECT CONCAT(TIME_FORMAT(MIN(hora_inicio), "%l:%i %p"), " - ", TIME_FORMAT(MAX(hora_fin), "%l:%i %p")) FROM horarios WHERE empleado_id = empleados.id), "Sin horario") horario, estado FROM empleados ORDER BY id DESC', ['Empleado','Especialidad','Horario','Estado']),
            'servicios' => $this->query('SELECT s.id, s.nombre, c.nombre categoria, CONCAT("RD$ ", FORMAT(s.precio, 0)) precio, s.estado FROM servicios s JOIN categorias_servicios c ON c.id = s.categoria_id ORDER BY s.id DESC', ['Servicio','Categoria','Precio','Estado']),
            'categorias' => $this->query('SELECT c.id, c.nombre categoria, COUNT(s.id) servicios, COALESCE(c.color, "Nude") color, c.estado FROM categorias_servicios c LEFT JOIN servicios s ON s.categoria_id = c.id GROUP BY c.id, c.nombre, c.color, c.estado ORDER BY c.id DESC', ['Categoria','Servicios','Color','Estado']),
            'citas' => $this->query('SELECT c.id, CONCAT(c.fecha, " ", TIME_FORMAT(c.hora_inicio, "%l:%i %p")) fecha, cl.nombre cliente, COALESCE((SELECT s.nombre FROM detalle_citas dc JOIN servicios s ON s.id = dc.servicio_id WHERE dc.cita_id = c.id LIMIT 1), "Sin servicio") servicio, e.nombre empleado, c.estado FROM citas c JOIN clientes cl ON cl.id = c.cliente_id JOIN empleados e ON e.id = c.empleado_id ORDER BY c.fecha DESC, c.hora_inicio DESC', ['Fecha','Cliente','Servicio','Empleado','Estado']),
            'pagos' => $this->query('SELECT p.id, f.numero factura, cl.nombre cliente, p.metodo, p.estado FROM pagos p JOIN facturas f ON f.id = p.factura_id JOIN clientes cl ON cl.id = f.cliente_id ORDER BY p.id DESC', ['Factura','Cliente','Metodo','Estado']),
            'facturacion' => $this->query('SELECT f.id, f.numero factura, DATE(f.fecha_emision) fecha, CONCAT("RD$ ", FORMAT(f.total, 0)) total, f.estado FROM facturas f ORDER BY f.id DESC', ['Factura','Fecha','Total','Estado']),
            'cancelaciones' => $this->query('SELECT ca.id, CONCAT("C-", c.id) cita, cl.nombre cliente, ca.motivo, "Cancelado" estado FROM cancelaciones ca JOIN citas c ON c.id = ca.cita_id JOIN clientes cl ON cl.id = c.cliente_id ORDER BY ca.id DESC', ['Cita','Cliente','Motivo','Estado']),
            'inventario' => $this->query('SELECT id, nombre, stock_actual, proveedor, estado FROM inventario ORDER BY id DESC', ['Producto','Stock','Proveedor','Estado']),
            'horarios' => $this->query('SELECT h.id, e.nombre empleado, CASE h.dia_semana WHEN 1 THEN "Lunes" WHEN 2 THEN "Martes" WHEN 3 THEN "Miercoles" WHEN 4 THEN "Jueves" WHEN 5 THEN "Viernes" WHEN 6 THEN "Sabado" ELSE "Domingo" END dia, TIME_FORMAT(h.hora_inicio, "%l:%i %p") entrada, h.estado FROM horarios h JOIN empleados e ON e.id = h.empleado_id ORDER BY h.id DESC', ['Empleado','Dia','Entrada','Estado']),
            'usuarios' => $this->query('SELECT u.id, u.email usuario, r.nombre rol, COALESCE(DATE(u.ultimo_acceso), "Sin acceso") ultimo_acceso, u.estado FROM usuarios u JOIN roles r ON r.id = u.rol_id ORDER BY u.id DESC', ['Usuario','Rol','Ultimo acceso','Estado']),
            'auditoria' => $this->query('SELECT a.id, a.accion, COALESCE(u.email, "Sistema") usuario, a.modulo, "Activo" estado FROM auditoria a LEFT JOIN usuarios u ON u.id = a.usuario_id ORDER BY a.id DESC LIMIT 100', ['Accion','Usuario','Modulo','Estado']),
            'notificaciones' => $this->query('SELECT id, DATE_FORMAT(programado_para, "%Y-%m-%d %H:%i") fecha, COALESCE((SELECT nombre FROM empleados WHERE empleados.id = recordatorios.empleado_id), "Admin") para, mensaje, estado FROM recordatorios ORDER BY programado_para DESC', ['Fecha','Para','Mensaje','Estado']),
            'reportes' => $this->query('SELECT id, evento reporte, entidad periodo, estado resultado, estado FROM automatizacion_eventos ORDER BY id DESC LIMIT 50', ['Reporte','Periodo','Resultado','Estado']),
            'dashboard' => $this->dashboard(),
            default => ['ok' => false, 'message' => 'Recurso no configurado'],
        };
    }

    private function dashboard(): array
    {
        // CONSULTAS MYSQL PARA DASHBOARD:
        // Conectar aqui vistas SQL, procedimientos almacenados o indices optimizados si la BD crece.
        $citasHoy = (int)$this->db->query('SELECT COUNT(*) FROM citas WHERE fecha = CURDATE()')->fetchColumn();
        $ingresosDia = (float)$this->db->query('SELECT COALESCE(SUM(total), 0) FROM facturas WHERE DATE(fecha_emision) = CURDATE() AND estado <> "Anulada"')->fetchColumn();

        $servicioTop = $this->db->query(
            'SELECT s.nombre, COUNT(*) total
             FROM detalle_citas dc
             JOIN servicios s ON s.id = dc.servicio_id
             JOIN citas c ON c.id = dc.cita_id
             WHERE c.estado <> "Cancelado"
             GROUP BY s.id, s.nombre
             ORDER BY total DESC
             LIMIT 1'
        )->fetch() ?: ['nombre' => 'Sin datos', 'total' => 0];

        $productividad = (float)$this->db->query(
            'SELECT COALESCE(ROUND(SUM(estado = "Completado") / NULLIF(COUNT(*), 0) * 100), 0) FROM citas'
        )->fetchColumn();

        $weeklyRows = $this->db->query(
            'SELECT DATE(fecha_emision) fecha, SUM(total) total
             FROM facturas
             WHERE fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
               AND estado <> "Anulada"
             GROUP BY DATE(fecha_emision)
             ORDER BY fecha'
        )->fetchAll();

        $serviceRows = $this->db->query(
            'SELECT cs.nombre categoria, COUNT(*) total
             FROM detalle_citas dc
             JOIN servicios s ON s.id = dc.servicio_id
             JOIN categorias_servicios cs ON cs.id = s.categoria_id
             JOIN citas c ON c.id = dc.cita_id
             WHERE c.estado <> "Cancelado"
             GROUP BY cs.id, cs.nombre
             ORDER BY total DESC
             LIMIT 5'
        )->fetchAll();

        $agendaRows = $this->db->query(
            'SELECT TIME_FORMAT(c.hora_inicio, "%l:%i %p") hora,
                    cl.nombre cliente,
                    COALESCE((SELECT s.nombre FROM detalle_citas dc JOIN servicios s ON s.id = dc.servicio_id WHERE dc.cita_id = c.id LIMIT 1), "Sin servicio") servicio,
                    e.nombre empleado,
                    c.estado
             FROM citas c
             JOIN clientes cl ON cl.id = c.cliente_id
             JOIN empleados e ON e.id = c.empleado_id
             ORDER BY c.fecha DESC, c.hora_inicio DESC
             LIMIT 8'
        )->fetchAll();

        return [
            'ok' => true,
            'data' => [
                'metrics' => [
                    'citasHoy' => ['value' => (string)$citasHoy, 'note' => 'Agenda del dia'],
                    'ingresosDia' => ['value' => 'RD$ ' . number_format($ingresosDia, 0), 'note' => 'Facturas de hoy'],
                    'servicioTop' => ['value' => $servicioTop['nombre'], 'note' => (int)$servicioTop['total'] . ' ' . ((int)$servicioTop['total'] === 1 ? 'solicitud' : 'solicitudes')],
                    'productividad' => ['value' => (int)$productividad . '%', 'note' => 'Citas completadas'],
                ],
                'sales' => [
                    'labels' => array_map(fn ($row) => date('d M', strtotime($row['fecha'])), $weeklyRows),
                    'data' => array_map(fn ($row) => round((float)$row['total'], 2), $weeklyRows),
                ],
                'services' => [
                    'labels' => array_map(fn ($row) => $row['categoria'], $serviceRows),
                    'data' => array_map(fn ($row) => (int)$row['total'], $serviceRows),
                ],
                'agenda' => [
                    'columns' => ['Hora', 'Cliente', 'Servicio', 'Empleado', 'Estado'],
                    'rows' => array_map(fn ($row) => array_values($row), $agendaRows),
                ],
            ],
        ];
    }

    private function query(string $sql, array $columns): array
    {
        $rows = $this->db->query($sql)->fetchAll();
        $ids = array_map(fn ($row) => (int)$row['id'], $rows);
        $dataRows = array_map(function ($row) {
            unset($row['id']);
            return array_values($row);
        }, $rows);

        return ['ok' => true, 'data' => ['columns' => $columns, 'rows' => $dataRows, 'ids' => $ids]];
    }

    private function storeOrUpdate(array $payload): array
    {
        $row = $payload['row'] ?? [];
        $id = isset($payload['dbId']) ? (int)$payload['dbId'] : null;
        $extra = $payload['extra'] ?? [];

        match ($this->resource) {
            'clientes' => $this->saveCliente($row, $id),
            'empleados' => $this->saveEmpleado($row, $id),
            'servicios' => $this->saveServicio($row, $id),
            'categorias' => $this->saveCategoria($row, $id),
            'citas' => $this->saveCita($row, $id, $extra),
            'pagos' => $this->savePago($row, $id),
            'facturacion' => $this->saveFactura($row, $id),
            'cancelaciones' => $this->saveCancelacion($row, $id),
            'inventario' => $this->saveInventario($row, $id),
            'horarios' => $this->saveHorario($row, $id),
            'usuarios' => $this->saveUsuario($row, $id),
            'auditoria' => $this->saveAuditoria($row, $id),
            'notificaciones' => $this->saveNotificacion($row, $id),
            'reportes' => $this->saveReporte($row, $id),
            default => null,
        };

        return $this->index();
    }

    private function destroy(int $id): array
    {
        if ($id <= 0) return ['ok' => false, 'message' => 'ID invalido'];

        $table = match ($this->resource) {
            'clientes' => 'clientes',
            'empleados' => 'empleados',
            'servicios' => 'servicios',
            'categorias' => 'categorias_servicios',
            'citas' => 'citas',
            'pagos' => 'pagos',
            'facturacion' => 'facturas',
            'cancelaciones' => 'cancelaciones',
            'inventario' => 'inventario',
            'horarios' => 'horarios',
            'usuarios' => 'usuarios',
            'auditoria' => 'auditoria',
            'notificaciones' => 'recordatorios',
            'reportes' => 'automatizacion_eventos',
            default => null,
        };

        if (!$table) return ['ok' => false, 'message' => 'Eliminacion no configurada para este modulo'];

        $this->deleteDependencies($id);

        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $this->index();
    }

    private function deleteDependencies(int $id): void
    {
        match ($this->resource) {
            'clientes' => $this->deleteClienteDependencies($id),
            'empleados' => $this->deleteEmpleadoDependencies($id),
            'servicios' => $this->db->prepare('DELETE FROM detalle_citas WHERE servicio_id=:id')->execute(['id' => $id]),
            'categorias' => $this->deleteCategoriaDependencies($id),
            'citas' => $this->deleteCitaDependencies($id),
            'facturacion' => $this->db->prepare('DELETE FROM pagos WHERE factura_id=:id')->execute(['id' => $id]),
            'usuarios' => $this->clearUsuarioReferences($id),
            'reportes' => $this->db->prepare('DELETE FROM automatizacion_logs WHERE evento_id=:id')->execute(['id' => $id]),
            default => null,
        };
    }

    private function deleteClienteDependencies(int $clienteId): void
    {
        $citas = $this->ids('SELECT id FROM citas WHERE cliente_id = :id', $clienteId);
        foreach ($citas as $citaId) {
            $this->deleteCitaDependencies($citaId);
        }
        $this->db->prepare('DELETE FROM pagos WHERE factura_id IN (SELECT id FROM facturas WHERE cliente_id=:id)')->execute(['id' => $clienteId]);
        $this->db->prepare('DELETE FROM facturas WHERE cliente_id=:id')->execute(['id' => $clienteId]);
        $this->db->prepare('DELETE FROM recordatorios WHERE cliente_id=:id')->execute(['id' => $clienteId]);
        $this->db->prepare('DELETE FROM citas WHERE cliente_id=:id')->execute(['id' => $clienteId]);
    }

    private function deleteEmpleadoDependencies(int $empleadoId): void
    {
        $citas = $this->ids('SELECT id FROM citas WHERE empleado_id = :id', $empleadoId);
        foreach ($citas as $citaId) {
            $this->deleteCitaDependencies($citaId);
        }
        $this->db->prepare('DELETE FROM horarios WHERE empleado_id=:id')->execute(['id' => $empleadoId]);
        $this->db->prepare('DELETE FROM recordatorios WHERE empleado_id=:id')->execute(['id' => $empleadoId]);
        $this->db->prepare('DELETE FROM citas WHERE empleado_id=:id')->execute(['id' => $empleadoId]);
    }

    private function deleteCategoriaDependencies(int $categoriaId): void
    {
        $servicios = $this->ids('SELECT id FROM servicios WHERE categoria_id = :id', $categoriaId);
        foreach ($servicios as $servicioId) {
            $this->db->prepare('DELETE FROM detalle_citas WHERE servicio_id=:id')->execute(['id' => $servicioId]);
        }
        $this->db->prepare('DELETE FROM servicios WHERE categoria_id=:id')->execute(['id' => $categoriaId]);
    }

    private function deleteCitaDependencies(int $citaId): void
    {
        $this->db->prepare('DELETE FROM pagos WHERE factura_id IN (SELECT id FROM facturas WHERE cita_id=:id)')->execute(['id' => $citaId]);
        $this->db->prepare('DELETE FROM facturas WHERE cita_id=:id')->execute(['id' => $citaId]);
        $this->db->prepare('DELETE FROM cancelaciones WHERE cita_id=:id')->execute(['id' => $citaId]);
        $this->db->prepare('DELETE FROM recordatorios WHERE cita_id=:id')->execute(['id' => $citaId]);
        $this->db->prepare('DELETE FROM detalle_citas WHERE cita_id=:id')->execute(['id' => $citaId]);
    }

    private function clearUsuarioReferences(int $usuarioId): void
    {
        $this->db->prepare('UPDATE empleados SET usuario_id=NULL WHERE usuario_id=:id')->execute(['id' => $usuarioId]);
        $this->db->prepare('UPDATE citas SET creado_por=NULL WHERE creado_por=:id')->execute(['id' => $usuarioId]);
        $this->db->prepare('UPDATE pagos SET creado_por=NULL WHERE creado_por=:id')->execute(['id' => $usuarioId]);
        $this->db->prepare('UPDATE cancelaciones SET cancelado_por=NULL WHERE cancelado_por=:id')->execute(['id' => $usuarioId]);
        $this->db->prepare('UPDATE auditoria SET usuario_id=NULL WHERE usuario_id=:id')->execute(['id' => $usuarioId]);
    }

    private function ids(string $sql, int $id): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function saveCliente(array $row, ?int $id): void
    {
        [$nombre, $telefono, $ultimaVisita, $estado] = array_pad($row, 4, '');
        $sql = $id
            ? 'UPDATE clientes SET nombre=:nombre, telefono=:telefono, estado=:estado WHERE id=:id'
            : 'INSERT INTO clientes (nombre, telefono, estado) VALUES (:nombre, :telefono, :estado)';
        $params = ['nombre' => $nombre, 'telefono' => $telefono, 'estado' => $this->validEnum($estado, ['Activo','Inactivo','Pendiente'], 'Activo')];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
        $clienteId = $id ?: (int)$this->db->lastInsertId();
        $this->saveClienteUltimaVisita($clienteId, $ultimaVisita);
    }

    private function saveClienteUltimaVisita(int $clienteId, string $ultimaVisita): void
    {
        $fecha = $this->parseOptionalDate($ultimaVisita);
        if (!$fecha) {
            return;
        }

        $stmt = $this->db->prepare('SELECT id FROM citas WHERE cliente_id=:cliente_id ORDER BY fecha DESC, hora_inicio DESC LIMIT 1');
        $stmt->execute(['cliente_id' => $clienteId]);
        $citaId = $stmt->fetchColumn();

        if ($citaId) {
            $this->db->prepare('UPDATE citas SET fecha=:fecha, estado="Completado" WHERE id=:id')
                ->execute(['fecha' => $fecha, 'id' => (int)$citaId]);
            return;
        }

        $empleadoId = (int)$this->db->query('SELECT id FROM empleados ORDER BY id LIMIT 1')->fetchColumn();
        if (!$empleadoId) {
            $empleadoId = $this->findOrCreateEmpleado('Empleado general');
        }

        $this->db->prepare('INSERT INTO citas (cliente_id, empleado_id, fecha, hora_inicio, estado, origen) VALUES (:cliente_id, :empleado_id, :fecha, "09:00:00", "Completado", "Admin")')
            ->execute(['cliente_id' => $clienteId, 'empleado_id' => $empleadoId, 'fecha' => $fecha]);
    }

    private function saveEmpleado(array $row, ?int $id): void
    {
        [$nombre, $especialidad, $horario, $estado, $telefono, $email] = array_pad($row, 6, '');
        $sql = $id
            ? 'UPDATE empleados SET nombre=:nombre, especialidad=:especialidad, telefono=COALESCE(NULLIF(:telefono, ""), telefono), email=COALESCE(NULLIF(:email, ""), email), estado=:estado WHERE id=:id'
            : 'INSERT INTO empleados (nombre, especialidad, telefono, email, estado) VALUES (:nombre, :especialidad, NULLIF(:telefono, ""), NULLIF(:email, ""), :estado)';
        $params = [
            'nombre' => $nombre,
            'especialidad' => $especialidad,
            'telefono' => $telefono,
            'email' => $email,
            'estado' => $this->validEnum($estado, ['Activo','Inactivo','Vacaciones','Pendiente'], 'Activo'),
        ];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
        $empleadoId = $id ?: (int)$this->db->lastInsertId();
        $this->saveEmpleadoHorario($empleadoId, $horario);
    }

    private function saveEmpleadoHorario(int $empleadoId, string $horario): void
    {
        $horario = trim($horario);
        if ($empleadoId <= 0 || $horario === '' || strtolower($horario) === 'sin horario') {
            return;
        }

        [$inicioTexto, $finTexto] = array_pad(preg_split('/\s*-\s*/', $horario, 2), 2, '');
        $inicio = $this->parseTime($inicioTexto ?: '09:00 AM');
        $fin = $this->parseTime($finTexto ?: '05:00 PM');

        // El formulario de empleados maneja un horario general. Se guarda como lunes a viernes.
        $this->db->prepare('DELETE FROM horarios WHERE empleado_id=:empleado_id')->execute(['empleado_id' => $empleadoId]);
        $stmt = $this->db->prepare('INSERT INTO horarios (empleado_id, dia_semana, hora_inicio, hora_fin, estado) VALUES (:empleado_id, :dia_semana, :hora_inicio, :hora_fin, "Activo")');
        for ($dia = 1; $dia <= 5; $dia++) {
            $stmt->execute([
                'empleado_id' => $empleadoId,
                'dia_semana' => $dia,
                'hora_inicio' => $inicio,
                'hora_fin' => $fin,
            ]);
        }
    }

    private function saveServicio(array $row, ?int $id): void
    {
        [$nombre, $categoria, $precioTexto, $estado] = array_pad($row, 4, '');
        $categoriaId = $this->findOrCreateCategoria($categoria ?: 'General');
        $precio = (float)preg_replace('/[^0-9.]/', '', $precioTexto);
        $sql = $id
            ? 'UPDATE servicios SET nombre=:nombre, categoria_id=:categoria_id, precio=:precio, estado=:estado WHERE id=:id'
            : 'INSERT INTO servicios (nombre, categoria_id, precio, estado) VALUES (:nombre, :categoria_id, :precio, :estado)';
        $params = ['nombre' => $nombre, 'categoria_id' => $categoriaId, 'precio' => $precio, 'estado' => $this->validEnum($estado, ['Activo','Inactivo'], 'Activo')];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
    }

    private function saveCategoria(array $row, ?int $id): void
    {
        [$nombre, $_servicios, $color, $estado] = array_pad($row, 4, '');
        $sql = $id
            ? 'UPDATE categorias_servicios SET nombre=:nombre, color=:color, estado=:estado WHERE id=:id'
            : 'INSERT INTO categorias_servicios (nombre, color, estado) VALUES (:nombre, :color, :estado)';
        $params = ['nombre' => $nombre, 'color' => $color ?: null, 'estado' => $this->validEnum($estado, ['Activo','Inactivo'], 'Activo')];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
    }

    private function saveInventario(array $row, ?int $id): void
    {
        [$nombre, $stock, $proveedor, $estado] = array_pad($row, 4, '');
        $sql = $id
            ? 'UPDATE inventario SET nombre=:nombre, stock_actual=:stock, proveedor=:proveedor, estado=:estado WHERE id=:id'
            : 'INSERT INTO inventario (nombre, stock_actual, proveedor, estado) VALUES (:nombre, :stock, :proveedor, :estado)';
        $params = ['nombre' => $nombre, 'stock' => (int)$stock, 'proveedor' => $proveedor, 'estado' => $this->validEnum($estado, ['Activo','Inactivo','Bajo stock','Agotado'], 'Activo')];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
    }

    private function saveHorario(array $row, ?int $id): void
    {
        [$empleado, $dia, $entrada, $estado] = array_pad($row, 4, '');
        $empleadoId = $this->findOrCreateEmpleado($empleado ?: 'Empleado general');
        $diaSemana = $this->parseDay($dia);
        $horaInicio = $this->parseTime($entrada ?: '09:00 AM');
        $horaFin = date('H:i:s', strtotime($horaInicio . ' +8 hours'));
        $sql = $id
            ? 'UPDATE horarios SET empleado_id=:empleado_id, dia_semana=:dia_semana, hora_inicio=:hora_inicio, hora_fin=:hora_fin, estado=:estado WHERE id=:id'
            : 'INSERT INTO horarios (empleado_id, dia_semana, hora_inicio, hora_fin, estado) VALUES (:empleado_id, :dia_semana, :hora_inicio, :hora_fin, :estado)';
        $params = [
            'empleado_id' => $empleadoId,
            'dia_semana' => $diaSemana,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'estado' => $this->validEnum($estado, ['Activo','Inactivo'], 'Activo'),
        ];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
    }

    private function saveFactura(array $row, ?int $id): void
    {
        [$numero, $fecha, $totalTexto, $estado] = array_pad($row, 4, '');
        $clienteId = $this->findOrCreateCliente('Cliente general');
        $total = (float)preg_replace('/[^0-9.]/', '', $totalTexto);
        $estado = $this->validEnum($estado, ['Pendiente','Pagada','Anulada'], 'Pendiente');
        $sql = $id
            ? 'UPDATE facturas SET numero=:numero, fecha_emision=:fecha, subtotal=:total, total=:total, estado=:estado WHERE id=:id'
            : 'INSERT INTO facturas (cliente_id, numero, fecha_emision, subtotal, total, estado) VALUES (:cliente_id, :numero, :fecha, :total, :total, :estado)';
        $params = ['numero' => $numero ?: $this->nextInvoiceNumber(), 'fecha' => $this->parseDateTime($fecha), 'total' => $total, 'estado' => $estado];
        if ($id) {
            $params['id'] = $id;
        } else {
            $params['cliente_id'] = $clienteId;
        }
        $this->db->prepare($sql)->execute($params);
    }

    private function savePago(array $row, ?int $id): void
    {
        [$factura, $cliente, $metodo, $estado] = array_pad($row, 4, '');
        $facturaId = $this->findOrCreateFactura($factura ?: $this->nextInvoiceNumber(), $cliente ?: 'Cliente general');
        $monto = (float)$this->db->query("SELECT total FROM facturas WHERE id = {$facturaId}")->fetchColumn();
        $sql = $id
            ? 'UPDATE pagos SET factura_id=:factura_id, metodo=:metodo, monto=:monto, estado=:estado WHERE id=:id'
            : 'INSERT INTO pagos (factura_id, metodo, monto, estado) VALUES (:factura_id, :metodo, :monto, :estado)';
        $params = [
            'factura_id' => $facturaId,
            'metodo' => $this->validEnum($metodo, ['Efectivo','Tarjeta','Transferencia','Deposito','Otro'], 'Otro'),
            'monto' => $monto,
            'estado' => $this->validEnum($estado, ['Pendiente','Pagado','Rechazado','Reembolsado'], 'Pagado'),
        ];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
    }

    private function saveCancelacion(array $row, ?int $id): void
    {
        [$citaTexto, $cliente, $motivo, $_estado] = array_pad($row, 4, '');
        $citaId = $this->findOrCreateCitaForCancelacion($citaTexto, $cliente ?: 'Cliente general');
        $sql = $id
            ? 'UPDATE cancelaciones SET cita_id=:cita_id, motivo=:motivo WHERE id=:id'
            : 'INSERT INTO cancelaciones (cita_id, motivo, tipo_actor) VALUES (:cita_id, :motivo, "Admin")';
        $params = ['cita_id' => $citaId, 'motivo' => $motivo ?: 'Cancelacion administrativa'];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
        $this->db->prepare('UPDATE citas SET estado="Cancelado" WHERE id=:id')->execute(['id' => $citaId]);
    }

    private function saveUsuario(array $row, ?int $id): void
    {
        [$email, $rol, $ultimoAcceso, $estado] = array_pad($row, 4, '');
        $rolId = $this->findOrCreateRole($rol ?: 'Empleado');
        $nombre = ucfirst(strtok($email ?: 'usuario', '@'));
        $sql = $id
            ? 'UPDATE usuarios SET email=:email, nombre=:nombre, rol_id=:rol_id, ultimo_acceso=:ultimo_acceso, estado=:estado WHERE id=:id'
            : 'INSERT INTO usuarios (email, nombre, rol_id, password_hash, ultimo_acceso, estado) VALUES (:email, :nombre, :rol_id, :password_hash, :ultimo_acceso, :estado)';
        $params = [
            'email' => $email,
            'nombre' => $nombre,
            'rol_id' => $rolId,
            'ultimo_acceso' => $ultimoAcceso && $ultimoAcceso !== 'Sin acceso' ? $this->parseDateTime($ultimoAcceso) : null,
            'estado' => $this->validEnum($estado, ['Activo','Inactivo','Bloqueado','Pendiente'], 'Activo'),
        ];
        if ($id) {
            $params['id'] = $id;
        } else {
            $params['password_hash'] = password_hash('123456', PASSWORD_DEFAULT);
        }
        $this->db->prepare($sql)->execute($params);
    }

    private function saveAuditoria(array $row, ?int $id): void
    {
        [$accion, $usuario, $modulo, $estado] = array_pad($row, 4, '');
        $usuarioId = $this->findUsuarioId($usuario);
        $sql = $id
            ? 'UPDATE auditoria SET accion=:accion, usuario_id=:usuario_id, modulo=:modulo, descripcion=:descripcion WHERE id=:id'
            : 'INSERT INTO auditoria (accion, usuario_id, modulo, descripcion) VALUES (:accion, :usuario_id, :modulo, :descripcion)';
        $params = ['accion' => $accion, 'usuario_id' => $usuarioId, 'modulo' => $modulo, 'descripcion' => $estado ?: 'Activo'];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
    }

    private function saveNotificacion(array $row, ?int $id): void
    {
        [$fecha, $para, $mensaje, $estado] = array_pad($row, 4, '');
        $empleadoId = $this->findEmpleadoId($para);
        $sql = $id
            ? 'UPDATE recordatorios SET empleado_id=:empleado_id, destinatario=:destinatario, mensaje=:mensaje, programado_para=:programado_para, estado=:estado WHERE id=:id'
            : 'INSERT INTO recordatorios (empleado_id, tipo, canal, destinatario, mensaje, programado_para, estado) VALUES (:empleado_id, "Sistema", "Sistema", :destinatario, :mensaje, :programado_para, :estado)';
        $params = [
            'empleado_id' => $empleadoId,
            'destinatario' => $para ?: 'Admin',
            'mensaje' => $mensaje ?: 'Notificacion Mirror Glam',
            'programado_para' => $this->parseDateTime($fecha ?: date('Y-m-d H:i:s')),
            'estado' => $this->validEnum($estado, ['Pendiente','Enviado','Fallido','Cancelado'], 'Pendiente'),
        ];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
    }

    private function saveReporte(array $row, ?int $id): void
    {
        [$reporte, $periodo, $resultado, $estado] = array_pad($row, 4, '');
        $sql = $id
            ? 'UPDATE automatizacion_eventos SET evento=:evento, entidad=:entidad, payload=:payload, estado=:estado WHERE id=:id'
            : 'INSERT INTO automatizacion_eventos (evento, entidad, entidad_id, payload, estado) VALUES (:evento, :entidad, 0, :payload, :estado)';
        $params = [
            'evento' => $reporte ?: 'Reporte',
            'entidad' => $periodo ?: 'Periodo',
            'payload' => json_encode(['resultado' => $resultado], JSON_UNESCAPED_UNICODE),
            'estado' => $this->validEnum($estado, ['Pendiente','Procesado','Fallido','Cancelado'], 'Pendiente'),
        ];
        if ($id) $params['id'] = $id;
        $this->db->prepare($sql)->execute($params);
    }

    private function saveCita(array $row, ?int $id, array $extra = []): void
    {
        [$fechaHora, $cliente, $servicio, $empleado, $estado] = array_pad($row, 5, '');
        [$fecha, $hora] = $this->splitFechaHora($fechaHora);
        $clienteId = $this->findOrCreateCliente($cliente, (string)($extra['telefono'] ?? ''));
        $empleadoId = $this->findOrCreateEmpleado($empleado);
        $servicioId = $this->findOrCreateServicio($servicio);

        if ($id) {
            $stmt = $this->db->prepare('UPDATE citas SET cliente_id=:cliente_id, empleado_id=:empleado_id, fecha=:fecha, hora_inicio=:hora, estado=:estado WHERE id=:id');
            $stmt->execute(['cliente_id' => $clienteId, 'empleado_id' => $empleadoId, 'fecha' => $fecha, 'hora' => $hora, 'estado' => $this->validEnum($estado, ['Pendiente','Confirmada','Activo','Completado','Cancelado','No asistio'], 'Pendiente'), 'id' => $id]);
        } else {
            $stmt = $this->db->prepare('INSERT INTO citas (cliente_id, empleado_id, fecha, hora_inicio, estado) VALUES (:cliente_id, :empleado_id, :fecha, :hora, :estado)');
            $stmt->execute(['cliente_id' => $clienteId, 'empleado_id' => $empleadoId, 'fecha' => $fecha, 'hora' => $hora, 'estado' => $this->validEnum($estado, ['Pendiente','Confirmada','Activo','Completado','Cancelado','No asistio'], 'Pendiente')]);
            $id = (int)$this->db->lastInsertId();
        }

        $this->db->prepare('DELETE FROM detalle_citas WHERE cita_id=:cita_id')->execute(['cita_id' => $id]);
        $precio = (float)$this->db->query("SELECT precio FROM servicios WHERE id = {$servicioId}")->fetchColumn();
        $this->db->prepare('INSERT INTO detalle_citas (cita_id, servicio_id, precio) VALUES (:cita_id, :servicio_id, :precio)')
            ->execute(['cita_id' => $id, 'servicio_id' => $servicioId, 'precio' => $precio]);
    }

    private function findOrCreateCliente(string $nombre, string $telefono = ''): int
    {
        $stmt = $this->db->prepare('SELECT id FROM clientes WHERE nombre=:nombre LIMIT 1');
        $stmt->execute(['nombre' => $nombre]);
        $id = $stmt->fetchColumn();
        if ($id) {
            if (trim($telefono) !== '') {
                $this->db->prepare('UPDATE clientes SET telefono=:telefono WHERE id=:id AND (telefono="" OR telefono="Sin telefono" OR telefono IS NULL)')
                    ->execute(['telefono' => $telefono, 'id' => (int)$id]);
            }
            return (int)$id;
        }
        $this->db->prepare('INSERT INTO clientes (nombre, telefono) VALUES (:nombre, :telefono)')
            ->execute(['nombre' => $nombre, 'telefono' => trim($telefono) !== '' ? $telefono : 'Sin telefono']);
        return (int)$this->db->lastInsertId();
    }

    private function findOrCreateEmpleado(string $nombre): int
    {
        $stmt = $this->db->prepare('SELECT id FROM empleados WHERE nombre=:nombre LIMIT 1');
        $stmt->execute(['nombre' => $nombre]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
        $this->db->prepare('INSERT INTO empleados (nombre, especialidad) VALUES (:nombre, "General")')->execute(['nombre' => $nombre]);
        return (int)$this->db->lastInsertId();
    }

    private function findOrCreateServicio(string $nombre): int
    {
        $stmt = $this->db->prepare('SELECT id FROM servicios WHERE nombre=:nombre LIMIT 1');
        $stmt->execute(['nombre' => $nombre]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
        $categoriaId = $this->findOrCreateCategoria('General');
        $this->db->prepare('INSERT INTO servicios (categoria_id, nombre, precio) VALUES (:categoria_id, :nombre, 0)')->execute(['categoria_id' => $categoriaId, 'nombre' => $nombre]);
        return (int)$this->db->lastInsertId();
    }

    private function findOrCreateCategoria(string $nombre): int
    {
        $stmt = $this->db->prepare('SELECT id FROM categorias_servicios WHERE nombre=:nombre LIMIT 1');
        $stmt->execute(['nombre' => $nombre]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
        $this->db->prepare('INSERT INTO categorias_servicios (nombre) VALUES (:nombre)')->execute(['nombre' => $nombre]);
        return (int)$this->db->lastInsertId();
    }

    private function findOrCreateFactura(string $numero, string $cliente): int
    {
        $stmt = $this->db->prepare('SELECT id FROM facturas WHERE numero=:numero LIMIT 1');
        $stmt->execute(['numero' => $numero]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
        $clienteId = $this->findOrCreateCliente($cliente);
        $this->db->prepare('INSERT INTO facturas (cliente_id, numero, subtotal, total, estado) VALUES (:cliente_id, :numero, 0, 0, "Pendiente")')
            ->execute(['cliente_id' => $clienteId, 'numero' => $numero]);
        return (int)$this->db->lastInsertId();
    }

    private function findOrCreateRole(string $nombre): int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE nombre=:nombre LIMIT 1');
        $stmt->execute(['nombre' => $nombre]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
        $this->db->prepare('INSERT INTO roles (nombre, descripcion) VALUES (:nombre, "Rol creado desde CRUD")')->execute(['nombre' => $nombre]);
        return (int)$this->db->lastInsertId();
    }

    private function findUsuarioId(string $email): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM usuarios WHERE email=:email OR nombre=:email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    private function findEmpleadoId(string $nombre): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM empleados WHERE nombre=:nombre LIMIT 1');
        $stmt->execute(['nombre' => $nombre]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    private function findOrCreateCitaForCancelacion(string $citaTexto, string $cliente): int
    {
        if (preg_match('/(\d+)/', $citaTexto, $matches)) {
            $stmt = $this->db->prepare('SELECT id FROM citas WHERE id=:id LIMIT 1');
            $stmt->execute(['id' => (int)$matches[1]]);
            $id = $stmt->fetchColumn();
            if ($id) return (int)$id;
        }

        $clienteId = $this->findOrCreateCliente($cliente);
        $empleadoId = (int)$this->db->query('SELECT id FROM empleados ORDER BY id LIMIT 1')->fetchColumn();
        if (!$empleadoId) $empleadoId = $this->findOrCreateEmpleado('Empleado general');
        $this->db->prepare('INSERT INTO citas (cliente_id, empleado_id, fecha, hora_inicio, estado, origen) VALUES (:cliente_id, :empleado_id, CURDATE(), "09:00:00", "Cancelado", "Admin")')
            ->execute(['cliente_id' => $clienteId, 'empleado_id' => $empleadoId]);
        return (int)$this->db->lastInsertId();
    }

    private function nextInvoiceNumber(): string
    {
        return 'F-' . date('YmdHis');
    }

    private function parseDateTime(string $value): string
    {
        $time = strtotime($value);
        return date('Y-m-d H:i:s', $time ?: time());
    }

    private function parseOptionalDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || strtolower($value) === 'sin visitas') {
            return null;
        }

        $time = strtotime($value);
        return $time ? date('Y-m-d', $time) : null;
    }

    private function parseTime(string $value): string
    {
        $time = strtotime($value);
        return date('H:i:s', $time ?: strtotime('09:00'));
    }

    private function parseDay(string $value): int
    {
        $text = strtolower($value);
        return match (true) {
            str_contains($text, 'martes') => 2,
            str_contains($text, 'miercoles'), str_contains($text, 'miércoles') => 3,
            str_contains($text, 'jueves') => 4,
            str_contains($text, 'viernes') => 5,
            str_contains($text, 'sabado'), str_contains($text, 'sábado') => 6,
            str_contains($text, 'domingo') => 7,
            default => 1,
        };
    }

    private function validEnum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function splitFechaHora(string $fechaHora): array
    {
        $parts = explode(' ', trim($fechaHora), 2);
        $fecha = $parts[0] ?: date('Y-m-d');
        $horaTexto = $parts[1] ?? '09:00 AM';
        $hora = date('H:i:s', strtotime($horaTexto));
        return [$fecha, $hora];
    }
}
