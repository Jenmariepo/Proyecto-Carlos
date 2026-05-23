-- Mirror Glam - Base de datos completa
-- Motor: MySQL 8+
-- Charset recomendado: utf8mb4
-- Preparada para PHP/MySQL y futuras automatizaciones con n8n, WhatsApp y correo.

CREATE DATABASE IF NOT EXISTS mirror_glam
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mirror_glam;

SET FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS vw_recordatorios_pendientes;
DROP VIEW IF EXISTS vw_clientes_frecuentes;
DROP VIEW IF EXISTS vw_servicios_mas_solicitados;
DROP VIEW IF EXISTS vw_agenda_diaria;

DROP PROCEDURE IF EXISTS sp_reporte_productividad_empleado;
DROP PROCEDURE IF EXISTS sp_reporte_ingresos;
DROP PROCEDURE IF EXISTS sp_horarios_disponibles;

DROP TRIGGER IF EXISTS trg_cita_cancelada_au;
DROP TRIGGER IF EXISTS trg_cita_recordatorio_ai;
DROP TRIGGER IF EXISTS trg_inventario_estado_bu;
DROP TRIGGER IF EXISTS trg_inventario_estado_bi;

DROP TABLE IF EXISTS automatizacion_logs;
DROP TABLE IF EXISTS automatizacion_eventos;
DROP TABLE IF EXISTS mensajes;
DROP TABLE IF EXISTS recordatorios;
DROP TABLE IF EXISTS auditoria;
DROP TABLE IF EXISTS cancelaciones;
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS facturas;
DROP TABLE IF EXISTS detalle_citas;
DROP TABLE IF EXISTS citas;
DROP TABLE IF EXISTS inventario;
DROP TABLE IF EXISTS horarios;
DROP TABLE IF EXISTS servicios;
DROP TABLE IF EXISTS categorias_servicios;
DROP TABLE IF EXISTS empleados;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE,
  descripcion VARCHAR(180) NULL,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rol_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  telefono VARCHAR(30) NULL,
  foto VARCHAR(255) NULL,
  ultimo_acceso DATETIME NULL,
  estado ENUM('Activo','Inactivo','Bloqueado','Pendiente') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_roles FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE clientes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  email VARCHAR(160) NULL,
  fecha_nacimiento DATE NULL,
  direccion VARCHAR(220) NULL,
  preferencias TEXT NULL,
  alergias TEXT NULL,
  notas TEXT NULL,
  acepta_whatsapp TINYINT(1) NOT NULL DEFAULT 1,
  acepta_email TINYINT(1) NOT NULL DEFAULT 1,
  estado ENUM('Activo','Inactivo','Pendiente') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_clientes_nombre (nombre),
  INDEX idx_clientes_telefono (telefono),
  INDEX idx_clientes_email (email)
) ENGINE=InnoDB;

CREATE TABLE empleados (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NULL,
  nombre VARCHAR(120) NOT NULL,
  telefono VARCHAR(30) NULL,
  email VARCHAR(160) NULL,
  especialidad VARCHAR(120) NOT NULL,
  biografia TEXT NULL,
  fecha_ingreso DATE NULL,
  comision_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  calificacion_promedio DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  estado ENUM('Activo','Inactivo','Vacaciones','Pendiente') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_empleados_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  INDEX idx_empleados_nombre (nombre),
  INDEX idx_empleados_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE categorias_servicios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  descripcion VARCHAR(220) NULL,
  color VARCHAR(30) NULL,
  icono VARCHAR(80) NULL,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE servicios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id BIGINT UNSIGNED NOT NULL,
  nombre VARCHAR(140) NOT NULL,
  descripcion TEXT NULL,
  duracion_minutos INT UNSIGNED NOT NULL DEFAULT 60,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  requiere_deposito TINYINT(1) NOT NULL DEFAULT 0,
  deposito DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_servicios_categorias FOREIGN KEY (categoria_id) REFERENCES categorias_servicios(id),
  INDEX idx_servicios_nombre (nombre),
  INDEX idx_servicios_categoria (categoria_id),
  INDEX idx_servicios_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE horarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empleado_id BIGINT UNSIGNED NOT NULL,
  dia_semana TINYINT UNSIGNED NOT NULL COMMENT '1=Lunes, 7=Domingo',
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  descanso_inicio TIME NULL,
  descanso_fin TIME NULL,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_horarios_empleados FOREIGN KEY (empleado_id) REFERENCES empleados(id),
  INDEX idx_horarios_empleado_dia (empleado_id, dia_semana)
) ENGINE=InnoDB;

