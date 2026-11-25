<?php
declare(strict_types=1);

namespace Tegia\Formulario;

/**
 * Arma y envía los correos del formulario por la API de SendGrid.
 * Todos son de servicio (no comerciales) y van sin seguimiento de aperturas ni clics,
 * para que SendGrid no reescriba el enlace de confirmación.
 */
final class Correo
{
    /** @var callable(string,array<int,string>,string):array{0:int,1:string} */
    private $transporte;

    /**
     * @param array{sendgrid_clave:string,remitente:string,remitente_nombre:string,responder_a:string,
     *   aviso_para:string,url_confirmar:string,url_politica:string,telefono:string,whatsapp:string} $cfg
     */
    public function __construct(private array $cfg, ?callable $transporte = null)
    {
        $this->transporte = $transporte ?? [self::class, 'enviarPorCurl'];
    }

    /** @param array{nombre:string,correo:string,area:string} $d */
    public function acuse(array $d): bool
    {
        $nombre = self::primerNombre($d['nombre']);
        $texto = "Hola {$nombre}:\n\n"
            . "Recibimos tu solicitud de diagnóstico jurídico sobre {$d['area']}. Un abogado de nuestro equipo la revisará y te responderá en menos de 24 horas.\n\n"
            . "Si quieres agregar algo, responde a este correo o escríbenos por WhatsApp al {$this->cfg['telefono']}.\n\n"
            . "Lo que nos cuentes está protegido por el secreto profesional.\n\n"
            . "Equipo Tegia Legal\n\n" . $this->pieTexto('Recibes este correo porque enviaste una solicitud en tegialegal.com. No es un correo comercial.');
        $html = $this->html(
            '<p>Hola ' . self::e($nombre) . ':</p>'
            . '<p>Recibimos tu solicitud de diagnóstico jurídico sobre <strong>' . self::e($d['area']) . '</strong>. Un abogado de nuestro equipo la revisará y te responderá en menos de 24 horas.</p>'
            . '<p>Si quieres agregar algo, responde a este correo o escríbenos por WhatsApp al ' . self::e($this->cfg['telefono']) . '.</p>'
            . '<p>Lo que nos cuentes está protegido por el secreto profesional.</p>'
            . '<p>Equipo Tegia Legal</p>',
            'Recibes este correo porque enviaste una solicitud en tegialegal.com. No es un correo comercial.'
        );
        return $this->enviar($d['correo'], $d['nombre'], 'Recibimos tu solicitud — Tegia Legal', $texto, $html, $this->cfg['responder_a'], 'formulario-acuse');
    }

    /**
     * Aviso a la firma. Incluye el relato (decisión A de la propuesta).
     * @param array{id:int,nombre:string,correo:string,telefono:string,area:string,relato:string,correos:bool,whatsapp:bool,fecha:string} $d
     */
    public function aviso(array $d): bool
    {
        $si = static fn(bool $b): string => $b ? 'Sí' : 'No';
        $relato = $d['relato'] !== '' ? $d['relato'] : '(No escribió relato)';
        $filas = [
            'Consulta' => '#' . $d['id'] . ' · ' . $d['fecha'] . ' (hora de Colombia)',
            'Nombre' => $d['nombre'],
            'Correo' => $d['correo'],
            'Teléfono / WhatsApp' => $d['telefono'],
            'Área' => $d['area'],
            'Prefiere WhatsApp' => $si($d['whatsapp']),
            'Autorizó correos comerciales' => $si($d['correos']) . ($d['correos'] ? ' (pendiente de doble confirmación)' : ''),
        ];
        $texto = "Nueva consulta desde el formulario de diagnóstico.\n\n";
        $tabla = '';
        foreach ($filas as $k => $v) {
            $texto .= "{$k}: {$v}\n";
            $tabla .= '<tr><td style="padding:4px 12px 4px 0;color:#3d4543;white-space:nowrap">' . self::e($k) . '</td><td style="padding:4px 0">' . self::e($v) . '</td></tr>';
        }
        $texto .= "\nRelato:\n{$relato}\n\nResponder a este correo le escribe directamente a la persona. Información confidencial: secreto profesional y Ley 1581 de 2012.\n";
        $html = $this->html(
            '<p>Nueva consulta desde el formulario de diagnóstico.</p><table style="border-collapse:collapse;font-size:14px">' . $tabla . '</table>'
            . '<p style="margin-top:16px"><strong>Relato</strong></p><p style="white-space:pre-wrap;background:#f5f8f6;padding:12px;border-radius:8px">' . self::e($relato) . '</p>'
            . '<p style="font-size:12px;color:#919191">Responder a este correo le escribe directamente a la persona.</p>',
            'Información confidencial: secreto profesional y Ley 1581 de 2012. No reenviar fuera de la firma.'
        );
        $asunto = 'Nueva consulta: ' . $d['area'] . ' — ' . $d['nombre'];
        return $this->enviar($this->cfg['aviso_para'], 'Tegia Legal', $asunto, $texto, $html, $d['correo'], 'formulario-aviso');
    }

