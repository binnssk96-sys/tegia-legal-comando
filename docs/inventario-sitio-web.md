# Inventario del sitio web — tegialegal.com

Fecha: 2026-11-22. Fuente: Hostinger Connector (archivos y configuración del hosting) y lectura pública del sitio.

## Plataforma
| Elemento | Estado |
|---|---|
| Hosting | Hostinger, hosting compartido (cuenta u533514420), sitio creado en 2020 |
| CMS | WordPress 6.8.10 |
| PHP | 8.4 |
| Tema activo | Astra 4.11.5 + Astra Addon |
| Constructor | Elementor 3.30.0 |
| SSL | Activo (certificado de por vida de Hostinger), redirección a HTTPS activa |
| CDN | Hostinger CDN activo |
| Caché del servidor | Apagada (plugin LiteSpeed Cache instalado) |

## Contenido actual de la página de inicio
- Título del sitio: "Tegia Legal – Business Services" (en inglés).
- Menú: Inicio, Nosotros, Servicios, Política de privacidad, Reviews, Contacto. Aparecen además "Home" y "Sample Page" (restos de la instalación).
- Áreas: Derecho Laboral y Seguridad Social, Comercial, Corporativo, Propiedad Intelectual, Servicios Transversales, Mensualidades y Suscripciones.
- Testimonios: 3 (Camila Gómez Bravo, Camilo Maya, Mike Rogers).
- Teléfonos: +57 304 683 0354 y +57 322 580 3897. Dirección: Carrera 43 # 41 sur 16, Edificio Villa Esperanza apto 503.
- Política de privacidad publicada como PDF en la raíz.

## Formulario "Solicita tu diagnóstico jurídico gratuito" (WPForms Lite, formulario 10)
- Campos: Nombre (nombre y apellido), Teléfono, Correo electrónico, Mensaje.
- Etiquetas mezcladas español/inglés ("Name", "First", "Last").
- Antispam: solo campo trampa (honeypot) y token de WPForms. Sin CAPTCHA/Turnstile.
- **Sin casilla de autorización de tratamiento de datos** ni enlace a la política junto al formulario (requisito Ley 1581/2012).
- WPForms Lite no guarda las solicitudes: solo envía un correo. No hay registro del consentimiento.
- El campo Mensaje invita a contar el caso: información confidencial viaja por correo sin control.

## Plugins instalados (19)
Activos en la página pública: Elementor, WPForms Lite, Astra Sites.
Instalados (no se puede confirmar cuáles están activos sin el panel de WordPress):
- **Riesgo alto:** Slider Revolution 6.2.8 (2020, muy desactualizado, historial de vulnerabilidades graves), WP Reset 2.04 (puede borrar el sitio entero; no debe estar en producción), Fusion Core 4.2.3 y Fusion Builder (del tema Avada 6.2.3 de 2020).
- **Sobrantes:** One Click Demo Import, Astra Sites, Sydney Toolbox, Astra Widgets, Header Footer Elementor, The Events Calendar, Contact Form 7 6.1 (segundo plugin de formularios), All-in-One WP Migration, wp-cli-login-server.
- **Útiles:** Akismet, LiteSpeed Cache, Elementor, WPForms Lite, Astra Addon.
- Versiones de mediados de 2025: no se actualizan desde hace más de un año.

## Temas instalados (10)
Astra (activo), Avada 6.2.3, Sydney, Roxima (CSSIgniter) y 6 temas Twenty*. Solo debería quedar Astra y un tema por defecto de respaldo.

## Hallazgos de seguridad
| # | Hallazgo | Gravedad |
|---|---|---|
| 1 | Registros de errores de 2023 descargables por cualquiera (`error_log-2023*.gz` responde 200) | Alta |
| 2 | Plugins/tema abandonados y desactualizados (Slider Revolution 2020, Avada 2020, Fusion) | Alta |
| 3 | WP Reset instalado en producción | Alta |
| 4 | Formulario sin autorización de datos ni registro de consentimiento | Alta (legal) |
| 5 | Sin CAPTCHA en el formulario | Media |
| 6 | Cabeceras de seguridad ausentes: sin HSTS, sin X-Frame-Options, sin X-Content-Type-Options, sin Referrer-Policy; CSP solo `upgrade-insecure-requests` | Media |
| 7 | `X-Powered-By: PHP/8.4.27` expuesto (`expose_php` activo) | Baja |
| 8 | `readme.html` público (revela WordPress) y meta generator con versión | Baja |
| 9 | Archivos sobrantes en la raíz: `default.php`, `default.php.bak`, `.htaccess.bk`, `.htaccess_original`, `wp-config-sample.php` (responde 500) | Baja |
| 10 | Cookies de sesión PHP sin `secure`/`httponly`, `allow_url_fopen` activo | Baja |
| 11 | Bloque de reglas WordPress del `.htaccess` vacío | Por revisar |
| 12 | Caché del servidor apagada (velocidad) | Rendimiento |