CREATE TABLE inventario (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(140) NOT NULL,
  descripcion TEXT NULL,
  sku VARCHAR(80) NULL UNIQUE,
  proveedor VARCHAR(140) NULL,
  stock_actual INT NOT NULL DEFAULT 0,
  stock_minimo INT NOT NULL DEFAULT 5,
  costo_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  precio_venta DECIMAL(12,2) NULL,
  estado ENUM('Activo','Inactivo','Bajo stock','Agotado') NOT NULL DEFAULT 'Activo',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_inventario_nombre (nombre),
  INDEX idx_inventario_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE citas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id BIGINT UNSIGNED NOT NULL,
  empleado_id BIGINT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  hora_inicio TIME NOT NULL,
  hora_fin TIME NULL,
  estado ENUM('Pendiente','Confirmada','Activo','Completado','Cancelado','No asistio') NOT NULL DEFAULT 'Pendiente',
  origen ENUM('Web','Admin','Empleado','WhatsApp','Telefono') NOT NULL DEFAULT 'Web',
  notas TEXT NULL,
  creado_por BIGINT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_citas_clientes FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_citas_empleados FOREIGN KEY (empleado_id) REFERENCES empleados(id),
  CONSTRAINT fk_citas_usuarios FOREIGN KEY (creado_por) REFERENCES usuarios(id),
  INDEX idx_citas_fecha (fecha),
  INDEX idx_citas_empleado_fecha (empleado_id, fecha),
  INDEX idx_citas_cliente (cliente_id),
  INDEX idx_citas_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE detalle_citas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cita_id BIGINT UNSIGNED NOT NULL,
  servicio_id BIGINT UNSIGNED NOT NULL,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  duracion_minutos INT UNSIGNED NOT NULL DEFAULT 60,
  notas TEXT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_detalle_citas_citas FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
  CONSTRAINT fk_detalle_citas_servicios FOREIGN KEY (servicio_id) REFERENCES servicios(id),
  INDEX idx_detalle_cita (cita_id),
  INDEX idx_detalle_servicio (servicio_id)
) ENGINE=InnoDB;

CREATE TABLE facturas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cita_id BIGINT UNSIGNED NULL,
  cliente_id BIGINT UNSIGNED NOT NULL,
  numero VARCHAR(40) NOT NULL UNIQUE,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  impuesto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  estado ENUM('Pendiente','Pagada','Anulada') NOT NULL DEFAULT 'Pendiente',
  fecha_emision DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_facturas_citas FOREIGN KEY (cita_id) REFERENCES citas(id),
  CONSTRAINT fk_facturas_clientes FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  INDEX idx_facturas_cliente (cliente_id),
  INDEX idx_facturas_estado (estado),
  INDEX idx_facturas_fecha (fecha_emision)
) ENGINE=InnoDB;

CREATE TABLE pagos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  factura_id BIGINT UNSIGNED NOT NULL,
  metodo ENUM('Efectivo','Tarjeta','Transferencia','Deposito','Otro') NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  referencia VARCHAR(120) NULL,
  estado ENUM('Pendiente','Pagado','Rechazado','Reembolsado') NOT NULL DEFAULT 'Pagado',
  fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creado_por BIGINT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pagos_facturas FOREIGN KEY (factura_id) REFERENCES facturas(id),
  CONSTRAINT fk_pagos_usuarios FOREIGN KEY (creado_por) REFERENCES usuarios(id),
  INDEX idx_pagos_factura (factura_id),
  INDEX idx_pagos_estado (estado),
  INDEX idx_pagos_fecha (fecha_pago)
) ENGINE=InnoDB;

CREATE TABLE cancelaciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cita_id BIGINT UNSIGNED NOT NULL,
  motivo VARCHAR(220) NOT NULL,
  detalle TEXT NULL,
  cancelado_por BIGINT UNSIGNED NULL,
  tipo_actor ENUM('Cliente','Empleado','Admin','Sistema') NOT NULL DEFAULT 'Cliente',
  fecha_cancelacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cancelaciones_citas FOREIGN KEY (cita_id) REFERENCES citas(id),
  CONSTRAINT fk_cancelaciones_usuarios FOREIGN KEY (cancelado_por) REFERENCES usuarios(id),
  INDEX idx_cancelaciones_cita (cita_id),
  INDEX idx_cancelaciones_fecha (fecha_cancelacion)
) ENGINE=InnoDB;

