# Modelos

Coloca aqui las clases que encapsulan consultas a tus tablas existentes.

Ejemplos:

- `ClienteModel.php` usa tabla `clientes`
- `CitaModel.php` usa `citas` y `detalle_citas`
- `FacturaModel.php` usa `facturas` y `pagos`
- `InventarioModel.php` usa `inventario`

Mantener las consultas parametrizadas con PDO para evitar inyeccion SQL.
