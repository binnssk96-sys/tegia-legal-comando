# Plan inicial por sprints — Tegia Legal

Sprints de 2 semanas. Cada sprint cierra con algo usable.

## Sprint 0 — Fundaciones (semana 1)
- Comprar SendGrid Email API Essentials; verificar cupo con `GET /v3/user/credits` y enviar correo de prueba.
- Crear repositorio GitHub del proyecto (código, skills, plantillas) para que todo persista entre sesiones.
- Acceso a Hostinger; inventario del sitio actual (CMS, plugins, formularios, DNS, correo).
- DMARC en `p=none`; Google Postmaster Tools.
- Redactar con el equipo jurídico: aviso de privacidad, política de tratamiento de datos, texto de autorización para marketing y WhatsApp.

## Sprint 1 — Sitio web seguro
- Terminar el rediseño (partir del artefacto existente), en español.
- Formularios seguros: anti-bot, validación servidor, cifrado, casilla de autorización, registro de consentimiento.
- Endurecimiento: HTTPS/HSTS, CSP, WAF, MFA, backups.
- Conectar formulario → base de datos de contactos → correo de bienvenida/acuse por SendGrid.

## Sprint 2 — Base de datos + flujos de seguimiento
- Base de datos de contactos/casos con campo de consentimiento y origen.
- Workflow 1 (describió su caso): acuse inmediato → asignación interna → ofrecer llamada/WhatsApp.
- Workflow 2 (solo suscripción): bienvenida → pedir más info → contenido útil → oferta de llamada.
- Event Webhook de SendGrid → tabla de eventos (entregas, aperturas, clics, rebotes, quejas, bajas).
- Inicio del warm-up con contactos existentes y autorizados.

## Sprint 3 — Tableros
- Calendario de comunicaciones (campañas, temas, fechas, estado).
- Tablero de estadísticas con sección de insights y recomendaciones.
- Calendario editorial hasta fin de año (temas legales por área de práctica, fechas clave colombianas).

## Sprint 4 — WhatsApp + CRM
- WhatsApp Business API con plantillas aprobadas.
- CRM: pipeline de prospectos → consulta → cliente → caso.

## Sprint 5+ — Centralización
- Bandejas de correo por cliente/caso; control de casos, plazos, entregables; recordatorios.

## Agentes y "turnos" (tareas programadas)
Las sesiones de Claude no están siempre encendidas; los "agentes" son tareas programadas que arrancan una sesión, hacen su trabajo y dejan resultados. Respetar el límite de uso de la cuenta: pocas tareas, bien definidas.
- **Diario (mañana):** resumen de nuevos contactos/formularios y estado del warm-up.
- **Diario (tarde):** revisión de entregabilidad (rebotes, quejas) con alertas.
- **Semanal:** informe de campañas con insights y propuestas de mejora.
- **Semanal:** borradores de la próxima campaña en español para aprobación humana.
- Regla: ningún agente envía campañas sin aprobación humana explícita.
