<?php
declare(strict_types=1);

namespace Tegia\Formulario;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Procesa un envío del formulario de diagnóstico.
 * Devuelve [código HTTP, cuerpo JSON]. No escribe datos personales en el registro de errores.
 */
final class Formulario
{
    public const TIEMPO_MINIMO_S = 3;
    public const VIGENCIA_TOKEN_DIAS = 30;

    private const MENSAJE_FALLA = 'No pudimos enviar tu solicitud. Intenta de nuevo en unos minutos o escríbenos por WhatsApp al +57 304 683 0354.';

    /** @var callable(string,string):bool */
    private $verificarTurnstile;
    /** @var callable():DateTimeImmutable */
    private $reloj;
    /** @var callable(string,array<string,scalar>):void */
    private $registro;

    /**
     * @param array{origenes:list<string>,secreto_ip:string,limite_por_hora:int,ip_cabecera:string,
     *   clave_prueba:string,turnstile_secreto:string} $cfg
     */
    public function __construct(
        private array $cfg,
        private Repositorio $repo,
        private Correo $correo,
        private Cifrado $cifrado,
        ?callable $verificarTurnstile = null,
        ?callable $reloj = null,
        ?callable $registro = null,
    ) {
        $this->verificarTurnstile = $verificarTurnstile ?? fn(string $r, string $ip): bool => self::turnstilePorCurl($this->cfg['turnstile_secreto'], $r, $ip);
        $this->reloj = $reloj ?? static fn(): DateTimeImmutable => new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $this->registro = $registro ?? static function (string $c, array $x): void { error_log('formulario ' . $c . ' ' . json_encode($x)); };
    }

