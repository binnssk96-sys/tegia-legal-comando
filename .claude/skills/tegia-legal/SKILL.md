---
name: tegia-legal
description: Contexto, decisiones y reglas del proyecto Tegia Legal (firma de abogados en Medellín, Colombia) — email marketing con SendGrid, rediseño seguro del sitio web tegialegal.com, CRM/centro de comandos, WhatsApp, calendario de campañas y plan por sprints. Úsala siempre que el trabajo toque Tegia Legal, SendGrid, sus campañas, su sitio web, su CRM o sus agentes programados.
---

# Proyecto Tegia Legal — centro de comandos

## Reglas fijas
- **Idioma:** todo en español — conversación, documentos, tableros y correos. Los correos van en español neutro/colombiano, tono profesional y cercano de firma jurídica.
- **Datos sensibles:** los visitantes pueden describir casos legales. Trátalo como información confidencial (secreto profesional + Ley 1581 de 2012). Nunca la pongas en logs, URLs, artefactos públicos ni en servicios externos no aprobados.
- **Mantener esta skill al día sin preguntar:** cada decisión, cambio o dato nuevo del proyecto se agrega aquí y se sube al repositorio GitHub `tegia-legal-comando` (cuenta del usuario) sin pedir confirmación. La copia en claude.ai no se puede actualizar desde Claude Code; el usuario la sube a mano cuando quiera.
- **Secretos:** las API keys se guardan solo con el formulario seguro (`request_credential`), nunca en el chat, variables de entorno ni archivos. Si un script exige `SENDGRID_API_KEY`, usar el valor `proxy-injected`; el proxy inyecta la real.
- **Consentimiento antes de enviar:** solo se envía marketing a personas con autorización previa, expresa e informada (Ley 1581/2012, Decreto 1377/2013, Ley 2300/2023 "dejen de fregar"). Todo correo lleva enlace de baja y datos de la firma.
- **Listas de la Cámara de Comercio / RUES: NO cargarlas en SendGrid para envíos masivos.** Motivos: (1) la SIC exige autorización previa para fines comerciales y ha sancionado marketing sin consentimiento (p. ej. Movistar, 670 millones COP); (2) la Política de Uso Aceptable de SendGrid prohíbe listas compradas/extraídas/de terceros — suspensión de la cuenta y daño a la reputación del dominio tegialegal.com; (3) riesgo reputacional y disciplinario para una firma de abogados. Uso aceptable: investigación de mercado, segmentación, y prospección 1-a-1 personalizada a correos corporativos genéricos de empresas, con baja inmediata — validar con el equipo jurídico de Tegia antes.

## Estado de SendGrid (verificado 2026-11-21)
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
- Acceso a Hostinger: **Hostinger Connector** (MCP) conectado en claude.ai. Operaciones útiles: `dns_records_list/validate/update` (usar `overwrite: true` solo con el name+type exacto), `mail_orders_list` (orden O210e860082f9a3a, plan Free Business Email, 100 cupos), `mail_mailboxes_list`, `mail_aliases_create-alias`. Buzones: alexandra@, info@, isabella@, mariaramirez@.
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
- WordPress 7.1.5 desde 2026-11-23 (antes 6.8.10), PHP 8.4, tema Astra 4.11.5 + Elementor 3.30, hosting compartido Hostinger cuenta `u533514420`, raíz `/home/u533514420/domains/tegialegal.com/public_html`. SSL de por vida + CDN activos; caché apagada.
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
