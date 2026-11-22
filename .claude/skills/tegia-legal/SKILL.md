---
name: tegia-legal
description: Contexto, decisiones y reglas del proyecto Tegia Legal (firma de abogados en Medellín, Colombia) — email marketing con SendGrid, rediseño seguro del sitio web tegialegal.com, CRM/centro de comandos, WhatsApp, calendario de campañas y plan por sprints. Úsala siempre que el trabajo toque Tegia Legal, SendGrid, sus campañas, su sitio web, su CRM o sus agentes programados.
---

# Proyecto Tegia Legal — centro de comandos

## Reglas fijas
- **Idioma:** todo en español — conversación, documentos, tableros y correos. Los correos van en español neutro/colombiano, tono profesional y cercano de firma jurídica.
- **Datos sensibles:** los visitantes pueden describir casos legales. Trátalo como información confidencial (secreto profesional + Ley 1581 de 2012). Nunca la pongas en logs, URLs, artefactos públicos ni en servicios externos no aprobados.
- **Mantener esta skill al día sin preguntar:** cada decisión, cambio o dato nuevo del proyecto se agrega aquí y se sube al repositorio GitHub `tegia-legal-comando` (cuenta del usuario) sin pedir confirmación. La copia en claude.ai no se puede actualizar desde Claude Code; el usuario la sube a mano (última subida: 2026-11-23). Cuando haya cambios importantes, generar `tegia-legal-skill.zip` y enviárselo.
- **Secretos:** las API keys se guardan solo con el formulario seguro (`request_credential`), nunca en el chat, variables de entorno ni archivos. Si un script exige `SENDGRID_API_KEY`, usar el valor `proxy-injected`; el proxy inyecta la real.
- **Consentimiento antes de enviar:** solo se envía marketing a personas con autorización previa, expresa e informada (Ley 1581/2012, Decreto 1377/2013, Ley 2300/2023 "dejen de fregar"). Todo correo lleva enlace de baja y datos de la firma.
- **Listas de la Cámara de Comercio / RUES: NO cargarlas en SendGrid para envíos masivos.** Motivos: (1) la SIC exige autorización previa para fines comerciales y ha sancionado marketing sin consentimiento (p. ej. Movistar, 670 millones COP); (2) la Política de Uso Aceptable de SendGrid prohíbe listas compradas/extraídas/de terceros — suspensión de la cuenta y daño a la reputación del dominio tegialegal.com; (3) riesgo reputacional y disciplinario para una firma de abogados. Uso aceptable: investigación de mercado, segmentación, y prospección 1-a-1 personalizada a correos corporativos genéricos de empresas, con baja inmediata — validar con el equipo jurídico de Tegia antes.

## Estado de SendGrid (verificado 2026-11-21)
- API keys (2026-11-23): "API KEY 2V" es la nueva y la que usa el proxy (guardada con el formulario seguro). Las keys antiguas ("Claude API Key", expuesta en el chat, y "1234 Test") fueron borradas.
- API key conectada y guardada en el entorno **Default** (host `api.sendgrid.com`), acceso total.
- Remitente verificado: `info@tegialegal.com` ("Tegia Legal"), reply-to `mariaramirez@tegialegal.com`.
- Dominio autenticado: `tegialegal.com` (CNAME `em7166`, DKIM s1/s2) — válido.
- 2026-11-21: plan **Essentials comprado** — cuenta `paid`, cupo **500.000 correos/mes** (reset mensual el día 1, límite duro), reputación 100. Primer correo de prueba en español aceptado (202) desde info@tegialegal.com.
- Antes de la compra: cuenta `free` con 0 créditos ("Maximum credits exceeded"); SendGrid reemplazó el plan gratis por una prueba de 60 días.
- 2026-11-23: el usuario confirma que compró Essentials de 50.000/mes, pero `GET /v3/user/credits` reporta total 500.000. Aclarar con SendGrid; para planificar, usar 50.000.
- Aunque el cupo sea grande, el volumen real lo dicta el warm-up y la lista con consentimiento.
- Prueba: `GET /v3/user/credits` muestra el cupo; `POST /v3/mail/send` envía.