    /**
     * @param array<string,mixed> $post
     * @param array<string,mixed> $server
     * @return array{0:int,1:array<string,mixed>}
     */
    public function procesar(array $post, array $server): array
    {
        if (!$this->pasaClavePrueba($server)) {
            return [404, ['ok' => false, 'error' => 'no_encontrado']];
        }
        if (($server['REQUEST_METHOD'] ?? '') !== 'POST') {
            return [405, ['ok' => false, 'error' => 'metodo']];
        }
        if (!$this->origenPermitido($server)) {
            $this->log('origen_rechazado');
            return [403, ['ok' => false, 'error' => 'origen', 'mensaje' => self::MENSAJE_FALLA]];
        }

        $ahora = ($this->reloj)();
        $ip = $this->ip($server);
        $ipHmac = hash_hmac('sha256', $ip, $this->cfg['secreto_ip']);
        try {
            $desde = $ahora->modify('-1 hour')->format('Y-m-d H:i:s');
            if ($this->repo->contarIntentos($ipHmac, $desde) >= $this->cfg['limite_por_hora']) {
                $this->log('limite');
                return [429, ['ok' => false, 'error' => 'limite', 'mensaje' => 'Recibimos varias solicitudes desde tu conexión. Intenta de nuevo en una hora o escríbenos por WhatsApp al +57 304 683 0354.']];
            }
            $this->repo->registrarIntento($ipHmac, $ahora->format('Y-m-d H:i:s'));
        } catch (\Throwable $e) {
            $this->log('bd_intentos', ['clase' => $e::class]);
            return [500, ['ok' => false, 'error' => 'servidor', 'mensaje' => self::MENSAJE_FALLA]];
        }

        // Trampas para bots: se responde como si todo saliera bien, sin guardar ni enviar nada.
        if (trim((string) ($post['sitio_web'] ?? '')) !== '') {
            $this->log('trampa');
            return [200, ['ok' => true]];
        }
        $inicio = $post['inicio'] ?? '';
        if (!is_string($inicio) || !ctype_digit($inicio) || ($ahora->getTimestamp() * 1000 - (int) $inicio) < self::TIEMPO_MINIMO_S * 1000) {
            $this->log('rapido');
            return [200, ['ok' => true]];
        }

        if ($this->cfg['turnstile_secreto'] !== '') {
            $respuesta = $post['cf-turnstile-response'] ?? '';
            if (!is_string($respuesta) || $respuesta === '' || !($this->verificarTurnstile)($respuesta, $ip)) {
                $this->log('turnstile');
                return [403, ['ok' => false, 'error' => 'antibot', 'mensaje' => 'No pudimos verificar que no eres un robot. Recarga la página e intenta de nuevo.']];
            }
        }

        ['datos' => $d, 'errores' => $errores] = Validador::validar($post);
        if ($errores) {
            return [422, ['ok' => false, 'error' => 'datos', 'campos' => $errores, 'mensaje' => 'Revisa los campos marcados.']];
        }

        $textos = Textos::version($d['version']);
        $etiqueta = Textos::etiqueta($d['version']);
        $autorizaciones = [['tipo' => 'atencion', 'version' => $etiqueta, 'texto' => $textos['atencion'], 'token_sha256' => null, 'token_expira_en' => null]];
        $token = null;
        if ($d['correos']) {
            $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $autorizaciones[] = ['tipo' => 'correos', 'version' => $etiqueta, 'texto' => $textos['correos'],
                'token_sha256' => hash('sha256', $token),
                'token_expira_en' => $ahora->modify('+' . self::VIGENCIA_TOKEN_DIAS . ' days')->format('Y-m-d H:i:s')];
        }
        if ($d['whatsapp']) {
            $autorizaciones[] = ['tipo' => 'whatsapp', 'version' => $etiqueta, 'texto' => $textos['whatsapp'], 'token_sha256' => null, 'token_expira_en' => null];
        }

        try {
            $consultaId = $this->repo->guardarSolicitud([
                'correo' => $d['correo'], 'nombre' => $d['nombre'], 'telefono' => $d['telefono'], 'area' => $d['area'],
                'relato_cifrado' => $d['relato'] !== '' ? $this->cifrado->cifrar($d['relato']) : null,
                'prefiere_whatsapp' => $d['whatsapp'], 'origen' => 'web-diagnostico', 'ahora' => $ahora->format('Y-m-d H:i:s'),
                'ip' => $ip, 'navegador' => mb_substr((string) ($server['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'autorizaciones' => $autorizaciones,
            ]);
        } catch (\Throwable $e) {
            $this->log('bd_guardar', ['clase' => $e::class]);
            return [500, ['ok' => false, 'error' => 'servidor', 'mensaje' => self::MENSAJE_FALLA]];
        }

        $acuse = $this->correo->acuse($d);
        $aviso = $this->correo->aviso($d + [
            'id' => $consultaId,
            'fecha' => $ahora->setTimezone(new DateTimeZone('America/Bogota'))->format('d/m/Y H:i'),
        ]);
        if ($token !== null && !$this->correo->confirmacion(['nombre' => $d['nombre'], 'correo' => $d['correo'], 'token' => $token])) {
            $this->log('correo_confirmacion', ['consulta' => $consultaId]);
        }
        if (!$acuse || !$aviso) {
            $this->log('correo', ['consulta' => $consultaId, 'acuse' => $acuse, 'aviso' => $aviso]);
        }
        try {
            $this->repo->marcarCorreos($consultaId, $acuse, $aviso);
        } catch (\Throwable $e) {
            $this->log('bd_marcar', ['consulta' => $consultaId]);
        }

        return [200, ['ok' => true]];
    }

    /** @return 'confirmada'|'ya_confirmada'|'vencida'|'invalida'|'error' */
    public function confirmar(string $token, array $server): string
    {
        if (!preg_match('/^[A-Za-z0-9_-]{43}$/', $token)) {
            return 'invalida';
        }
        try {
            return $this->repo->confirmarCorreos(hash('sha256', $token), ($this->reloj)()->format('Y-m-d H:i:s'), $this->ip($server));
        } catch (\Throwable $e) {
            $this->log('bd_confirmar', ['clase' => $e::class]);
            return 'error';
        }
    }

    /** Mientras haya clave de prueba, el formulario solo responde a quien la envíe en la cabecera X-Prueba. */
    public function pasaClavePrueba(array $server): bool
    {
        return $this->cfg['clave_prueba'] === '' || hash_equals($this->cfg['clave_prueba'], (string) ($server['HTTP_X_PRUEBA'] ?? ''));
    }

    private function origenPermitido(array $server): bool
    {
        $origen = (string) ($server['HTTP_ORIGIN'] ?? '');
        if ($origen === '' && !empty($server['HTTP_REFERER'])) {
            $p = parse_url((string) $server['HTTP_REFERER']);
            $origen = isset($p['scheme'], $p['host']) ? $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '') : '';
        }
        return in_array($origen, $this->cfg['origenes'], true);
    }

    private function ip(array $server): string
    {
        $cab = $this->cfg['ip_cabecera'];
        $valor = (string) ($server[$cab] ?? $server['REMOTE_ADDR'] ?? '');
        $ip = trim(explode(',', $valor)[0]);
        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : '0.0.0.0';
    }

    private function log(string $codigo, array $ctx = []): void
    {
        ($this->registro)($codigo, $ctx);
    }

    private static function turnstilePorCurl(string $secreto, string $respuesta, string $ip): bool
    {
        $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8,
            CURLOPT_POSTFIELDS => http_build_query(['secret' => $secreto, 'response' => $respuesta, 'remoteip' => $ip]),
        ]);
        $r = curl_exec($ch);
        curl_close($ch);
        $j = is_string($r) ? json_decode($r, true) : null;
        return is_array($j) && ($j['success'] ?? false) === true;
    }
}
