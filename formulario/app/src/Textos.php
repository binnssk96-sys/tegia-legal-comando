<?php
declare(strict_types=1);

namespace Tegia\Formulario;

/**
 * Textos de las casillas tal como los ve el visitante. El servidor guarda SU copia
 * del texto (no la que mande el navegador) junto con la versión.
 * Al cambiar un texto: crear una versión nueva y dejar la anterior para las pruebas guardadas.
 */
final class Textos
{
    public const VERSION_ACTUAL = 'casillas-2026-11-23';

    private const VERSIONES = [
        'casillas-2026-11-23' => [
            'politica' => 'v2.1-borrador',
            'atencion' => 'Autorizo a Tegia Legal a tratar mis datos, incluida la descripción de mi caso, para analizar mi consulta y contactarme para responderla. Leí el aviso de privacidad y la política de tratamiento de datos.',
            'correos'  => 'Quiero recibir por correo contenidos jurídicos, invitaciones y novedades de Tegia Legal. Puedo darme de baja cuando quiera.',
            'whatsapp' => 'Prefiero que me contacten por WhatsApp para el seguimiento de mi consulta.',
        ],
    ];

    public const AREAS = [
        'Laboral y seguridad social',
        'Corporativo',
        'Comercial y contratos',
        'Propiedad intelectual',
        'Tecnología, datos y criptoactivos',
        'Cobro de cartera',
        'Proyecto puntual',
        'Consultoría y conceptos',
        'Plan Esencial',
        'Plan Crecimiento',
        'Plan Estratégico',
        'Otro',
    ];

    public static function existe(string $version): bool
    {
        return isset(self::VERSIONES[$version]);
    }

    /** @return array{politica:string,atencion:string,correos:string,whatsapp:string} */
    public static function version(string $version): array
    {
        if (!self::existe($version)) {
            throw new \InvalidArgumentException('Versión de textos desconocida');
        }
        return self::VERSIONES[$version];
    }

    /** Identificador que se guarda en la prueba: casillas + política. */
    public static function etiqueta(string $version): string
    {
        return $version . '/' . self::VERSIONES[$version]['politica'];
    }
}
