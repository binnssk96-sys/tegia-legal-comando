<?php
declare(strict_types=1);

// public_html/formulario/enviar.php — recibe el formulario y responde JSON.
require __DIR__ . '/../../formulario-app/bootstrap.php';

use function Tegia\Formulario\{cabecerasSinCache, formulario, registrar};

cabecerasSinCache();
header('Content-Type: application/json; charset=utf-8');

try {
    [$codigo, $cuerpo] = formulario()->procesar($_POST, $_SERVER);
} catch (\Throwable $e) {
    registrar('arranque', ['clase' => $e::class]);
    [$codigo, $cuerpo] = [500, ['ok' => false, 'error' => 'servidor',
        'mensaje' => 'No pudimos enviar tu solicitud. Intenta de nuevo en unos minutos o escríbenos por WhatsApp al +57 304 683 0354.']];
}

http_response_code($codigo);
echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
