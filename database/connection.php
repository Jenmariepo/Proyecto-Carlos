<?php
/**
 * Conexion MySQL Mirror Glam.
 * Reemplaza las credenciales por las de tu base existente.
 */

declare(strict_types=1);

function db_config(): array
{
    static $config = null;

    if (is_array($config)) {
        return $config;
    }

    $configPath = __DIR__ . '/config.php';
    $config = file_exists($configPath) ? require $configPath : [];

    return $config;
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = db_config();

    $host = $config['host'] ?? getenv('DB_HOST') ?: 'localhost';
    $database = $config['database'] ?? getenv('DB_DATABASE') ?: 'mirror_glam';
    $user = $config['user'] ?? getenv('DB_USER') ?: 'root';
    $password = $config['password'] ?? getenv('DB_PASSWORD') ?: '';

    // CONECTAR BASE DE DATOS MYSQL AQUI.
    // No se modifica tu estructura existente: roles, usuarios, clientes, empleados,
    // categorias_servicios, servicios, horarios, citas, detalle_citas, facturas,
    // pagos, cancelaciones, inventario, recordatorios y auditoria.
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}
