<?php
declare(strict_types=1);

// Pruebas del formulario: php formulario/pruebas/pruebas.php
// Base SQLite en memoria y SendGrid simulado: no se envía nada real.

namespace Tegia\Formulario;

use DateTimeImmutable;
use DateTimeZone;
use PDO;

spl_autoload_register(static function (string $c): void {
    $f = __DIR__ . '/../app/src/' . substr($c, strlen(__NAMESPACE__) + 1) . '.php';
    if (str_starts_with($c, __NAMESPACE__ . '\\') && is_file($f)) require $f;
});

$fallas = 0;
$total = 0;
function ok(bool $cond, string $nombre): void
{
    global $fallas, $total;
    $total++;
    if (!$cond) { $fallas++; echo "  FALLA  {$nombre}\n"; } else { echo "  ok     {$nombre}\n"; }
}

function esquemaSqlite(PDO $db): void
{
    $db->exec('CREATE TABLE contactos (id INTEGER PRIMARY KEY AUTOINCREMENT, correo TEXT NOT NULL UNIQUE, nombre TEXT NOT NULL, telefono TEXT NOT NULL, creado_en TEXT NOT NULL, actualizado_en TEXT NOT NULL)');
    $db->exec("CREATE TABLE consultas (id INTEGER PRIMARY KEY AUTOINCREMENT, contacto_id INTEGER NOT NULL REFERENCES contactos(id), area TEXT NOT NULL, relato_cifrado TEXT, prefiere_whatsapp INTEGER NOT NULL DEFAULT 0, estado TEXT NOT NULL DEFAULT 'nueva', origen TEXT NOT NULL, creada_en TEXT NOT NULL, acuse_enviado INTEGER, aviso_enviado INTEGER)");
    $db->exec('CREATE TABLE autorizaciones (id INTEGER PRIMARY KEY AUTOINCREMENT, contacto_id INTEGER NOT NULL, consulta_id INTEGER NOT NULL, tipo TEXT NOT NULL, version_texto TEXT NOT NULL, texto TEXT NOT NULL, texto_sha256 TEXT NOT NULL, otorgada_en TEXT NOT NULL, ip TEXT NOT NULL, navegador TEXT NOT NULL, canal TEXT NOT NULL, token_sha256 TEXT UNIQUE, token_expira_en TEXT, confirmada_en TEXT, ip_confirmacion TEXT, revocada_en TEXT)');
    $db->exec('CREATE TABLE intentos (id INTEGER PRIMARY KEY AUTOINCREMENT, ip_hmac TEXT NOT NULL, creado_en TEXT NOT NULL)');
}

/** Monta un formulario de prueba. */
function montar(array $cambios = [], ?int &$estadoSendgrid = null, ?bool &$turnstileOk = null): array
{
    $db = new PDO('sqlite::memory:');
    esquemaSqlite($db);
    $enviados = new \ArrayObject();
    $log = new \ArrayObject();
    $reloj = new \ArrayObject(['t' => new DateTimeImmutable('2026-11-25 15:00:00', new DateTimeZone('UTC'))]);
    $cfg = $cambios + [
        'origenes' => ['https://www.tegialegal.com', 'https://tegialegal.com'], 'secreto_ip' => 'secreto', 'limite_por_hora' => 5,
        'ip_cabecera' => 'REMOTE_ADDR', 'clave_prueba' => '', 'turnstile_secreto' => '',
        'sendgrid_clave' => 'SG.prueba', 'remitente' => 'info@tegialegal.com', 'remitente_nombre' => 'Tegia Legal',
        'responder_a' => 'mariaramirez@tegialegal.com', 'aviso_para' => 'info@tegialegal.com',
        'url_confirmar' => 'https://www.tegialegal.com/formulario/confirmar.php',
        'url_politica' => 'https://www.tegialegal.com/politica-de-tratamiento-de-datos',
        'telefono' => '+57 304 683 0354', 'whatsapp' => 'https://wa.me/573046830354',
    ];
    $transporte = static function (string $url, array $cab, string $cuerpo) use ($enviados, &$estadoSendgrid): array {
        $enviados[] = ['url' => $url, 'cab' => $cab, 'json' => json_decode($cuerpo, true)];
        return [$estadoSendgrid ?? 202, ''];
    };
    $clave = Cifrado::nuevaClave();
    $f = new Formulario(
        $cfg, new Repositorio($db), new Correo($cfg, $transporte), new Cifrado($clave),
        static function (string $r, string $ip) use (&$turnstileOk): bool { return $turnstileOk ?? true; },
        static fn(): DateTimeImmutable => $reloj['t'],
        static function (string $c, array $x) use ($log): void { $log[] = [$c, $x]; },
    );
    return [$f, $db, $enviados, $log, new Cifrado($clave), $reloj];
}