    /** @param array{nombre:string,correo:string,token:string} $d */
    public function confirmacion(array $d): bool
    {
        $nombre = self::primerNombre($d['nombre']);
        $enlace = $this->cfg['url_confirmar'] . '?t=' . rawurlencode($d['token']);
        $texto = "Hola {$nombre}:\n\n"
            . "Marcaste la casilla para recibir por correo contenidos jurídicos, invitaciones y novedades de Tegia Legal. Para confirmar que este correo es tuyo, abre este enlace:\n\n{$enlace}\n\n"
            . "Si no fuiste tú o cambiaste de opinión, ignora este mensaje: no te enviaremos correos comerciales. Tu consulta se atiende igual.\n\n"
            . "El enlace vence en 30 días.\n\n" . $this->pieTexto('Recibes este correo porque marcaste la casilla de correos en tegialegal.com.');
        $html = $this->html(
            '<p>Hola ' . self::e($nombre) . ':</p>'
            . '<p>Marcaste la casilla para recibir por correo contenidos jurídicos, invitaciones y novedades de Tegia Legal. Para confirmar que este correo es tuyo, usa este botón:</p>'
            . '<p><a href="' . self::e($enlace) . '" style="display:inline-block;background:#2a5c44;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:10px;font-weight:600">Confirmar mi suscripción</a></p>'
            . '<p>Si no fuiste tú o cambiaste de opinión, ignora este mensaje: no te enviaremos correos comerciales. Tu consulta se atiende igual.</p>'
            . '<p style="font-size:12px;color:#919191">El enlace vence en 30 días.</p>',
            'Recibes este correo porque marcaste la casilla de correos en tegialegal.com.'
        );
        return $this->enviar($d['correo'], $d['nombre'], 'Confirma tu suscripción — Tegia Legal', $texto, $html, $this->cfg['responder_a'], 'formulario-confirmacion');
    }

    private function enviar(string $para, string $paraNombre, string $asunto, string $texto, string $html, string $responderA, string $categoria): bool
    {
        $cuerpo = [
            'personalizations' => [['to' => [['email' => $para, 'name' => $paraNombre]]]],
            'from' => ['email' => $this->cfg['remitente'], 'name' => $this->cfg['remitente_nombre']],
            'reply_to' => ['email' => $responderA],
            'subject' => $asunto,
            'content' => [['type' => 'text/plain', 'value' => $texto], ['type' => 'text/html', 'value' => $html]],
            'categories' => [$categoria],
            'tracking_settings' => [
                'click_tracking' => ['enable' => false, 'enable_text' => false],
                'open_tracking' => ['enable' => false],
                'subscription_tracking' => ['enable' => false],
            ],
        ];
        try {
            [$estado] = ($this->transporte)(
                'https://api.sendgrid.com/v3/mail/send',
                ['Authorization: Bearer ' . $this->cfg['sendgrid_clave'], 'Content-Type: application/json'],
                json_encode($cuerpo, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            );
        } catch (\Throwable) {
            return false;
        }
        return $estado === 202;
    }

    /** @param array<int,string> $cabeceras @return array{0:int,1:string} */
    public static function enviarPorCurl(string $url, array $cabeceras, string $cuerpo): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => $cuerpo, CURLOPT_HTTPHEADER => $cabeceras,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $respuesta = curl_exec($ch);
        $estado = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return [$estado, is_string($respuesta) ? $respuesta : ''];
    }

    private function pieTexto(string $motivo): string
    {
        return "—\nTegia Legal · {$this->cfg['remitente']} · {$this->cfg['telefono']}\n"
            . "Responsable del tratamiento: María Camila Ramírez Gómez. Política de tratamiento de datos: {$this->cfg['url_politica']}\n{$motivo}\n";
    }

    private function html(string $contenido, string $motivo): string
    {
        return '<!doctype html><html lang="es"><body style="margin:0;padding:24px;background:#ffffff;font-family:Montserrat,Arial,sans-serif;color:#11150f;font-size:15px;line-height:1.55">'
            . '<div style="max-width:560px;margin:0 auto">' . $contenido
            . '<hr style="border:0;border-top:1px solid #e5ece8;margin:24px 0">'
            . '<p style="font-size:12px;color:#919191">Tegia Legal · ' . self::e($this->cfg['remitente']) . ' · ' . self::e($this->cfg['telefono']) . '<br>'
            . 'Responsable del tratamiento: María Camila Ramírez Gómez. <a href="' . self::e($this->cfg['url_politica']) . '" style="color:#2a5c44">Política de tratamiento de datos</a><br>'
            . self::e($motivo) . '</p></div></body></html>';
    }

    private static function primerNombre(string $nombre): string
    {
        return explode(' ', trim($nombre))[0] ?: $nombre;
    }

    public static function e(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