CREATE TABLE auditoria (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NULL,
  accion VARCHAR(100) NOT NULL,
  modulo VARCHAR(80) NOT NULL,
  registro_id BIGINT UNSIGNED NULL,
  descripcion TEXT NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_auditoria_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  INDEX idx_auditoria_modulo (modulo),
  INDEX idx_auditoria_usuario (usuario_id),
  INDEX idx_auditoria_fecha (creado_en)
) ENGINE=InnoDB;

CREATE TABLE recordatorios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cita_id BIGINT UNSIGNED NULL,
  cliente_id BIGINT UNSIGNED NULL,
  empleado_id BIGINT UNSIGNED NULL,
  tipo ENUM('Cita','Pago','Inventario','Cumpleanos','Seguimiento','Sistema') NOT NULL DEFAULT 'Cita',
  canal ENUM('WhatsApp','Email','Sistema','SMS') NOT NULL DEFAULT 'Sistema',
  destinatario VARCHAR(160) NOT NULL,
  mensaje TEXT NOT NULL,
  programado_para DATETIME NOT NULL,
  enviado_en DATETIME NULL,
  estado ENUM('Pendiente','Enviado','Fallido','Cancelado') NOT NULL DEFAULT 'Pendiente',
  intentos INT UNSIGNED NOT NULL DEFAULT 0,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_recordatorios_citas FOREIGN KEY (cita_id) REFERENCES citas(id),
  CONSTRAINT fk_recordatorios_clientes FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_recordatorios_empleados FOREIGN KEY (empleado_id) REFERENCES empleados(id),
  INDEX idx_recordatorios_programado (programado_para, estado),
  INDEX idx_recordatorios_canal (canal),
  INDEX idx_recordatorios_cita (cita_id)
) ENGINE=InnoDB;

-- Mensajes enviados o recibidos. n8n puede leer/escribir aqui para WhatsApp, email o SMS.
CREATE TABLE mensajes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recordatorio_id BIGINT UNSIGNED NULL,
  cliente_id BIGINT UNSIGNED NULL,
  empleado_id BIGINT UNSIGNED NULL,
  canal ENUM('WhatsApp','Email','SMS','Sistema') NOT NULL,
  direccion ENUM('Entrante','Saliente') NOT NULL DEFAULT 'Saliente',
  destinatario VARCHAR(160) NOT NULL,
  asunto VARCHAR(180) NULL,
  cuerpo TEXT NOT NULL,
  proveedor VARCHAR(80) NULL COMMENT 'Twilio, Meta, Gmail, SMTP, etc.',
  proveedor_message_id VARCHAR(160) NULL,
  estado ENUM('Pendiente','Enviado','Entregado','Leido','Fallido') NOT NULL DEFAULT 'Pendiente',
  error TEXT NULL,
  enviado_en DATETIME NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mensajes_recordatorios FOREIGN KEY (recordatorio_id) REFERENCES recordatorios(id),
  CONSTRAINT fk_mensajes_clientes FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_mensajes_empleados FOREIGN KEY (empleado_id) REFERENCES empleados(id),
  INDEX idx_mensajes_estado (estado),
  INDEX idx_mensajes_canal (canal),
  INDEX idx_mensajes_proveedor_id (proveedor_message_id)
) ENGINE=InnoDB;

-- Cola de eventos para n8n. El workflow puede consultar Pendiente, procesar y marcar Procesado/Fallido.
CREATE TABLE automatizacion_eventos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  evento VARCHAR(100) NOT NULL,
  entidad VARCHAR(80) NOT NULL,
  entidad_id BIGINT UNSIGNED NOT NULL,
  payload JSON NULL,
  estado ENUM('Pendiente','Procesado','Fallido','Cancelado') NOT NULL DEFAULT 'Pendiente',
  programado_para DATETIME NULL,
  procesado_en DATETIME NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_auto_evento_estado (evento, estado),
  INDEX idx_auto_programado (programado_para, estado)
) ENGINE=InnoDB;

CREATE TABLE automatizacion_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  evento_id BIGINT UNSIGNED NULL,
  workflow VARCHAR(120) NULL,
  nivel ENUM('Info','Warning','Error') NOT NULL DEFAULT 'Info',
  mensaje TEXT NOT NULL,
  respuesta JSON NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_auto_logs_eventos FOREIGN KEY (evento_id) REFERENCES automatizacion_eventos(id),
  INDEX idx_auto_logs_evento (evento_id),
  INDEX idx_auto_logs_nivel (nivel)
) ENGINE=InnoDB;

