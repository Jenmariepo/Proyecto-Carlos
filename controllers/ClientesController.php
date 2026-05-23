<?php
declare(strict_types=1);

final class ClientesController
{
    public function handle(string $method): array
    {
        return match ($method) {
            'GET' => $this->index(),
            'POST' => $this->store(json_decode(file_get_contents('php://input'), true) ?? []),
            'PUT', 'PATCH' => $this->update(json_decode(file_get_contents('php://input'), true) ?? []),
            'DELETE' => $this->destroy((int)($_GET['id'] ?? 0)),
            default => ['ok' => false, 'message' => 'Metodo no permitido'],
        };
    }

    private function index(): array
    {
        // IMPLEMENTAR AQUI CONSULTA MYSQL:
        // SELECT * FROM clientes ORDER BY nombre ASC LIMIT :limit OFFSET :offset
        return ['ok' => true, 'data' => []];
    }

    private function store(array $data): array
    {
        // CONECTAR CRUD CLIENTES:
        // INSERT INTO clientes (...) VALUES (...)
        // Registrar accion en auditoria.
        return ['ok' => true, 'data' => $data];
    }

    private function update(array $data): array
    {
        // UPDATE clientes SET ... WHERE id = :id
        return ['ok' => true, 'data' => $data];
    }

    private function destroy(int $id): array
    {
        // DELETE logico recomendado si tu BD maneja estados.
        // UPDATE clientes SET estado = 'Inactivo' WHERE id = :id
        return ['ok' => true, 'id' => $id];
    }
}