function post(array $cambios = []): array
{
    $inicio = (string) ((new DateTimeImmutable('2026-11-25 14:59:40', new DateTimeZone('UTC')))->getTimestamp() * 1000);
    return array_filter($cambios + [
        'nombre' => 'Ana María Pérez', 'correo' => 'Ana@Ejemplo.com', 'telefono' => '+57 300 123 4567',
        'area' => 'Laboral y seguridad social', 'relato' => "Me despidieron sin justa causa.\nTengo contrato a término fijo.",
        'autoriza_atencion' => '1', 'autoriza_correos' => '1', 'autoriza_whatsapp' => '1',
        'version_textos' => Textos::VERSION_ACTUAL, 'inicio' => $inicio, 'sitio_web' => '',
    ], static fn($v) => $v !== null);
}

function servidor(array $cambios = []): array
{
    return $cambios + ['REQUEST_METHOD' => 'POST', 'HTTP_ORIGIN' => 'https://www.tegialegal.com', 'REMOTE_ADDR' => '181.50.10.20', 'HTTP_USER_AGENT' => 'Pruebas/1.0'];
}

function contar(PDO $db, string $tabla): int { return (int) $db->query("SELECT COUNT(*) FROM {$tabla}")->fetchColumn(); }
function porCategoria(\ArrayObject $env, string $cat): ?array
{
    foreach ($env as $e) if (($e['json']['categories'][0] ?? '') === $cat) return $e['json'];
    return null;
}
function cuerpoTexto(array $correo): string { return $correo['content'][0]['value'] . "\n" . $correo['content'][1]['value']; }

