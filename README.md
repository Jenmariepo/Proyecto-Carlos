# Mirror Glam - Sistema Web

Sistema web profesional y funcional para salon de belleza premium. Incluye landing page, login, recuperacion, panel administrativo, panel de empleados, reservas, facturacion, inventario y reportes con datos simulados.

## Ejecutar en VS Code

Con el PHP portable incluido en este proyecto:

```bash
tools\php\php.exe -S localhost:8000
```

Si tienes PHP instalado globalmente, tambien puedes usar:

```bash
php -S localhost:8000
```

Luego abre `http://localhost:8000`.

Composer portable:

```bash
tools\php\php.exe tools\composer\composer.phar --version
```

## Estructura

- `/assets/css` estilos responsive y tema claro/oscuro.
- `/assets/js` interacciones, tablas, modales, datos simulados y capa API.
- `/components` sidebar, topbar, tablas, header y footer reutilizables.
- `/pages` paginas publicas funcionales.
- `/admin` dashboard y modulos administrativos.
- `/empleados` panel restringido para empleados.
- `/auth` login y recuperacion.
- `/database/connection.php` conexion MySQL.
- `/controllers` endpoints, autenticacion y CRUD.
- `/models` consultas y reglas de datos.
- `/views` preparado para MVC/Laravel.

## Donde conectar tu base MySQL existente

1. Edita `database/connection.php` con tus credenciales.
2. Implementa consultas en `controllers/*Controller.php`.
3. Consume endpoints desde `assets/js/api.js`.
4. Reemplaza datos simulados de `assets/js/mock-data.js` por respuestas reales.
5. Valida roles en `AuthController.php` usando `usuarios` y `roles`.

## Tablas consideradas

`roles`, `usuarios`, `clientes`, `empleados`, `categorias_servicios`, `servicios`, `horarios`, `citas`, `detalle_citas`, `facturas`, `pagos`, `cancelaciones`, `inventario`, `recordatorios`, `auditoria`.

Tambien esta preparado para vistas SQL, triggers, procedimientos almacenados e indices existentes.