## DNS de tegialegal.com (leído 2026-11-21 vía dns.google, permitido en el entorno Default)
- DNS y hosting en **Hostinger confirmado** (NS `ns1/ns2.dns-parking.com`; A 213.210.57.241, 147.93.77.86).
- Correo de la firma: **Hostinger Mail** (MX mx1/mx2.hostinger.com; DKIM `hostingermail-a` activo).
- SPF: `v=spf1 include:_spf.mail.hostinger.com ~all` — no hace falta agregar SendGrid (usa el subdominio `em7166` para el return-path, alineación relajada).
- DMARC original: `v=DMARC1; p=none` (sin reportes).
- **2026-11-22 aplicado vía Hostinger Connector:** alias `dmarc@tegialegal.com` → buzón `info@` (alias id AAb156dd02ebb604ba3e4a6033c487) y DMARC fase 1 `v=DMARC1; p=none; rua=mailto:dmarc@tegialegal.com; fo=1; adkim=r; aspf=r` (TTL 3600), verificado publicado.
- Acceso a Hostinger: **Hostinger Connector** (MCP) conectado en claude.ai. Operaciones útiles: `dns_records_list/validate/update` (usar `overwrite: true` solo con el name+type exacto), `mail_orders_list` (orden O210e860082f9a3a, plan Free Business Email, 100 cupos), `mail_mailboxes_list`, `mail_aliases_create-alias`. Buzones: alexandra@, info@, isabella@, mariaramirez@ (reenvía a tegialegal@gmail.com con copia desde 2026-08-18; el usuario confirmó que es cuenta de la firma — debe tener verificación en dos pasos).
- Fases: 2–4 semanas en `none` revisando reportes → `p=quarantine; pct=25` → `pct=100` → `p=reject`. Solo avanzar si todos los remitentes legítimos (Hostinger Mail, SendGrid, formularios web) pasan DKIM/SPF alineado.
- Google Postmaster Tools: TXT `google-site-verification=e_vUEq_GzkbRbn6jjxOGGk-JJUZVO3flU5r2Q8iXOGM` en `@` junto al SPF (2026-11-22). Al agregar TXT en `@` usar `overwrite: false` para no borrar el SPF.
- Consultas DNS: el contenedor no resuelve DNS directo; usar `https://dns.google/resolve?name=...&type=...`.

## Plan recomendado de SendGrid
- **Inicio: Email API Essentials (~US$19,95/mes, 50.000 correos/mes, IP compartida).** El volumen inicial con lista de opt-in será bajo; una IP dedicada no se puede calentar bien con poco volumen.
- **No comprar Marketing Campaigns:** el editor, listas, automatizaciones y tableros los construimos nosotros (CRM propio) y enviamos por la Email API.
- **Pasar a Pro (~US$89,95/mes, 100.000, IP dedicada)** solo cuando se superen ~50–100 mil envíos/mes sostenidos; ahí se hace warm-up de la IP dedicada.
- Desde mar-2026 Essentials/Pro tienen techo duro de 10× el cupo del plan.

## Warm-up (dominio en IP compartida)
1. Semana 1–2: solo transaccionales y correos a contactos que ya conocen a Tegia (clientes, ex-clientes, contactos que dieron autorización). 20–50/día.
2. Semana 3–4: duplicar cada pocos días si rebote < 2 % y quejas < 0,1 %.
3. Monitorear: rebotes, spam reports, aperturas, Google Postmaster Tools para gmail.com.
4. Añadir DMARC (`p=none` → `quarantine`) y un subdominio de envío de marketing (p. ej. `news.tegialegal.com`) separado del transaccional.

