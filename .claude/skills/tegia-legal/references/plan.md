# Plan por sprints — Tegia Legal

Sprints de 2 semanas. Cada sprint cierra con algo usable.
Estados: ✅ hecho · 🔄 en curso · ⏳ pendiente · 👤 depende del usuario
Última actualización: 2026-11-22

## Sprint 0 — Fundaciones (21 nov – 5 dic 2026) 🔄
- ✅ SendGrid conectado por API (key en el entorno Default).
- ✅ Plan Essentials comprado (cuenta `paid`, cupo 500.000/mes).
- ✅ Correo de prueba en español enviado desde info@ → llegó a bandeja Principal de Gmail.
- ✅ Hostinger confirmado (sitio, DNS y correo) y conectado con el Hostinger Connector.
- ✅ Alias dmarc@tegialegal.com → info@ creado.
- ✅ DMARC fase 1 publicado (`p=none` con reportes).
- ✅ Repositorio GitHub `binnssk96-sys/tegia-legal-comando` creado, con la skill.
- ✅ Skill `tegia-legal` subida a claude.ai (versión del 21 nov, desactualizada).
- ✅ Inventario del sitio actual (docs/inventario-sitio-web.md).
- ✅ Arreglos rápidos de seguridad aplicados (ver docs/inventario-sitio-web.md).
- 🔄 WordPress actualizado a 7.1.5 (2026-11-23); faltan plugins (Elementor 3.30.0, WPForms 1.9.6.1, CF7 6.1) y tema Astra 4.11.5.
- ✅ Borrador de textos legales listo para el equipo jurídico: https://claude.ai/artifact/FNwSxVtXXqDgkTspCu7YC5
- ✅ Google Postmaster Tools verificado por el usuario (2026-11-23).
- 👤 Rotar la API key de SendGrid que se pegó en el chat.
- ✅ Plan Essentials de 50.000 confirmado por el usuario; la API muestra cupo de 500.000 (aclarar).
- 👤 Revisar si el reenvío activo de mariaramirez@ es intencional.
- 👤 Actualizar la skill en claude.ai con el último .zip.

## Sprint 1 — Sitio web seguro (7 – 18 dic 2026) ⏳
- Terminar el rediseño (partir del artefacto https://claude.ai/artifact/9HkGQ9Dqy342r9FWsn868E), en español.
- Formularios seguros: anti-bot, validación servidor, cifrado, casilla de autorización, registro de consentimiento.
- Endurecimiento: HTTPS/HSTS, CSP, WAF, MFA, backups.
- Conectar formulario → base de datos de contactos → correo de bienvenida/acuse por SendGrid.
- Revisar reportes DMARC (desde ~13 dic) y decidir paso a `quarantine`.

## Sprint 2 — Base de datos + flujos de seguimiento (ene 2027) ⏳
- Base de datos de contactos/casos con campo de consentimiento y origen.
- Workflow 1 (describió su caso): acuse inmediato → asignación interna → ofrecer llamada/WhatsApp.
- Workflow 2 (solo suscripción): bienvenida → pedir más info → contenido útil → oferta de llamada.
- Event Webhook de SendGrid → tabla de eventos.
- Inicio del warm-up con contactos existentes y autorizados.

## Sprint 3 — Tableros (ene–feb 2027) ⏳
- Calendario de comunicaciones.
- Tablero de estadísticas con insights y recomendaciones.
- Calendario editorial 2027.

## Sprint 4 — WhatsApp + CRM (feb 2027) ⏳
- WhatsApp Business API con plantillas aprobadas.
- CRM: prospecto → consulta → cliente → caso.

## Sprint 5+ — Centralización (mar 2027 en adelante) ⏳
- Bandejas de correo por cliente/caso; control de casos, plazos, entregables; recordatorios.

## Agentes y "turnos" (tareas programadas)
Las sesiones de Claude no están siempre encendidas; los "agentes" son tareas programadas que arrancan una sesión, hacen su trabajo y dejan resultados. Pocas tareas, bien definidas.
- Diario (mañana): resumen de nuevos contactos/formularios y estado del warm-up.
- Diario (tarde): revisión de entregabilidad (rebotes, quejas) con alertas.
- Semanal: informe de campañas con insights y propuestas de mejora.
- Semanal: borradores de la próxima campaña en español para aprobación humana.
- Regla: ningún agente envía campañas sin aprobación humana explícita.

## Tablero del proyecto
Artefacto "Tablero Tegia Legal" en claude.ai con el estado de este plan (enlace en SKILL.md).
