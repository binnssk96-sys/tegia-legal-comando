<?php
declare(strict_types=1);

// Arranque del formulario. Vive FUERA de public_html (domains/tegialegal.com/formulario-app/).

namespace Tegia\Formulario;

spl_autoload_register(static function (string $clase): void {
    $prefijo = __NAMESPACE__ . '\\';
    if (str_starts_with($clase, $prefijo)) {
        $archivo = __DIR__ . '/src/' . substr($clase, strlen($prefijo)) . '.php';
        if (is_file($archivo)) {
            require $archivo;
        }
    }
});

/** @return array<string,mixed> */
function configuracion(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $ruta = __DIR__ . '/config.php';
        if (!is_file($ruta)) {
            throw new \RuntimeException('Falta config.php');
        }
        $cfg = require $ruta;
    }
    return $cfg;
}

function registrar(string $codigo, array $ctx = []): void
{
    $cfg = configuracion();
    $linea = gmdate('c') . ' ' . $codigo . ($ctx ? ' ' . json_encode($ctx, JSON_UNESCAPED_UNICODE) : '') . "\n";
    @file_put_contents($cfg['archivo_registro'], $linea, FILE_APPEND | LOCK_EX);
}

function formulario(): Formulario
{
    $cfg = configuracion();
    $pdo = new \PDO($cfg['bd_dsn'], $cfg['bd_usuario'], $cfg['bd_clave'], [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return new Formulario(
        $cfg,
        new Repositorio($pdo),
        new Correo($cfg),
        new Cifrado($cfg['clave_cifrado']),
        null,
        null,
        __NAMESPACE__ . '\\registrar',
    );
}

/** Cabeceras comunes: nada se guarda en caché (ni LiteSpeed ni la CDN). */
function cabecerasSinCache(): void
{
    header('Cache-Control: no-store, private');
    header('X-LiteSpeed-Cache-Control: no-cache');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
}