## Sitio web
- URL: https://www.tegialegal.com/ — hosting probablemente Hostinger (por confirmar; sin acceso aún).
- Borrador de rediseño existente: https://claude.ai/artifact/9HkGQ9Dqy342r9FWsn868E
- Requisitos de seguridad para formularios de casos: HTTPS/HSTS, CSP, protección anti-bot (Turnstile/reCAPTCHA), validación del lado servidor, cifrado en reposo de los relatos de casos, mínimos datos en el formulario, aviso de privacidad + casilla de autorización de tratamiento (no premarcada), registro de la autorización (fecha, IP, texto aceptado), backups, WAF, MFA en Hostinger, actualizaciones del CMS.
- Captura: formulario "Cuéntanos tu caso" y suscripción ligera. Flujo de seguimiento: si describe su caso → acuse + agendar llamada/WhatsApp; si no → bienvenida, pedir más información, contenido útil y opción de que Tegia llame o escriba por WhatsApp.

## Inventario del sitio (2026-11-22)
- WordPress 7.1.5 desde 2026-11-23 (antes 6.8.10), PHP 8.4, tema Astra 4.14.3 + Elementor 4.4.1 (WPForms 2.0.5.1, CF7 6.2.5; actualizados 2026-11-23), hosting compartido Hostinger cuenta `u533514420`, raíz `/home/u533514420/domains/tegialegal.com/public_html`. SSL de por vida + CDN activos; caché apagada.
- Formulario WPForms Lite id 10 ("Solicita tu diagnóstico jurídico gratuito"): sin casilla de autorización, sin CAPTCHA, no guarda entradas.
- Riesgos: logs de errores 2023 públicos, Slider Revolution 6.2.8 y Avada 6.2.3 (2020), WP Reset en producción, 10 temas y 19 plugins, cabeceras de seguridad ausentes.
- Informe completo: `docs/inventario-sitio-web.md`. Recomendación: arreglos rápidos ya + reconstrucción limpia en el rediseño.
- 2026-11-22 arreglos rápidos aplicados: plugins revslider/wp-reset/fusion-* y 8 temas sin uso movidos a `public_html/_cuarentena-20261122/` (bloqueada), `.htaccess` con cabeceras de seguridad (original en `sitio/htaccess-original-2026-11-22`), PHP expose_php Off y cookies seguras. Detalle y cómo deshacer en `docs/inventario-sitio-web.md`.
- Cambios de archivos: `hosting_files_generate-upload-url` da una clave temporal (~6 h) para la API de File Browser en `srv1282-files.hstgr.io` (permitido en Default): `POST api/resources/<ruta>?override=true` sube, `PATCH ...?action=rename&destination=` mueve, `DELETE` borra. Nunca guardar esas claves en archivos del repo. Nombres con bytes no UTF-8 se codifican como %C2%xx.
- WordPress no aparece en `wordpress_installations_list` ni tras `wordpress_installations_detect`: las operaciones `wordpress_*` no están disponibles.
- Lectura pública del sitio permitida en el entorno Default (`tegialegal.com`, `www.tegialegal.com`).

## Textos legales (borrador 2026-11-22)
- Documento "Textos legales de datos personales — Tegia Legal": https://claude.ai/artifact/FNwSxVtXXqDgkTspCu7YC5 (Claude Docs). Casillas del formulario (atención obligatoria + marketing opcional + WhatsApp opcional), aviso de privacidad, doble confirmación, pie de correos, reglas Ley 2300, cambios a la política, prueba del consentimiento y preguntas abiertas.
- Política vigente: PDF "Politica de privacidad TEGIA LEGAL.pdf" en la raíz del sitio; responsable "Tegia Legal Business Services", domicilio Carrera 43 # 41 sur 16, Envigado. Discrepancias abiertas: NIT vs C.C., Carrera 43/44, Envigado/Medellín.
- No publicar ninguno de estos textos hasta que el equipo jurídico los apruebe.

