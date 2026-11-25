<?php
declare(strict_types=1);

namespace Tegia\Formulario;

/** Valida y normaliza los campos del formulario. Nunca confía en el navegador. */
final class Validador
{
    public const RELATO_MAX = 4000;

    /**
     * @param array<string,mixed> $post
     * @return array{datos:array{nombre:string,correo:string,telefono:string,area:string,relato:string,
     *   atencion:bool,correos:bool,whatsapp:bool,version:string},errores:array<string,string>}
     */
    public static function validar(array $post): array
    {
        $errores = [];
        $txt = static fn(string $k): string => is_string($post[$k] ?? null) ? self::limpiar($post[$k]) : '';
        $si = static fn(string $k): bool => in_array($post[$k] ?? null, ['1', 'on', 'true', 'si', 'sí'], true);

        $nombre = $txt('nombre');
        $len = mb_strlen($nombre);
        if ($len < 2 || $len > 120 || !preg_match('/\p{L}/u', $nombre)) {
            $errores['nombre'] = 'Escribe tu nombre completo.';
        }

        $correo = mb_strtolower($txt('correo'));
        if (strlen($correo) > 254 || filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
            $errores['correo'] = 'Escribe un correo válido, por ejemplo tu@correo.com.';
        }

        $telefono = preg_replace('/[^\d+]/', '', $txt('telefono')) ?? '';
        $digitos = strlen(preg_replace('/\D/', '', $telefono) ?? '');
        if ($digitos < 7 || $digitos > 15 || substr_count($telefono, '+') > 1 || (str_contains($telefono, '+') && $telefono[0] !== '+')) {
            $errores['telefono'] = 'Escribe un teléfono válido, con indicativo si no es de Colombia.';
        }

        $area = $txt('area');
        if (!in_array($area, Textos::AREAS, true)) {
            $errores['area'] = 'Elige el área de tu consulta.';
        }

        $relato = is_string($post['relato'] ?? null) ? self::limpiar($post['relato'], true) : '';
        if (mb_strlen($relato) > self::RELATO_MAX) {
            $errores['relato'] = 'El relato puede tener hasta ' . self::RELATO_MAX . ' caracteres.';
        }

        $atencion = $si('autoriza_atencion');
        if (!$atencion) {
            $errores['autoriza_atencion'] = 'Para atender tu consulta necesitamos tu autorización.';
        }

        $version = $txt('version_textos');
        if (!Textos::existe($version)) {
            $errores['version_textos'] = 'Recarga la página e intenta de nuevo.';
        }

        return [
            'datos' => [
                'nombre' => $nombre, 'correo' => $correo, 'telefono' => $telefono, 'area' => $area, 'relato' => $relato,
                'atencion' => $atencion, 'correos' => $si('autoriza_correos'), 'whatsapp' => $si('autoriza_whatsapp'),
                'version' => $version,
            ],
            'errores' => $errores,
        ];
    }

    /** Quita caracteres de control y espacios sobrantes. Con $multilinea conserva saltos de línea. */
    private static function limpiar(string $v, bool $multilinea = false): string
    {
        if (!mb_check_encoding($v, 'UTF-8')) {
            return '';
        }
        $v = str_replace(["\r\n", "\r"], "\n", $v);
        $v = preg_replace($multilinea ? '/[^\P{C}\n\t]/u' : '/\p{C}/u', '', $v) ?? '';
        if (!$multilinea) {
            $v = preg_replace('/\s+/u', ' ', $v) ?? '';
        } else {
            $v = preg_replace("/\n{3,}/", "\n\n", $v) ?? '';
        }
        return trim($v);
    }
}
