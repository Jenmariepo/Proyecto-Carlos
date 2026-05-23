# Como importar la base de datos Mirror Glam

Archivo principal:

```txt
database/mirror_glam_schema.sql
```

## Opcion recomendada en phpMyAdmin

1. Entra a phpMyAdmin.
2. Ve a la pestana **Importar**.
3. Selecciona `mirror_glam_schema.sql`.
4. Formato: `SQL`.
5. Clic en **Importar**.

## Si no carga completa

Revisa el mensaje exacto del error. Las causas mas comunes son:

- No tienes permiso para `CREATE DATABASE`.
- Pegaste el SQL en la pestana SQL en vez de usar **Importar**.
- Ya existian triggers/procedimientos/vistas de un intento anterior.
- Tu MySQL/MariaDB es muy antiguo.

El archivo ya fue ajustado para poder reimportarse: elimina vistas, procedimientos y triggers antes de crearlos otra vez.

## Si tu hosting no permite crear base de datos

1. Crea manualmente una base llamada `mirror_glam`.
2. Abre `mirror_glam_schema.sql`.
3. Borra o comenta estas lineas:

```sql
CREATE DATABASE IF NOT EXISTS mirror_glam
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mirror_glam;
```

4. Importa el archivo dentro de la base ya creada.

## Si falla en triggers o procedimientos

Importa primero solo hasta antes de:

```sql
DELIMITER //
```

Luego importa desde las vistas/datos. Los triggers y procedimientos son utiles, pero las tablas principales funcionan sin ellos.

## Para n8n

n8n debe leer registros pendientes desde:

```sql
automatizacion_eventos
recordatorios
mensajes
```

Y luego actualizar:

```sql
estado = 'Procesado'
procesado_en = NOW()
```

o registrar errores en:

```sql
automatizacion_logs
```
