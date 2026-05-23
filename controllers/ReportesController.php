<?php
declare(strict_types=1);

final class ReportesController
{
    public function handle(string $method): array
    {
        return match ($method) {
            'GET' => $this->generate($_GET),
            default => ['ok' => false, 'message' => 'Metodo no permitido'],
        };
    }

    private function generate(array $filters): array
    {
        $type = $filters['type'] ?? 'ingresos';
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        // IMPLEMENTAR AQUI CONSULTA MYSQL PARA REPORTES:
        // - ingresos: facturas + pagos
        // - servicios: servicios + detalle_citas + categorias_servicios
        // - empleados: empleados + citas + detalle_citas
        // - clientes: clientes + citas + facturas
        // - inventario: inventario
        // - citas: citas + detalle_citas + cancelaciones
        //
        // LLAMAR PROCEDIMIENTO ALMACENADO si tu BD ya tiene reportes:
        // CALL sp_reporte_ingresos(:from, :to)
        //
        // EXPORTAR PDF/EXCEL:
        // Puedes generar archivos desde backend con librerias como Dompdf/PhpSpreadsheet
        // cuando decidas integrar dependencias con Composer.

        return [
            'ok' => true,
            'message' => 'Reporte listo',
            'filters' => compact('type', 'from', 'to'),
            'data' => [],
        ];
    }
}
