<?php
declare(strict_types=1);

// public_html/formulario/confirmar.php — doble confirmación de la casilla de correos.
// GET muestra un botón; solo el POST confirma. Así los filtros de correo que abren
// enlaces automáticamente no confirman por el visitante.
require __DIR__ . '/../../formulario-app/bootstrap.php';

use function Tegia\Formulario\{cabecerasSinCache, formulario, registrar};
use Tegia\Formulario\Correo;

cabecerasSinCache();
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
header('X-Frame-Options: DENY');
header('Content-Type: text/html; charset=utf-8');

$token = (string) ($_POST['t'] ?? $_GET['t'] ?? '');
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    // Sin token no hay nada que mostrar. Con token funciona aunque haya clave de prueba:
    // el enlace llega por correo y se abre sin la cabecera X-Prueba.
    if ($token === '') {
        http_response_code(404);
        exit;
    }
    $estado = $metodo === 'POST' ? formulario()->confirmar($token, $_SERVER) : 'pedir';
} catch (\Throwable $e) {
    registrar('arranque_confirmar', ['clase' => $e::class]);
    $estado = 'error';
}

$mensajes = [
    'pedir'         => ['Confirma tu suscripción', 'Haz clic en el botón para confirmar que quieres recibir por correo contenidos jurídicos, invitaciones y novedades de Tegia Legal.'],
    'confirmada'    => ['¡Listo, quedaste suscrito!', 'Te escribiremos solo por correo y nunca más de una vez por semana. Cada correo trae un enlace para darte de baja.'],
    'ya_confirmada' => ['Ya estabas suscrito', 'Tu suscripción ya estaba confirmada. No tienes que hacer nada más.'],
    'vencida'       => ['El enlace venció', 'Este enlace ya no es válido. Si quieres recibir nuestros correos, vuelve a marcar la casilla en el formulario de tegialegal.com.'],
    'invalida'      => ['Enlace no válido', 'No encontramos esta suscripción. Revisa que el enlace esté completo o escríbenos a info@tegialegal.com.'],
    'error'         => ['Algo salió mal', 'No pudimos confirmar tu suscripción. Intenta de nuevo en unos minutos o escríbenos a info@tegialegal.com.'],
];
[$titulo, $texto] = $mensajes[$estado] ?? $mensajes['error'];
$e = [Correo::class, 'e'];
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $e($titulo) ?> — Tegia Legal</title>
<style>
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f5f8f6;font-family:Montserrat,-apple-system,"Segoe UI",Arial,sans-serif;color:#11150f;padding:24px;box-sizing:border-box}
  main{background:#fff;border:1px solid #e5ece8;border-radius:16px;padding:36px 32px;max-width:460px;width:100%;box-sizing:border-box}
  h1{font-size:22px;margin:0 0 12px;letter-spacing:-.01em}
  p{color:#3d4543;line-height:1.6;margin:0 0 20px}
  button,a.btn{display:inline-block;background:#2a5c44;color:#fff;border:0;border-radius:10px;padding:13px 22px;font:600 15px/1 inherit;font-family:inherit;cursor:pointer;text-decoration:none}
  button:hover,a.btn:hover{background:#1e4433}
  button:focus-visible,a.btn:focus-visible{outline:3px solid #9fe3c1;outline-offset:2px}
  small{display:block;margin-top:24px;color:#919191;font-size:12px}
</style>
</head>
<body>
<main>
  <h1><?= $e($titulo) ?></h1>
  <p><?= $e($texto) ?></p>
  <?php if ($estado === 'pedir'): ?>
    <form method="post">
      <input type="hidden" name="t" value="<?= $e($token) ?>">
      <button type="submit">Confirmar mi suscripción</button>
    </form>
  <?php else: ?>
    <a class="btn" href="https://www.tegialegal.com/">Ir a tegialegal.com</a>
  <?php endif; ?>
  <small>Tegia Legal · Responsable del tratamiento: María Camila Ramírez Gómez</small>
</main>
</body>
</html>
