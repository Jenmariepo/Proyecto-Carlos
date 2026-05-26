# Integracion n8n + WhatsApp - Mirror Glam

Esta integracion deja a Mirror Glam listo para que n8n envie mensajes automaticos por WhatsApp usando el proveedor que prefieras: WhatsApp Cloud API, Twilio, Evolution API, WPPConnect u otro.

## 1. Token privado

En tu archivo local `database/config.php` debe existir:

```php
'n8n_secret' => 'mirror-glam-n8n-2026',
```

En n8n envia ese mismo valor como header:

```text
X-MirrorGlam-N8N-Secret: mirror-glam-n8n-2026
```

## 2. Leer recordatorios pendientes

Nodo recomendado en n8n: `HTTP Request`

```text
GET http://localhost:8000/controllers/api.php?resource=n8n&action=pendientes&canal=WhatsApp
```

Headers:

```text
X-MirrorGlam-N8N-Secret: mirror-glam-n8n-2026
```

Respuesta esperada:

```json
{
  "ok": true,
  "data": {
    "recordatorios": [
      {
        "id": 1,
        "telefono_whatsapp": "18095550141",
        "mensaje": "Hola Valentina...",
        "cliente": "Valentina Perez",
        "servicio": "Peinado glam"
      }
    ],
    "eventos": []
  }
}
```

## 3. Enviar WhatsApp desde n8n

Flujo recomendado:

1. `Schedule Trigger`: cada 5 o 10 minutos.
2. `HTTP Request`: leer pendientes desde Mirror Glam.
3. `Split In Batches`: recorrer `data.recordatorios`.
4. Nodo del proveedor WhatsApp:
   - Para: `telefono_whatsapp`
   - Mensaje: `mensaje`
5. `HTTP Request`: marcar resultado en Mirror Glam.

## 4. Marcar como enviado o fallido

```text
POST http://localhost:8000/controllers/api.php?resource=n8n&action=resultado
```

Headers:

```text
Content-Type: application/json
X-MirrorGlam-N8N-Secret: mirror-glam-n8n-2026
```

Body si se envio correctamente:

```json
{
  "recordatorio_id": 1,
  "ok": true,
  "proveedor": "WhatsApp Cloud API",
  "proveedor_message_id": "wamid.xxx",
  "workflow": "recordatorios-whatsapp"
}
```

Body si fallo:

```json
{
  "recordatorio_id": 1,
  "ok": false,
  "proveedor": "WhatsApp Cloud API",
  "error": "Numero invalido o proveedor sin respuesta",
  "workflow": "recordatorios-whatsapp"
}
```

## 5. Como se crean los recordatorios

Cuando se crea o actualiza una cita desde el sistema, Mirror Glam genera un registro en `recordatorios` con:

- `tipo = Cita`
- `canal = WhatsApp`
- `estado = Pendiente`
- `programado_para = 24 horas antes de la cita`

Si la cita es para menos de 24 horas, se programa unos minutos despues para que n8n pueda enviarlo.

## 6. Tablas usadas

- `recordatorios`: cola de mensajes pendientes.
- `mensajes`: historial de mensajes enviados o fallidos.
- `automatizacion_eventos`: eventos internos para flujos futuros.
- `automatizacion_logs`: respuestas y errores de n8n.

## 7. Cuando lo pongas en hosting

Cambia `localhost:8000` por tu dominio real:

```text
https://tudominio.com/controllers/api.php?resource=n8n&action=pendientes
```

No compartas el token de n8n en GitHub ni en capturas.
