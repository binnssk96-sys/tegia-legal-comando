# Formulario seguro de diagnóstico

Programa PHP aparte de WordPress que recibe "Cuéntanos tu caso", guarda la consulta y la prueba de cada autorización, y envía por SendGrid el acuse, el aviso interno y la doble confirmación. Diseño y decisiones: `docs/propuesta-formulario-seguro.md`.

## Carpetas

| En el repo | En Hostinger | Qué es |
|---|---|---|
| `publico/` | `public_html/formulario/` | Lo único accesible desde internet: `enviar.php` (JSON) y `confirmar.php` (página) |
| `app/` | `domains/tegialegal.com/formulario-app/` | Código, configuración y registro. **Fuera de `public_html`** |
| `app/sql/esquema-mysql.sql` | — | Tablas de la base de datos propia |
| `cliente/formulario.js` | Página del sitio | Envía el formulario `#form-diagnostico` sin recargar |
| `pruebas/pruebas.php` | — | Pruebas: `php formulario/pruebas/pruebas.php` |

`config.php` (copia de `app/config.ejemplo.php` con las claves) **nunca** va al repositorio; está en `.gitignore`.

## Decisiones aplicadas (25/11/2026)

- Aviso interno **con relato** a `info@tegialegal.com` (opción A), con respuesta directa al visitante.
- Turnstile listo pero **apagado** hasta tener la clave de Cloudflare (`turnstile_secreto` vacío); mientras tanto, campo trampa, tiempo mínimo de 3 s, límite de 5 envíos por IP por hora y verificación de origen.
- Textos de las casillas: versión `casillas-2026-11-23` + política `v2.1-borrador`. Si cambian, agregar una versión nueva en `app/src/Textos.php` y en `cliente/formulario.js`.

## Modo de prueba

Mientras `clave_prueba` tenga valor, `enviar.php` responde 404 a todo el que no mande la cabecera `X-Prueba` con esa clave. Así se puede probar en el servidor real sin que nadie lo use. Para publicar: vaciar `clave_prueba`, cuando el equipo jurídico apruebe los textos.

## Puesta en marcha en Hostinger

1. Crear la base de datos y su usuario (hPanel o conector) y cargar `app/sql/esquema-mysql.sql`.
2. Crear en SendGrid una clave **restringida a Mail Send**.
3. Generar `clave_cifrado` y `secreto_ip` (comandos en `config.ejemplo.php`) y guardar copia de `clave_cifrado` en un lugar seguro: sin ella los relatos no se pueden leer.
4. Subir `app/` a `formulario-app/` con su `config.php`, y `publico/` a `public_html/formulario/`.
5. Verificar en el servidor qué cabecera trae la IP real del visitante (la CDN puede cambiarla) y ajustar `ip_cabecera`.
6. Probar con `X-Prueba`: envío completo, correos, doble confirmación, límite.

## Leer un relato

Los relatos están cifrados en la base de datos. Para leer uno fuera del correo de aviso, con acceso al servidor:

```php
$c = new Tegia\Formulario\Cifrado($claveCifrado);
echo $c->descifrar($filaConsulta['relato_cifrado']);
```

## Pendiente

- Proceso de borrado según los plazos del numeral 14 de la política (cuando se aprueben).
- Panel para consultar casos (CRM, Sprint 2).
