# Codigos en orden para pasar a Visual Studio Code

Si vas a copiar manualmente, crea primero las carpetas y luego pega los archivos en este orden.

## 1. Estructura de carpetas

```txt
assets/
assets/css/
assets/js/
assets/img/
components/
pages/
admin/
empleados/
auth/
database/
controllers/
models/
views/
```

## 2. Archivos base

1. `README.md`
2. `assets/img/mirror-glam-logo.svg`
3. `assets/css/styles.css`
4. `assets/js/mock-data.js`
5. `assets/js/api.js`
6. `assets/js/app.js`

## 3. Componentes reutilizables

1. `components/header.php`
2. `components/footer.php`
3. `components/public-nav.php`
4. `components/sidebar.php`
5. `components/topbar.php`
6. `components/module-table.php`

## 4. Pagina publica y autenticacion

1. `index.php`
2. `pages/reservas.php`
3. `auth/login.php`
4. `auth/recover.php`

## 5. Panel administrativo

1. `admin/dashboard.php`
2. `admin/clientes.php`
3. `admin/empleados.php`
4. `admin/servicios.php`
5. `admin/categorias.php`
6. `admin/citas.php`
7. `admin/pagos.php`
8. `admin/facturacion.php`
9. `admin/cancelaciones.php`
10. `admin/inventario.php`
11. `admin/horarios.php`
12. `admin/usuarios.php`
13. `admin/auditoria.php`
14. `admin/reportes.php`

## 6. Panel empleados

1. `empleados/dashboard.php`
2. `empleados/citas.php`
3. `empleados/horario.php`
4. `empleados/productividad.php`
5. `empleados/historial.php`
6. `empleados/perfil.php`

## 7. Backend listo para conectar MySQL

1. `database/connection.php`
2. `controllers/api.php`
3. `controllers/AuthController.php`
4. `controllers/ClientesController.php`
5. `controllers/CitasController.php`
6. `controllers/CrudController.template.php`
7. `models/BaseModel.php`
8. `models/README.md`
9. `views/README.md`

## 8. Como correrlo

Con el PHP portable incluido:

```bash
tools\php\php.exe -S localhost:8000
```

Con PHP global:

```bash
php -S localhost:8000
```

Abre:

```txt
http://localhost:8000
```

## 9. Importante sobre la base de datos

Edita primero:

```txt
database/connection.php
```

Luego conecta los CRUD en:

```txt
controllers/
assets/js/api.js
```

Los datos simulados estan en:

```txt
assets/js/mock-data.js
```
