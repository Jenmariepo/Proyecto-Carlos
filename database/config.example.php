<?php
/**
 * Copia este archivo como database/config.php y coloca tus credenciales locales.
 * database/config.php esta ignorado por Git para no subir contrasenas al repositorio.
 */

return [
    'host' => 'localhost',
    'database' => 'mirror_glam',
    'user' => 'root',
    'password' => '',

    // Token privado para que solo n8n pueda leer y actualizar recordatorios.
    // Cambialo en database/config.php y usalo en n8n como header:
    // X-MirrorGlam-N8N-Secret: tu-token-seguro
    'n8n_secret' => 'cambia-este-token',
];
