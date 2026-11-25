# Propuesta: formulario seguro de diagnóstico

**Estado (25/11/2026):** aprobada y **construida** en `formulario/` (64 pruebas pasan). Decisiones tomadas por defecto: aviso con relato a info@ (A), Turnstile listo pero apagado hasta tener la clave, campo trampa + límites activos. Aún no está en el servidor ni publicada.

El formulario "Cuéntanos tu caso" se procesa en un programa PHP pequeño, aparte de WordPress, en el mismo hosting de Hostinger. Guarda cada consulta y la prueba de la autorización en una base de datos propia, con el relato cifrado. Además, envía por SendGrid el acuse al visitante y el aviso interno a la firma.

## Por qué aparte de WordPress

- El formulario actual (WPForms, id 10) no tiene casilla de autorización, ni CAPTCHA, ni guarda las entradas.
- Un programa de un solo propósito expone mucho menos que un plugin. No depende de las actualizaciones de WordPress, y funciona igual en el sitio actual y en el nuevo.
- Hostinger ya tiene PHP 8.4, MySQL y la extensión de cifrado `sodium`. No hay que contratar nada.

## Cómo funciona

1. El visitante llena el formulario y marca las casillas. Ninguna viene marcada.
2. El navegador envía los datos a `tegialegal.com/formulario/enviar.php`.
3. El servidor:
   - verifica la protección contra bots;
   - verifica que la casilla de autorización esté marcada;
   - valida cada campo: largo, formato del correo y del teléfono, y área permitida.
4. Guarda en la base de datos:
   - el contacto;
   - la consulta, con el relato cifrado;
   - la prueba de cada autorización: texto exacto y versión, fecha y hora, IP, navegador y canal.
5. Envía por SendGrid:
   - **Acuse al visitante.** Confirma que se recibió la consulta y que responderemos en menos de 24 horas. **Nunca incluye el relato.**
   - **Aviso interno** a la firma (ver decisión 1).
   - **Confirmación de suscripción**, solo si marcó la casilla de correos. Entra a la lista comercial únicamente cuando hace clic en el enlace (doble confirmación, numeral 7 de la política).
6. El visitante ve "Recibimos tu solicitud". Si algo falla, ve un mensaje claro con los teléfonos de contacto. No pierde lo que escribió.

Los correos de acuse y de confirmación son de servicio, no comerciales. Por eso pueden salir a cualquier hora: el horario de la Ley 2300 aplica solo a los envíos comerciales.

## Seguridad

| Riesgo | Medida |
|---|---|
| Bots y spam | Cloudflare Turnstile (decisión 2) + campo trampa invisible + tiempo mínimo de llenado + límite de 5 envíos por IP por hora |
| Datos falsos o maliciosos | Validación en el servidor; todo se guarda con consultas preparadas (sin inyección SQL) y se muestra escapado |
| Robo de la base de datos | Relato cifrado con XChaCha20-Poly1305 (`sodium`); la clave vive fuera de `public_html` |
| Acceso al programa | Carpeta `formulario/` solo expone `enviar.php` y `confirmar.php`; configuración, claves y código interno fuera de `public_html` |
| Base de datos de WordPress comprometida | Base de datos **separada** de la de WordPress, con un usuario que solo puede insertar y leer sus tablas |
| Clave de SendGrid en el servidor | Clave **restringida solo a envío de correo**, distinta de la que usa Claude; guardada fuera de `public_html` |
| Registros con datos del caso | Los logs solo guardan errores técnicos, nunca nombre, correo ni relato |
| Envíos desde otros sitios | Solo se aceptan envíos con origen `tegialegal.com` |

## Datos que se guardan

| Tabla | Qué guarda |
|---|---|
| `contactos` | Nombre, correo, teléfono, fecha de creación |
| `consultas` | Área, relato cifrado, preferencia de WhatsApp, fecha, estado (nueva / en revisión / respondida) |
| `autorizaciones` | Tipo (atención / correos comerciales / WhatsApp), texto aceptado y versión de la política, fecha y hora, IP, navegador, canal, token y fecha de confirmación, fecha de revocación |

Un proceso programado (cron) borra o anonimiza los datos vencidos según el numeral 14 de la política, cuando el equipo jurídico confirme los plazos.

## Decisiones que necesito de ti

1. **¿El aviso interno lleva el relato?**
   - **A (recomendada para empezar):** el aviso a la firma trae nombre, contacto, área y relato, como hoy con el correo. Es lo más simple; el relato queda en el buzón de la firma.
   - **B:** el aviso solo dice "nueva consulta de X, área Y" y el relato se lee en un panel privado con contraseña. Más seguro, pero hay que construir el panel (es parte del CRM del Sprint 2).
2. **Protección contra bots:** Cloudflare Turnstile es gratis, no muestra acertijos y no rastrea para publicidad. Necesita que crees una cuenta gratuita de Cloudflare y un "widget" para `tegialegal.com` (te guío). La alternativa sin cuenta es solo campo trampa + límites, que frena bots simples pero no los dirigidos.
3. **¿A qué buzón llega el aviso interno?** `info@tegialegal.com`, `mariaramirez@tegialegal.com` u otro.
4. **¿Lo ponemos en el sitio actual antes del rediseño?** Recomiendo construirlo ya y, en cuanto el equipo jurídico apruebe los textos de las casillas, reemplazar el WPForms actual (que hoy recoge datos sin autorización). Luego se reutiliza tal cual en el sitio nuevo.

## Plan de construcción

1. Construir y probar el programa en el entorno de Claude (PHP 8.3 con `sodium`), con pruebas automáticas.
2. Crear en Hostinger la base de datos y su usuario, y la clave restringida de SendGrid.
3. Subir a una ruta oculta y probar de punta a punta: envío, correos, doble confirmación, límites.
4. Publicar en el sitio cuando los textos legales estén aprobados.
