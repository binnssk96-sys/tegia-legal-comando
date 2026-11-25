<?php
// Copiar como config.php en el servidor (fuera de public_html) y llenar los valores.
// NUNCA subir config.php al repositorio: tiene claves.

return [
    // Base de datos propia del formulario (no la de WordPress).
    'bd_dsn'     => 'mysql:host=127.0.0.1;port=3306;dbname=u533514420_formulario;charset=utf8mb4',
    'bd_usuario' => 'u533514420_formulario',
    'bd_clave'   => '',

    // php -r 'echo base64_encode(random_bytes(32)), PHP_EOL;'
    // Si se pierde, los relatos guardados no se pueden leer: guardar una copia en un lugar seguro.
    'clave_cifrado' => '',
    // Secreto para el HMAC de la IP en el límite de envíos: bin2hex(random_bytes(32)).
    'secreto_ip'    => '',

    // Clave de SendGrid RESTRINGIDA solo a "Mail Send".
    'sendgrid_clave'   => '',
    'remitente'        => 'info@tegialegal.com',
    'remitente_nombre' => 'Tegia Legal',
    'responder_a'      => 'mariaramirez@tegialegal.com',
    'aviso_para'       => 'info@tegialegal.com',

    'url_confirmar' => 'https://www.tegialegal.com/formulario/confirmar.php',
    'url_politica'  => 'https://www.tegialegal.com/politica-de-tratamiento-de-datos',
    'telefono'      => '+57 304 683 0354',
    'whatsapp'      => 'https://wa.me/573046830354',

    'origenes'        => ['https://www.tegialegal.com', 'https://tegialegal.com'],
    'limite_por_hora' => 5,
    // Cabecera con la IP real del visitante. Verificar en el servidor (la CDN puede usar otra).
    'ip_cabecera'     => 'REMOTE_ADDR',

    // Cloudflare Turnstile: vacío = desactivado (quedan el campo trampa y los límites).
    'turnstile_secreto' => '',

    // Mientras no esté vacía, el formulario solo responde a peticiones con la cabecera X-Prueba igual a esta clave.
    // Vaciarla para publicar (cuando el equipo jurídico apruebe los textos).
    'clave_prueba' => '',

    'archivo_registro' => __DIR__ . '/registro/errores.log',
];