DELIMITER //

CREATE TRIGGER trg_inventario_estado_bi
BEFORE INSERT ON inventario
FOR EACH ROW
BEGIN
  IF NEW.stock_actual <= 0 THEN
    SET NEW.estado = 'Agotado';
  ELSEIF NEW.stock_actual <= NEW.stock_minimo THEN
    SET NEW.estado = 'Bajo stock';
  END IF;
END//

CREATE TRIGGER trg_inventario_estado_bu
BEFORE UPDATE ON inventario
FOR EACH ROW
BEGIN
  IF NEW.stock_actual <= 0 THEN
    SET NEW.estado = 'Agotado';
  ELSEIF NEW.stock_actual <= NEW.stock_minimo THEN
    SET NEW.estado = 'Bajo stock';
  ELSEIF OLD.estado IN ('Bajo stock','Agotado') THEN
    SET NEW.estado = 'Activo';
  END IF;
END//

CREATE TRIGGER trg_cita_recordatorio_ai
AFTER INSERT ON citas
FOR EACH ROW
BEGIN
  INSERT INTO automatizacion_eventos (evento, entidad, entidad_id, payload, estado, programado_para)
  VALUES (
    'cita.creada',
    'citas',
    NEW.id,
    JSON_OBJECT('cita_id', NEW.id, 'cliente_id', NEW.cliente_id, 'empleado_id', NEW.empleado_id, 'fecha', NEW.fecha, 'hora_inicio', NEW.hora_inicio),
    'Pendiente',
    NOW()
  );
END//

CREATE TRIGGER trg_cita_cancelada_au
AFTER UPDATE ON citas
FOR EACH ROW
BEGIN
  IF OLD.estado <> 'Cancelado' AND NEW.estado = 'Cancelado' THEN
    INSERT INTO automatizacion_eventos (evento, entidad, entidad_id, payload, estado, programado_para)
    VALUES (
      'cita.cancelada',
      'citas',
      NEW.id,
      JSON_OBJECT('cita_id', NEW.id, 'cliente_id', NEW.cliente_id, 'empleado_id', NEW.empleado_id),
      'Pendiente',
      NOW()
    );
  END IF;
END//

CREATE PROCEDURE sp_horarios_disponibles(
  IN p_empleado_id BIGINT UNSIGNED,
  IN p_fecha DATE
)
BEGIN
  -- Bloques base de 5 horarios. Puedes reemplazar por generacion dinamica segun tabla horarios.
  SELECT slot.hora AS hora_disponible
  FROM (
    SELECT TIME('09:00:00') AS hora UNION ALL
    SELECT TIME('10:30:00') UNION ALL
    SELECT TIME('12:00:00') UNION ALL
    SELECT TIME('14:00:00') UNION ALL
    SELECT TIME('16:00:00')
  ) slot
  WHERE NOT EXISTS (
    SELECT 1
    FROM citas c
    WHERE c.empleado_id = p_empleado_id
      AND c.fecha = p_fecha
      AND c.hora_inicio = slot.hora
      AND c.estado NOT IN ('Cancelado','No asistio')
  )
  ORDER BY slot.hora;
END//

CREATE PROCEDURE sp_reporte_ingresos(
  IN p_desde DATE,
  IN p_hasta DATE
)
BEGIN
  SELECT
    DATE(f.fecha_emision) AS fecha,
    COUNT(*) AS facturas,
    SUM(f.total) AS total_facturado,
    SUM(CASE WHEN f.estado = 'Pagada' THEN f.total ELSE 0 END) AS total_pagado
  FROM facturas f
  WHERE DATE(f.fecha_emision) BETWEEN p_desde AND p_hasta
  GROUP BY DATE(f.fecha_emision)
  ORDER BY fecha;
END//

CREATE PROCEDURE sp_reporte_productividad_empleado(
  IN p_desde DATE,
  IN p_hasta DATE
)
BEGIN
  SELECT
    e.id AS empleado_id,
    e.nombre AS empleado,
    COUNT(c.id) AS total_citas,
    SUM(c.estado = 'Completado') AS citas_completadas,
    COALESCE(SUM(dc.precio), 0) AS ingresos_generados
  FROM empleados e
  LEFT JOIN citas c ON c.empleado_id = e.id AND c.fecha BETWEEN p_desde AND p_hasta
  LEFT JOIN detalle_citas dc ON dc.cita_id = c.id
  GROUP BY e.id, e.nombre
  ORDER BY citas_completadas DESC, ingresos_generados DESC;
