<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../database/connection.php';

$resource = $_GET['resource'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Router API modular.
// INSERTAR FETCH API desde assets/js/api.js hacia este archivo.
// Cada resource debe delegar en su controlador: ClientesController, CitasController, etc.

try {
    if ($resource === 'login') {
        require_once __DIR__ . '/AuthController.php';
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        echo json_encode((new AuthController())->login($payload));
        exit;
    }

    if ($resource === 'recover') {
        require_once __DIR__ . '/AuthController.php';
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        echo json_encode((new AuthController())->recoverPassword((string)($payload['email'] ?? '')));
        exit;
    }

    switch ($resource) {
        case 'clientes':
        case 'empleados':
        case 'servicios':
        case 'categorias':
        case 'citas':
        case 'pagos':
        case 'facturacion':
        case 'cancelaciones':
        case 'inventario':
        case 'horarios':
        case 'usuarios':
        case 'auditoria':
        case 'notificaciones':
        case 'reportes':
        case 'dashboard':
            require_once __DIR__ . '/GenericCrudController.php';
            $controller = new GenericCrudController($resource);
            break;
        default:
            echo json_encode(['ok' => true, 'message' => 'Endpoint listo para conectar', 'resource' => $resource, 'method' => $method]);
            exit;
    }

    echo json_encode($controller->handle($method));
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
