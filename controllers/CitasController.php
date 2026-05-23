<?php
declare(strict_types=1);

final class CitasController
{
    public function handle(string $method): array
    {
        return match ($method) {
            'GET' => $this->availability(),
            'POST' => $this->store(json_decode(file_get_contents('php://input'), true) ?? []),
            'PATCH' => $this->updateStatus(json_decode(file_get_contents('php://input'), true) ?? []),
            default => ['ok' => false, 'message' => 'Metodo no permitido'],
        };
    }

    private function availability(): array
    {
        // LLAMAR PROCEDIMIENTO ALMACENADO:
        // CALL sp_horarios_disponibles(:servicio_id, :empleado_id, :fecha)
        // Combinar tablas horarios, citas y empleados.
        return ['ok' => true, 'data' => []];
    }

    private function store(array $data): array
    {
        // INSERTAR en citas y detalle_citas dentro de una transaccion.
        // Crear recordatorio y registro de auditoria.
        return ['ok' => true, 'data' => $data];
    }

    private function updateStatus(array $data): array
    {
        // UPDATE citas SET estado = :estado WHERE id = :id
        // En empleados, filtrar tambien por empleado_id de la sesion.
        return ['ok' => true, 'data' => $data];
    }
}