END//

DELIMITER ;

CREATE OR REPLACE VIEW vw_agenda_diaria AS
SELECT
  c.id,
  c.fecha,
  c.hora_inicio,
  c.hora_fin,
  cl.nombre AS cliente,
  cl.telefono AS cliente_telefono,
  e.nombre AS empleado,
  GROUP_CONCAT(s.nombre ORDER BY s.nombre SEPARATOR ', ') AS servicios,
  c.estado
FROM citas c
JOIN clientes cl ON cl.id = c.cliente_id
JOIN empleados e ON e.id = c.empleado_id
LEFT JOIN detalle_citas dc ON dc.cita_id = c.id
LEFT JOIN servicios s ON s.id = dc.servicio_id
GROUP BY c.id, c.fecha, c.hora_inicio, c.hora_fin, cl.nombre, cl.telefono, e.nombre, c.estado;

CREATE OR REPLACE VIEW vw_servicios_mas_solicitados AS
SELECT
  s.id,
  s.nombre AS servicio,
  cs.nombre AS categoria,
  COUNT(dc.id) AS solicitudes,
  COALESCE(SUM(dc.precio), 0) AS ingresos
FROM servicios s
JOIN categorias_servicios cs ON cs.id = s.categoria_id
LEFT JOIN detalle_citas dc ON dc.servicio_id = s.id
GROUP BY s.id, s.nombre, cs.nombre
ORDER BY solicitudes DESC;

CREATE OR REPLACE VIEW vw_clientes_frecuentes AS
SELECT
  cl.id,
  cl.nombre,
  cl.telefono,
  COUNT(c.id) AS total_citas,
  MAX(c.fecha) AS ultima_visita
FROM clientes cl
LEFT JOIN citas c ON c.cliente_id = cl.id
GROUP BY cl.id, cl.nombre, cl.telefono
ORDER BY total_citas DESC, ultima_visita DESC;

CREATE OR REPLACE VIEW vw_recordatorios_pendientes AS
SELECT *
FROM recordatorios
WHERE estado = 'Pendiente'
  AND programado_para <= NOW();

INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'Administrador', 'Acceso completo al sistema'),
(2, 'Empleado', 'Acceso limitado a citas, perfil y productividad'),
(3, 'Recepcion', 'Gestion de agenda, clientes y pagos');

-- Password demo recomendado para reemplazar:
-- Genera hashes reales desde PHP con password_hash('123456', PASSWORD_DEFAULT).
INSERT INTO usuarios (id, rol_id, nombre, email, password_hash, telefono) VALUES
(1, 1, 'Administrador Mirror Glam', 'admin@mirrorglam.do', '$2y$10$CAMBIAR_HASH_DEMO_ADMIN', '809-555-0001'),
(2, 2, 'Lia Santos', 'lia@mirrorglam.do', '$2y$10$CAMBIAR_HASH_DEMO_LIA', '809-555-0101'),
(3, 2, 'Nora Diaz', 'nora@mirrorglam.do', '$2y$10$CAMBIAR_HASH_DEMO_NORA', '829-555-0102'),
(4, 2, 'Eva Rojas', 'eva@mirrorglam.do', '$2y$10$CAMBIAR_HASH_DEMO_EVA', '849-555-0103');

INSERT INTO clientes (id, nombre, telefono, email, preferencias) VALUES
(1, 'Valentina Perez', '809-555-0141', 'valentina@example.com', 'Peinados glam, acabado natural'),
(2, 'Camila Ruiz', '829-555-0122', 'camila@example.com', 'Manicure gel nude'),
(3, 'Laura Mendez', '849-555-0118', 'laura@example.com', 'Maquillaje social luminoso'),
(4, 'Sofia Alba', '809-555-0188', 'sofia@example.com', 'Tratamientos capilares');

INSERT INTO empleados (id, usuario_id, nombre, telefono, email, especialidad, fecha_ingreso, calificacion_promedio, estado) VALUES
(1, 2, 'Lia Santos', '809-555-0101', 'lia@mirrorglam.do', 'Maquillaje', '2025-01-15', 4.90, 'Activo'),
(2, 3, 'Nora Diaz', '829-555-0102', 'nora@mirrorglam.do', 'Uñas', '2025-02-10', 4.80, 'Activo'),
(3, 4, 'Eva Rojas', '849-555-0103', 'eva@mirrorglam.do', 'Cabello', '2025-03-01', 4.70, 'Activo');

