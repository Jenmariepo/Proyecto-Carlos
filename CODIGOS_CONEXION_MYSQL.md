# Codigos para conectar Mirror Glam a MySQL

Copia estos archivos en este orden dentro de Visual Studio Code.

## 1. Base de datos

Importa primero:

```txt
database/mirror_glam_schema.sql
```

## 2. Conexion MySQL

Edita credenciales en:

```txt
database/connection.php
```

Valores actuales:

```php
$host = 'localhost';
$database = 'mirror_glam';
$user = 'root';
$password = '';
```

## 3. API principal

```txt
controllers/api.php
```

Este archivo recibe las peticiones desde JavaScript:

```txt
/controllers/api.php?resource=clientes
/controllers/api.php?resource=citas
/controllers/api.php?resource=inventario
```

## 4. CRUD MySQL generico

```txt
controllers/GenericCrudController.php
```

Este controlador conecta con MySQL para:

- clientes
- empleados
- servicios
- citas
- pagos
- facturacion
- inventario
- usuarios
- notificaciones
- reportes

## 5. JavaScript que consume MySQL

```txt
assets/js/api.js
assets/js/app.js
```

`api.js` intenta usar MySQL por `fetch`. Si la base o API no estan listas, usa datos simulados para que el sistema no se rompa.

## 6. Paginas que ya consumen esos datos

Estas paginas no necesitan cambiar estructura. Ya usan `module-table.php` y `api.js`:

```txt
admin/clientes.php
admin/empleados.php
admin/servicios.php
admin/citas.php
admin/pagos.php
admin/facturacion.php
admin/inventario.php
admin/usuarios.php
admin/reportes.php
admin/notificaciones.php
empleados/dashboard.php
empleados/citas.php
empleados/notificaciones.php
pages/reservas.php
```

## 7. Probar la API

Abre en el navegador:

```txt
http://localhost:8000/controllers/api.php?resource=clientes
```

Debe responder JSON.

## 8. Ejecutar servidor

```bash
tools\php\php.exe -S localhost:8000
```

## 9. Nota importante

Si ves datos simulados, significa que:

- MySQL no esta encendido.
- La base `mirror_glam` no fue importada.
- Las credenciales de `database/connection.php` no coinciden.
- PHP no tiene activo `pdo_mysql`.
