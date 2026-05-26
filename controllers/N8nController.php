<?php
declare(strict_types=1);

final class N8nController
{
    private PDO $db;
    private array $config;

    public function __construct()
    {
        $this->db = db();
        $this->config = db_config();
    }

    public function handle(string $method): array
    {
        if (!$this->isAuthorized()) {
            http_response_code(401);
            return ['ok' => false, 'message' => 'Token n8n invalido'];
        }

        $action = $_GET['action'] ?? 'pendientes';

        return match ($action) {
            'pendientes' => $this->pending(),
            'resultado' => $this->result($this->payload()),
            default => ['ok' => false, 'message' => 'Accion n8n no configurada'],
        };
    }

    private function isAuthorized(): bool
    {
        $expected = (string)($this->config['n8n_secret'] ?? getenv('MIRROR_GLAM_N8N_SECRET') ?: 'mirror-glam-n8n-2026');
        $received = (string)(
            $_SERVER['HTTP_X_MIRRORGLAM_N8N_SECRET']
            ?? $_GET['secret']
            ?? ''
        );

        return $expected !== '' && hash_equals($expected, $received);
    }

    private function payload(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function pending(): array
    {
        $limit = max(1, min(50, (int)($_GET['limit'] ?? 20)));
        $channel = (string)($_GET['canal'] ?? 'WhatsApp');
        $includeEvents = (string)($_GET['include_eventos'] ?? '0') === '1';
        $allowedChannels = ['WhatsApp', 'Email', 'SMS', 'all'];
        if (!in_array($channel, $allowedChannels, true)) {
            $channel = 'WhatsApp';
        }

        $reminders = $this->db->prepare(
            'SELECT r.id,
                    r.cita_id,
                    r.cliente_id,
                    r.empleado_id,
                    r.tipo,
                    r.canal,
                    r.destinatario,
                    r.mensaje,
                    r.programado_para,
                    r.intentos,
                    cl.nombre AS cliente,
                    cl.telefono AS cliente_telefono,
                    cl.email AS cliente_email,
                    e.nombre AS empleado,
                    c.fecha,
                    c.hora_inicio,
                    COALESCE(s.nombre, "Servicio") AS servicio
             FROM recordatorios r
             LEFT JOIN clientes cl ON cl.id = r.cliente_id
             LEFT JOIN empleados e ON e.id = r.empleado_id
             LEFT JOIN citas c ON c.id = r.cita_id
             LEFT JOIN detalle_citas dc ON dc.cita_id = c.id
             LEFT JOIN servicios s ON s.id = dc.servicio_id
             WHERE r.estado = "Pendiente"
               AND r.programado_para <= NOW()
               AND (:canal = "all" OR r.canal = :canal)
             ORDER BY r.programado_para ASC
             LIMIT :limit'
        );
        $reminders->bindValue(':canal', $channel);
        $reminders->bindValue(':limit', $limit, PDO::PARAM_INT);
        $reminders->execute();

        $eventRows = [];
        if ($includeEvents) {
            $events = $this->db->prepare(
                'SELECT id, evento, entidad, entidad_id, payload, programado_para, creado_en
                 FROM automatizacion_eventos
                 WHERE estado = "Pendiente"
                   AND (programado_para IS NULL OR programado_para <= NOW())
                 ORDER BY creado_en ASC
                 LIMIT :limit'
            );
            $events->bindValue(':limit', $limit, PDO::PARAM_INT);
            $events->execute();
            $eventRows = $events->fetchAll();
        }

        return [
            'ok' => true,
            'data' => [
                'recordatorios' => array_map(fn ($row) => $this->formatReminder($row), $reminders->fetchAll()),
                'eventos' => array_map(fn ($row) => [
                    'id' => (int)$row['id'],
                    'evento' => $row['evento'],
                    'entidad' => $row['entidad'],
                    'entidad_id' => (int)$row['entidad_id'],
                    'payload' => json_decode((string)$row['payload'], true) ?: [],
                    'programado_para' => $row['programado_para'],
                    'creado_en' => $row['creado_en'],
                ], $eventRows),
            ],
        ];
    }

    private function formatReminder(array $row): array
    {
        $phone = (string)($row['cliente_telefono'] ?: $row['destinatario']);

        return [
            'id' => (int)$row['id'],
            'cita_id' => $row['cita_id'] ? (int)$row['cita_id'] : null,
            'cliente_id' => $row['cliente_id'] ? (int)$row['cliente_id'] : null,
            'empleado_id' => $row['empleado_id'] ? (int)$row['empleado_id'] : null,
            'tipo' => $row['tipo'],
            'canal' => $row['canal'],
            'destinatario' => $row['destinatario'],
            'telefono_whatsapp' => $this->normalizePhone($phone),
            'email' => $row['cliente_email'],
            'mensaje' => $row['mensaje'],
            'programado_para' => $row['programado_para'],
            'intentos' => (int)$row['intentos'],
            'cliente' => $row['cliente'],
            'empleado' => $row['empleado'],
            'servicio' => $row['servicio'],
            'fecha' => $row['fecha'],
            'hora' => $row['hora_inicio'],
        ];
    }

    private function result(array $payload): array
    {
        $recordatorioId = (int)($payload['recordatorio_id'] ?? $payload['id'] ?? 0);
        $eventoId = (int)($payload['evento_id'] ?? 0);
        $sent = (bool)($payload['ok'] ?? false);
        $status = $sent ? 'Enviado' : 'Fallido';
        $messageStatus = $sent ? 'Enviado' : 'Fallido';
        $provider = (string)($payload['proveedor'] ?? 'n8n');
        $providerMessageId = (string)($payload['proveedor_message_id'] ?? '');
        $error = (string)($payload['error'] ?? '');
        $workflow = (string)($payload['workflow'] ?? 'n8n-whatsapp');

        $this->db->beginTransaction();

        try {
            if ($recordatorioId > 0) {
                $reminder = $this->findReminder($recordatorioId);
                if (!$reminder) {
                    throw new RuntimeException('Recordatorio no encontrado');
                }

                $stmt = $this->db->prepare(
                    'UPDATE recordatorios
                     SET estado=:estado,
                         enviado_en=IF(:estado = "Enviado", NOW(), enviado_en),
                         intentos=intentos + 1
                     WHERE id=:id'
                );
                $stmt->execute(['estado' => $status, 'id' => $recordatorioId]);

                $this->db->prepare(
                    'INSERT INTO mensajes
                       (recordatorio_id, cliente_id, empleado_id, canal, direccion, destinatario, cuerpo, proveedor, proveedor_message_id, estado, error, enviado_en)
                     VALUES
                       (:recordatorio_id, :cliente_id, :empleado_id, :canal, "Saliente", :destinatario, :cuerpo, :proveedor, NULLIF(:proveedor_message_id, ""), :estado, NULLIF(:error, ""), IF(:estado = "Enviado", NOW(), NULL))'
                )->execute([
                    'recordatorio_id' => $recordatorioId,
                    'cliente_id' => $reminder['cliente_id'],
                    'empleado_id' => $reminder['empleado_id'],
                    'canal' => $reminder['canal'],
                    'destinatario' => $reminder['destinatario'],
                    'cuerpo' => $reminder['mensaje'],
                    'proveedor' => $provider,
                    'proveedor_message_id' => $providerMessageId,
                    'estado' => $messageStatus,
                    'error' => $error,
                ]);
            }

            if ($eventoId > 0) {
                $this->db->prepare(
                    'UPDATE automatizacion_eventos
                     SET estado=:estado, procesado_en=NOW()
                     WHERE id=:id'
                )->execute([
                    'estado' => $sent ? 'Procesado' : 'Fallido',
                    'id' => $eventoId,
                ]);

                $this->db->prepare(
                    'INSERT INTO automatizacion_logs (evento_id, workflow, nivel, mensaje, respuesta)
                     VALUES (:evento_id, :workflow, :nivel, :mensaje, :respuesta)'
                )->execute([
                    'evento_id' => $eventoId,
                    'workflow' => $workflow,
                    'nivel' => $sent ? 'Info' : 'Error',
                    'mensaje' => $sent ? 'Evento procesado por n8n' : ($error ?: 'Evento fallido en n8n'),
                    'respuesta' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ]);
            }

            $this->db->commit();
            return ['ok' => true, 'message' => 'Resultado registrado', 'estado' => $status];
        } catch (Throwable $e) {
            $this->db->rollBack();
            http_response_code(500);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function findReminder(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM recordatorios WHERE id=:id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) === 10 && str_starts_with($digits, '8')) {
            return '1' . $digits;
        }
        return $digits;
    }
}