INSERT INTO categorias_servicios (id, nombre, descripcion, color, icono) VALUES
(1, 'Cabello', 'Peinados, color y tratamientos', 'Oliva', 'fa-scissors'),
(2, 'Uñas', 'Manicure, pedicure y gel', 'Rosa palo', 'fa-hand-sparkles'),
(3, 'Makeup', 'Maquillaje social y glam', 'Nude', 'fa-wand-magic-sparkles'),
(4, 'Spa facial', 'Cuidado facial y relajacion', 'Crema', 'fa-spa');

INSERT INTO servicios (id, categoria_id, nombre, descripcion, duracion_minutos, precio) VALUES
(1, 1, 'Peinado glam', 'Peinado premium para eventos', 90, 2800.00),
(2, 2, 'Manicure gel', 'Manicure con gel de larga duracion', 75, 1600.00),
(3, 3, 'Maquillaje social', 'Maquillaje elegante para eventos', 90, 4200.00),
(4, 1, 'Tratamiento capilar', 'Hidratacion y brillo', 75, 3100.00);

INSERT INTO horarios (empleado_id, dia_semana, hora_inicio, hora_fin, descanso_inicio, descanso_fin) VALUES
(1, 1, '09:00:00', '17:00:00', '13:00:00', '14:00:00'),
(1, 2, '09:00:00', '17:00:00', '13:00:00', '14:00:00'),
(1, 3, '09:00:00', '17:00:00', '13:00:00', '14:00:00'),
(2, 2, '10:00:00', '18:00:00', '13:00:00', '14:00:00'),
(2, 3, '10:00:00', '18:00:00', '13:00:00', '14:00:00'),
(2, 4, '10:00:00', '18:00:00', '13:00:00', '14:00:00'),
(3, 1, '08:00:00', '16:00:00', '12:00:00', '13:00:00'),
(3, 3, '08:00:00', '16:00:00', '12:00:00', '13:00:00'),
(3, 5, '08:00:00', '16:00:00', '12:00:00', '13:00:00');

INSERT INTO inventario (nombre, sku, proveedor, stock_actual, stock_minimo, costo_unitario, precio_venta) VALUES
('Shampoo argan', 'MG-SH-001', 'Beauty Pro', 8, 10, 450.00, 850.00),
('Base HD', 'MG-MK-002', 'Glam Supply', 24, 8, 900.00, 1600.00),
('Esmalte nude', 'MG-UN-003', 'Nail House', 36, 12, 180.00, 450.00);

INSERT INTO citas (id, cliente_id, empleado_id, fecha, hora_inicio, hora_fin, estado, origen, creado_por) VALUES
(1, 1, 1, '2026-05-20', '09:00:00', '10:30:00', 'Pendiente', 'Web', 1),
(2, 2, 2, '2026-05-20', '10:30:00', '11:45:00', 'Completado', 'Admin', 1),
(3, 4, 3, '2026-05-21', '14:00:00', '15:15:00', 'Activo', 'Web', 1);

INSERT INTO detalle_citas (cita_id, servicio_id, precio, duracion_minutos) VALUES
(1, 1, 2800.00, 90),
(2, 2, 1600.00, 75),
(3, 4, 3100.00, 75);

INSERT INTO facturas (id, cita_id, cliente_id, numero, subtotal, descuento, impuesto, total, estado) VALUES
(1, 2, 2, 'F-1042', 1600.00, 0.00, 0.00, 1600.00, 'Pagada');

INSERT INTO pagos (factura_id, metodo, monto, referencia, estado, creado_por) VALUES
(1, 'Tarjeta', 1600.00, 'POS-1042', 'Pagado', 1);

INSERT INTO recordatorios (cita_id, cliente_id, empleado_id, tipo, canal, destinatario, mensaje, programado_para) VALUES
(1, 1, 1, 'Cita', 'WhatsApp', '8095550141', 'Hola Valentina, te recordamos tu cita en Mirror Glam hoy a las 9:00 AM.', '2026-05-20 07:00:00'),
(3, 4, 3, 'Cita', 'Email', 'sofia@example.com', 'Recordatorio de tu cita de tratamiento capilar en Mirror Glam.', '2026-05-21 09:00:00');