## Recomendación
El sitio arrastra restos de al menos cuatro diseños (Avada, Sydney, Roxima, Astra). Para el rediseño conviene **reconstruir limpio** en lugar de remendar:
1. Arreglos rápidos ya (no cambian el diseño): borrar logs y archivos sobrantes, desactivar y borrar WP Reset, Slider Revolution, Avada/Fusion y temas sin uso, actualizar núcleo y plugins, cabeceras de seguridad, `expose_php` off.
2. Rediseño sobre una base mínima: Astra + Elementor (o tema de bloques) con pocos plugins, o un sitio estático/Node a medida desplegado desde el repositorio, con el formulario procesado en el servidor y enviado por SendGrid.
3. Formulario nuevo con casilla de autorización, enlace a la política, Turnstile, registro de consentimiento y almacenamiento cifrado.

## Arreglos rápidos aplicados (2026-11-22)
Aprobados por el usuario. Nada se borró: todo se movió a `public_html/_cuarentena-20261122/` (bloqueada al público con `Require all denied`, verificado 403).

| Cambio | Detalle | Verificación |
|---|---|---|
| Plugins retirados | revslider, wp-reset, fusion-core, fusion-builder → `_cuarentena-20261122/plugins/` | Sitio 200, sin errores PHP |
| Temas retirados | Avada, sydney, Roxima (renombrada `roxima`), twentyseventeen, twentynineteen, twentytwenty, twentytwentythree, twentytwentyfour → `_cuarentena-20261122/themes/`. Quedan astra (activo) y twentytwentyfive | Sitio 200 |
| Archivos sobrantes | 2 error_log-2023*.gz, default.php.bak, .htaccess.bk, .htaccess_original, readme.html → `_cuarentena-20261122/raiz/` | 404 público |
| `.htaccess` | Bloqueo de logs/copias/readme/license/wp-config-sample/default.php, XML-RPC bloqueado, sin listado de directorios, cabeceras HSTS, nosniff, X-Frame-Options, Referrer-Policy, Permissions-Policy, sin X-Powered-By. Copia del original: `sitio/htaccess-original-2026-11-22` | Cabeceras presentes; xmlrpc 403 |
| PHP | expose_php Off, session.cookie_secure On, session.cookie_httponly On, session.use_strict_mode On | Sin X-Powered-By |

Verificado después: portada, wp-login.php y página 454 responden 200 sin errores; formulario 10 presente; REST API y PDF de política siguen accesibles.

**Cómo deshacer:** mover las carpetas de vuelta desde `_cuarentena-20261122/` con el administrador de archivos de Hostinger; restaurar `.htaccess` desde `sitio/htaccess-original-2026-11-22`.

## Arreglo aplicado (2026-11-24): enumeración de usuarios
Hallazgo: `/?author=1` mostraba en el título el nombre público del administrador de WordPress, que es un correo personal de Gmail. Con `wp-login.php` abierto, eso facilita ataques de fuerza bruta.

| Cambio | Detalle | Verificación |
|---|---|---|
| `.htaccess` | Regla en el bloque "Tegia Seguridad": `?author=N` fuera de `/wp-admin` → 403. Se partió del archivo del servidor (las secciones de LiteSpeed habían cambiado frente a la copia del repo). Copia previa: `sitio/htaccess-antes-2026-11-24`; versión actual: `sitio/htaccess-seguro` | Portada 200, `?author=1` y `?author=2` 403, wp-admin 302, el correo ya no aparece. Se limpió la caché del servidor y de la CDN |
| Usuario | **Pendiente del usuario:** en wp-admin → Usuarios → Perfil, cambiar "Mostrar este nombre públicamente" para que no sea el correo | — |

**Cómo deshacer:** subir `sitio/htaccess-antes-2026-11-24` como `public_html/.htaccess`.

Otros datos de la revisión del 24/11: `wp-login.php` sigue sin CAPTCHA, límite de intentos ni verificación en dos pasos (resolver en el sitio nuevo o con MFA); el certificado es de Let's Encrypt (vence 25/01/2027, renovación automática); no hay registros CAA (opcional: `0 issue "letsencrypt.org"`).

**Pendiente:**
- WordPress no aparece en el instalador de Hostinger (la detección automática no lo encontró), así que las actualizaciones de núcleo y plugins no se pueden hacer por API: hacerlas desde wp-admin o reinstalar limpio en el rediseño.
- No hubo copia de seguridad de hPanel antes de los cambios (la API no tiene esa función); la cuarentena hace de respaldo de lo retirado. La base de datos no se tocó.
- Borrar definitivamente `_cuarentena-20261122/` cuando el sitio lleve unas semanas estable.