## Rediseño del sitio (Sprint 1)
- Decisiones del usuario (2026-11-23): **tuteo** ("tú") en sitio y correos; **Tegia Sport fuera por ahora**; plataforma **WordPress limpio** con pocos plugins y el formulario procesado aparte.
- Borrador v1 (original, compartido con enlace): https://claude.ai/artifact/9HkGQ9Dqy342r9FWsn868E — copia en `sitio/borrador/v1-original.html`.
- Borrador v2 (privado): https://claude.ai/artifact/4V68YxvdqiYT5ZSSkyxK3c — fuente `sitio/borrador/v2.html`. Cambios: tuteo, sin selector Legal/Sport, tarjeta del hero sin formulario (lleva a #diagnostico), formulario único con correo + 3 casillas (autorización obligatoria, marketing, WhatsApp) y aviso de datos sensibles, mapa reemplazado por tarjeta con enlace a Google Maps, enlaces legales en el pie.
- 2026-11-23 ronda 3 (mismo enlace del v2): logo vectorial exacto del manual de marca (`sitio/marca/logo-tegia.svg` y `logo-tegia-blanco.svg`, extraídos de la pág. 1 del PDF; no redibujar), foto de portada `sitio/marca/IMG_3231-2.jpg` con capa oscura, sin "Envigado" ni dirección escrita (ubicación solo por enlace https://maps.app.goo.gl/XEhF6GnhKdsEjoMaA), teléfonos +57 304 683 0354 y +57 304 647 6186 (el 322 580 3897 ya no se usa), sin menciones al Consejo Superior de la Judicatura, barra superior con íconos (teléfono, WhatsApp, correo, ubicación, redes) con tooltip en lugar del número, "Registro Nacional de Abogados" → "Acompañamiento personalizado", sección de alianzas eliminada, estilo más sobrio (negro/gris del manual, verde #2a5c44 solo en acentos; efectos hover conservados).
- 2026-11-23 ronda 4: sección de servicios reemplazada por la propuesta del usuario (3 bloques): 01 modalidades (Abogado de cabecera [recomendado], Proyectos puntuales, Consultoría y conceptos, Cobro de cartera); 02 cinco áreas (Laboral y seguridad social con Ley 2466 de 2025, Corporativo, Comercial y contratos, Propiedad intelectual, Tecnología, datos y criptoactivos [nuevo]); 03 planes mensuales Esencial / Crecimiento [más elegido] / Estratégico, sin precios; cierre "¿No sabes por dónde empezar?". Los enlaces de cada tarjeta preseleccionan el área en el formulario. Se quitó el bloque de charlas/capacitaciones (queda en `sitio/borrador/v1-original.html`).
- 2026-11-23 ronda 5 (reestructura para venta): orden Portada → 01 Servicios (modalidades) → 02 Planes → 03 Áreas (pestañas, una a la vez) → Reseñas (carrusel infinito en ambos sentidos, autoplay 5 s que se pausa al pasar el mouse) → Nosotros (compacto) → Diagnóstico (único formulario) → Pie con contacto y horario (`#contacto`). Eliminado: tarjeta del hero, intro duplicada de servicios, bloque "¿No sabes por dónde empezar?", sección Contacto y la tarjeta de mapa. Garantías unificadas en la portada; "24 horas hábiles" solo en Diagnóstico. Menú: Servicios, Planes, Reseñas, Nosotros, Contacto + botón "Diagnóstico gratuito". Las anclas se desplazan con JS dejando la sección bajo el menú (centrada si cabe). Altura en escritorio: 8.283 px → ~6.150 px.
- 2026-11-23 ronda 6: portada moderna (`.hx`): titular (sin la etiqueta "Firma de abogados · Derecho empresarial", quitada a pedido del usuario) "No creemos en el destino…" (se quitó "Somos tu aliado…") + 2 botones redondeados; foto más oscura y desaturada; tarjeta de vidrio esmerilado "Por qué Tegia" (ícono del caballo del manual `sitio/marca/icono-caballo-blanco.svg` + 3 garantías + lema) colocada a propósito sobre la cara de la persona de la foto (right 7vw, top 14 %); indicador de scroll; en <1100 px la tarjeta pasa debajo del texto. Se quitaron los rótulos numerados (01 —, 02 —, 03 —).
- 2026-11-23 ronda 7: fotos del usuario en `sitio/marca/fotos/` (foto-8 mujer de chaqueta roja de espaldas → portada dividida: texto sobre negro a la izquierda, foto desde 48 % a la derecha, tarjeta "Por qué Tegia" abajo a la derecha sin tapar el titular; foto-11 equipo con manos juntas → Nosotros; foto-12 apretón de manos → bajo las garantías del Diagnóstico; foto-9 NO usar: muestra el logo de Tegia Sport y un informe de la FIFA; foto-10 = la anterior de portada). Logos de clientes para Reseñas: REIKVET (Camila Gómez Bravo), Insetech (Camilo Maya), Excel Barbell (Mike Rogers, confirmado 2026-11-23); logos en `sitio/marca/clientes/` (recortados), mostrados siempre a color en cada reseña (decisión del usuario).
- 2026-11-23 ronda 8: flechas del carrusel a los lados de las reseñas; espacio entre secciones 104 → 72 px (56 px en celular); escala general `html{zoom:.9}` desde 1024 px de ancho (la portada compensa su alto). Alto de página en escritorio ≈ 5.050 px.
- 2026-11-23: panel "Tecnología, datos y criptoactivos" sin la etiqueta "Nuevo" y con estilo tecnológico (`.area-tech`): fondo casi negro con cuadrícula y brillo verde, línea de escaneo animada, bloque JSON decorativo en la esquina (solo escritorio), tipografía JetBrains Mono en rótulos, subtítulos numerados 01/02, verde neón #7fd1a8 solo en este panel.
- Manual de marca: tipografía Montserrat; paleta principal negro #000000 y gris #3d4543; secundaria verde #2a5c44 y gris #919191 (la principal debe predominar); logo mínimo 200 px de ancho; no cambiar tipografía, proporción, colores ni aplicar efectos.
- El diseño y los textos NO están aprobados: iterar con el usuario. Por confirmar: "respuesta en 24 horas", "Registro Nacional de Abogados", testimonios, horario, alianzas (c.e.r., Proyección Contable).

## Correo personal en Gmail
- `tegialegal@gmail.com` recibe el reenvío de mariaramirez@ y usaba "Enviar como" mariaramirez@tegialegal.com. Desde 2026 falla con `535 5.7.8 Authentication failed`. Solución: en Gmail → Cuentas → Enviar como → editar → SMTP `smtp.hostinger.com`, puerto 465 SSL, usuario `mariaramirez@tegialegal.com` y contraseña del buzón (o restablecerla en hPanel). No usar SendGrid para el correo personal.

## Arquitectura objetivo (centro de comandos)
- Base de datos de contactos/casos (con origen y prueba del consentimiento por contacto).
- CRM con workflows de correo que envían por SendGrid.
- Calendario de comunicaciones; tablero de estadísticas con insights (qué funciona, qué no, cómo mejorar) alimentado por el Event Webhook de SendGrid.
- WhatsApp Business API (Meta Cloud API o Twilio) con plantillas aprobadas y opt-in.
- Bandejas de correo por cliente/caso, control de casos, plazos y entregables.

## Plan de trabajo por sprints (2 semanas c/u)
Ver `references/plan.md` (fuente de verdad del estado de cada tarea).

## Tablero del proyecto
- Artefacto "Tablero Tegia Legal": https://claude.ai/artifact/HAa4j3zxfWYFmgweHTu2W1 (tipo Data).
- Datos: `tablero/tareas.json` en el repo (id, sprint, nombre_sprint, fechas_sprint, area, tarea, estado [hecho|en_curso|pendiente|usuario], responsable, fecha). Página: `tablero/index.html`.
- Para actualizar: editar `tareas.json` (y `plan.md`), subirlo como asset (`Artifact` publish con `url` del tablero, `asset: true`), y `ArtifactData update` en `datasets/tareas` con el nuevo `source.url` y `updated`. Hacerlo sin preguntar cada vez que cambie el estado de una tarea.

## Pendientes de decisión del usuario
- Confirmar hosting (Hostinger) y dar acceso.
- Comprar Essentials.
- Dónde vivirá el CRM (app propia + base de datos administrada vs. herramienta existente) y repositorio GitHub del proyecto.
- Proveedor de correo de Tegia (Google Workspace / Microsoft 365 / Hostinger Mail) para centralizar bandejas.
- Proveedor de WhatsApp Business.
