<?php
declare(strict_types=1);

// Instalador de UN SOLO USO. Se sube a una carpeta temporal de public_html, se llama una vez
// con la cabecera X-Prueba y se BORRA. Crea las tablas y muestra qué cabeceras traen la IP.
// Uso: subir junto a una copia de esquema-mysql.sql y ajustar $app.

$app = __DIR__ . '/../../formulario-app';
require $app . '/bootstrap.php';

use function Tegia\Formulario\configuracion;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-LiteSpeed-Cache-Control: no-cache');

$cfg = configuracion();
if ($cfg['clave_prueba'] === '' || !hash_equals($cfg['clave_prueba'], (string) ($_SERVER['HTTP_X_PRUEBA'] ?? ''))) {
    http_response_code(404);
    exit;
}

$salida = ['php' => PHP_VERSION, 'sodium' => extension_loaded('sodium'), 'curl' => extension_loaded('curl')];
try {
    $pdo = new PDO($cfg['bd_dsn'], $cfg['bd_usuario'], $cfg['bd_clave'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sql = file_get_contents($app . '/sql/esquema-mysql.sql');
    $sql = preg_replace('/^--.*$/m', '', (string) $sql);
    foreach (array_filter(array_map('trim', explode(';', (string) $sql))) as $sentencia) {
        $pdo->exec($sentencia);
    }
    $salida['tablas'] = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $salida['error_bd'] = $e->getMessage();
}

$salida['cabeceras_ip'] = [];
foreach (['REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CF_CONNECTING_IP', 'HTTP_TRUE_CLIENT_IP', 'HTTP_X_CLIENT_IP'] as $k) {
    if (isset($_SERVER[$k])) {
        $salida['cabeceras_ip'][$k] = $_SERVER[$k];
    }
}
$salida['registro_escribible'] = is_writable(dirname($cfg['archivo_registro']));
echo json_encode($salida, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