echo "Envío válido completo\n";
[$f, $db, $env, $log, $cif] = montar();
[$cod, $res] = $f->procesar(post(), servidor());
ok($cod === 200 && $res === ['ok' => true], 'responde 200 ok');
ok(contar($db, 'contactos') === 1 && contar($db, 'consultas') === 1 && contar($db, 'autorizaciones') === 3, 'guarda contacto, consulta y 3 autorizaciones');
$c = $db->query('SELECT * FROM contactos')->fetch(PDO::FETCH_ASSOC);
ok($c['correo'] === 'ana@ejemplo.com' && $c['telefono'] === '+573001234567', 'normaliza correo y teléfono');
$q = $db->query('SELECT * FROM consultas')->fetch(PDO::FETCH_ASSOC);
ok(!str_contains($q['relato_cifrado'], 'despidieron') && str_starts_with($q['relato_cifrado'], 'v1:'), 'el relato queda cifrado');
ok($cif->descifrar($q['relato_cifrado']) === "Me despidieron sin justa causa.\nTengo contrato a término fijo.", 'el relato se descifra con la clave');
ok((int) $q['acuse_enviado'] === 1 && (int) $q['aviso_enviado'] === 1 && (int) $q['prefiere_whatsapp'] === 1, 'marca correos enviados y preferencia de WhatsApp');
$auts = $db->query('SELECT * FROM autorizaciones ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
ok(array_column($auts, 'tipo') === ['atencion', 'correos', 'whatsapp'], 'tipos de autorización');
ok($auts[0]['texto'] === Textos::version(Textos::VERSION_ACTUAL)['atencion'] && $auts[0]['texto_sha256'] === hash('sha256', $auts[0]['texto']), 'guarda el texto exacto y su huella');
ok($auts[0]['version_texto'] === 'casillas-2026-11-23/v2.1-borrador' && $auts[0]['ip'] === '181.50.10.20' && $auts[0]['navegador'] === 'Pruebas/1.0', 'guarda versión, IP y navegador');
ok($auts[1]['token_sha256'] !== null && $auts[1]['token_expira_en'] === '2026-12-25 15:00:00' && $auts[1]['confirmada_en'] === null, 'correos: token con vencimiento a 30 días, sin confirmar');
ok($auts[0]['token_sha256'] === null && $auts[2]['token_sha256'] === null, 'solo la autorización de correos lleva token');
ok(count($env) === 3, 'envía 3 correos');
$acuse = porCategoria($env, 'formulario-acuse');
$aviso = porCategoria($env, 'formulario-aviso');
$conf = porCategoria($env, 'formulario-confirmacion');
ok($acuse && $acuse['personalizations'][0]['to'][0]['email'] === 'ana@ejemplo.com' && $acuse['from']['email'] === 'info@tegialegal.com', 'acuse al visitante desde info@');
ok($acuse && !str_contains(cuerpoTexto($acuse), 'despidieron'), 'el acuse NO incluye el relato');
ok($acuse && str_contains(cuerpoTexto($acuse), 'menos de 24 horas') && !str_contains(cuerpoTexto($acuse), 'hábiles'), 'el acuse promete menos de 24 horas, sin "hábiles"');
ok($aviso && $aviso['personalizations'][0]['to'][0]['email'] === 'info@tegialegal.com' && $aviso['reply_to']['email'] === 'ana@ejemplo.com', 'aviso a info@ con respuesta directa al visitante');
ok($aviso && str_contains(cuerpoTexto($aviso), 'despidieron') && str_contains($aviso['subject'], 'Laboral'), 'el aviso incluye relato y área');
ok($conf && preg_match('~confirmar\.php\?t=([A-Za-z0-9_-]{43})~', cuerpoTexto($conf), $m) === 1, 'la confirmación trae el enlace con token');
ok(hash('sha256', $m[1] ?? '') === $auts[1]['token_sha256'], 'el token del correo corresponde al guardado (solo se guarda su huella)');
$sinSeguimiento = true;
foreach ($env as $e) $sinSeguimiento = $sinSeguimiento && $e['json']['tracking_settings']['click_tracking']['enable'] === false && $e['json']['tracking_settings']['open_tracking']['enable'] === false;
ok($sinSeguimiento, 'ningún correo lleva seguimiento de aperturas ni clics');
ok(count($log) === 0, 'sin entradas en el registro de errores');

echo "Doble confirmación\n";
$token = $m[1];
ok($f->confirmar($token, ['REMOTE_ADDR' => '181.50.10.21']) === 'confirmada', 'confirma con el token');
$a = $db->query("SELECT * FROM autorizaciones WHERE tipo = 'correos'")->fetch(PDO::FETCH_ASSOC);
ok($a['confirmada_en'] === '2026-11-25 15:00:00' && $a['ip_confirmacion'] === '181.50.10.21', 'guarda fecha e IP de la confirmación');
ok($f->confirmar($token, []) === 'ya_confirmada', 'segunda vez: ya confirmada');
ok($f->confirmar(str_repeat('A', 43), []) === 'invalida', 'token inexistente: inválida');
ok($f->confirmar('corto', []) === 'invalida', 'token mal formado: inválida');
[$f2, $db2, $env2, , , $reloj2] = montar();
$f2->procesar(post(), servidor());
preg_match('~t=([A-Za-z0-9_-]{43})~', cuerpoTexto(porCategoria($env2, 'formulario-confirmacion')), $m2);
$reloj2['t'] = new DateTimeImmutable('2026-12-26 15:00:00', new DateTimeZone('UTC'));
ok($f2->confirmar($m2[1], []) === 'vencida', 'después de 30 días: vencida');

echo "Sin casillas opcionales\n";
[$f, $db, $env] = montar();
[$cod] = $f->procesar(post(['autoriza_correos' => null, 'autoriza_whatsapp' => null, 'relato' => null]), servidor());
ok($cod === 200 && contar($db, 'autorizaciones') === 1, 'solo guarda la autorización de atención');
ok(count($env) === 2 && porCategoria($env, 'formulario-confirmacion') === null, 'no envía correo de confirmación');
ok($db->query('SELECT relato_cifrado FROM consultas')->fetchColumn() === null, 'sin relato: no guarda nada cifrado');
ok(str_contains(cuerpoTexto(porCategoria($env, 'formulario-aviso')), 'No escribió relato'), 'el aviso dice que no hubo relato');

echo "Validación\n";
[$f, $db, $env] = montar(['limite_por_hora' => 100]);
[$cod, $res] = $f->procesar(post(['autoriza_atencion' => null]), servidor());
ok($cod === 422 && isset($res['campos']['autoriza_atencion']), 'sin autorización de atención: 422');
[$cod, $res] = $f->procesar(post(['correo' => 'no-es-correo']), servidor());
ok($cod === 422 && isset($res['campos']['correo']), 'correo inválido: 422');
[$cod, $res] = $f->procesar(post(['telefono' => '123']), servidor());
ok($cod === 422 && isset($res['campos']['telefono']), 'teléfono corto: 422');
[$cod, $res] = $f->procesar(post(['area' => 'Penal']), servidor());
ok($cod === 422 && isset($res['campos']['area']), 'área no permitida: 422');
[$cod, $res] = $f->procesar(post(['relato' => str_repeat('a', 4001)]), servidor());
ok($cod === 422 && isset($res['campos']['relato']), 'relato de más de 4000 caracteres: 422');
[$cod, $res] = $f->procesar(post(['version_textos' => 'otra']), servidor());
ok($cod === 422 && isset($res['campos']['version_textos']), 'versión de textos desconocida: 422');
[$cod, $res] = $f->procesar(post(['nombre' => ['arreglo']]), servidor());
ok($cod === 422 && isset($res['campos']['nombre']), 'campo que no es texto: 422');
ok(contar($db, 'consultas') === 0 && count($env) === 0, 'nada se guarda ni se envía si hay errores');

echo "Protección\n";
[$f, $db, $env, $log] = montar();
[$cod, $res] = $f->procesar(post(['sitio_web' => 'http://spam.example']), servidor());
ok($cod === 200 && $res['ok'] === true && contar($db, 'consultas') === 0 && count($env) === 0, 'campo trampa: responde ok pero no guarda ni envía');
$rapido = (string) ((new DateTimeImmutable('2026-11-25 14:59:59', new DateTimeZone('UTC')))->getTimestamp() * 1000);
[$cod, $res] = $f->procesar(post(['inicio' => $rapido]), servidor());
ok($cod === 200 && contar($db, 'consultas') === 0, 'enviado en menos de 3 segundos: no guarda');
[$cod] = $f->procesar(post(['inicio' => null]), servidor());
ok($cod === 200 && contar($db, 'consultas') === 0, 'sin marca de tiempo: no guarda');
[$cod] = $f->procesar(post(), servidor(['HTTP_ORIGIN' => 'https://otro-sitio.com']));
ok($cod === 403, 'origen ajeno: 403');
[$cod] = $f->procesar(post(), servidor(['HTTP_ORIGIN' => '', 'HTTP_REFERER' => 'https://www.tegialegal.com/#diagnostico']));
ok($cod === 200 && contar($db, 'consultas') === 1, 'sin Origin pero con Referer del sitio: acepta');
[$cod] = $f->procesar(post(), servidor(['REQUEST_METHOD' => 'GET']));
ok($cod === 405, 'GET: 405');
ok(in_array('trampa', array_column((array) $log, 0), true) && in_array('origen_rechazado', array_column((array) $log, 0), true), 'el registro anota trampas y orígenes rechazados');
$logPlano = json_encode((array) $log);
ok(!str_contains($logPlano, 'Ana') && !str_contains($logPlano, 'ejemplo.com') && !str_contains($logPlano, '181.50'), 'el registro no tiene nombre, correo ni IP');

echo "Límite por IP\n";
[$f, $db] = montar(['limite_por_hora' => 3]);
$codigos = [];
for ($i = 0; $i < 4; $i++) { [$codigos[]] = $f->procesar(post(['correo' => "p{$i}@ejemplo.com"]), servidor()); }
ok($codigos === [200, 200, 200, 429], 'el cuarto envío en la hora devuelve 429');
[$cod] = $f->procesar(post(), servidor(['REMOTE_ADDR' => '181.50.10.99']));
ok($cod === 200, 'otra IP sí puede enviar');
ok(!str_contains(implode(',', $db->query('SELECT ip_hmac FROM intentos')->fetchAll(PDO::FETCH_COLUMN)), '181.50'), 'el límite guarda HMAC, no la IP');

echo "Clave de prueba y Turnstile\n";
[$f, $db] = montar(['clave_prueba' => 'clave-secreta']);
[$cod] = $f->procesar(post(), servidor());
ok($cod === 404 && contar($db, 'consultas') === 0, 'con clave de prueba y sin cabecera: 404');
[$cod] = $f->procesar(post(), servidor(['HTTP_X_PRUEBA' => 'clave-secreta']));
ok($cod === 200 && contar($db, 'consultas') === 1, 'con la cabecera correcta: procesa');
$tsOk = false;
[$f, $db] = montar(['turnstile_secreto' => 'ts'], $nada, $tsOk);
[$cod, $res] = $f->procesar(post(['cf-turnstile-response' => 'x']), servidor());
ok($cod === 403 && $res['error'] === 'antibot', 'Turnstile rechazado: 403');
[$cod] = $f->procesar(post(), servidor());
ok($cod === 403, 'Turnstile activo y sin respuesta: 403');
$tsOk = true;
[$cod] = $f->procesar(post(['cf-turnstile-response' => 'x']), servidor());
ok($cod === 200 && contar($db, 'consultas') === 1, 'Turnstile aprobado: procesa');

echo "Fallas de SendGrid y datos peligrosos\n";
$sg = 500;
[$f, $db, $env, $log] = montar([], $sg);
[$cod, $res] = $f->procesar(post(['nombre' => 'Eve <script>alert(1)</script>']), servidor());
ok($cod === 200 && contar($db, 'consultas') === 1, 'si SendGrid falla, la consulta queda guardada y el visitante ve ok');
$q = $db->query('SELECT acuse_enviado, aviso_enviado FROM consultas')->fetch(PDO::FETCH_ASSOC);
ok((int) $q['acuse_enviado'] === 0 && (int) $q['aviso_enviado'] === 0, 'queda marcado que los correos no salieron');
ok(in_array('correo', array_column((array) $log, 0), true), 'el registro anota la falla de correo');
$html = porCategoria($env, 'formulario-aviso')['content'][1]['value'];
ok(!str_contains($html, '<script>') && str_contains($html, '&lt;script&gt;'), 'el HTML del aviso escapa lo que escribe el visitante');

echo "Mismo correo dos veces\n";
[$f, $db] = montar();
$f->procesar(post(), servidor());
$f->procesar(post(['nombre' => 'Ana M. Pérez', 'telefono' => '3009998877']), servidor());
ok(contar($db, 'contactos') === 1 && contar($db, 'consultas') === 2, 'un contacto, dos consultas');
ok($db->query('SELECT nombre FROM contactos')->fetchColumn() === 'Ana M. Pérez', 'actualiza los datos del contacto');

echo "Cifrado\n";
$k = Cifrado::nuevaClave();
$x = new Cifrado($k);
$g = $x->cifrar('secreto profesional');
ok($g !== $x->cifrar('secreto profesional'), 'cada cifrado es distinto (nonce aleatorio)');
$alterado = substr($g, 0, -2) . (substr($g, -2) === 'AA' ? 'BB' : 'AA');
$fallo = false;
try { $x->descifrar($alterado); } catch (\RuntimeException) { $fallo = true; }
ok($fallo, 'un dato alterado no se descifra');
$fallo = false;
try { (new Cifrado(Cifrado::nuevaClave()))->descifrar($g); } catch (\RuntimeException) { $fallo = true; }
ok($fallo, 'otra clave no descifra');

echo "\n" . ($total - $fallas) . " de {$total} pruebas pasaron.\n";
exit($fallas ? 1 : 0);
