# Traspaso del proyecto Tegia Legal a otra cuenta de Claude

Fecha del paquete: 24 de noviembre de 2026. Todo el proyecto vive en el repositorio privado de GitHub **`binnssk96-sys/tegia-legal-comando`**; este paquete es una copia completa de ese repositorio más esta guía.

## 1. Qué contiene el paquete

| Carpeta / archivo | Qué es |
|---|---|
| `.claude/skills/tegia-legal/` | La skill del proyecto: contexto, reglas, decisiones, estado de cada sistema y plan por sprints (`references/plan.md`). Es la "memoria" que le permite a Claude continuar. |
| `tegia-legal-skill.zip` | La misma skill empaquetada para subirla en claude.ai. |
| `docs/TRASPASO.md` | Esta guía. |
| `docs/inventario-sitio-web.md` | Inventario del sitio actual, arreglos de seguridad aplicados y cómo deshacerlos. |
| `sitio/borrador/v2.html` | **Borrador vigente del sitio nuevo** (página completa, se abre en cualquier navegador). |
| `sitio/borrador/v1-original.html` | Borrador original, como referencia. |
| `sitio/marca/` | Logo vectorial exacto del manual (color y blanco), ícono del caballo, fotos (`fotos/`) y logos de clientes (`clientes/`). |
| `sitio/htaccess-*` | `.htaccess` seguro aplicado en Hostinger y copia del original para deshacer. |
| `tablero/` | Datos del tablero del proyecto (`tareas.json`) y su página (`index-publicado.html`). |
| `tegia-legal-comando.bundle` | Historial completo de git (todos los cambios con fecha). Se restaura con `git clone tegia-legal-comando.bundle`. |

No contiene claves, contraseñas ni datos de clientes.

## 2. Cómo ponerlo a andar en la cuenta nueva (en este orden)

1. **Subir la skill.** claude.ai → Configuración → Capacidades → Skills → subir `tegia-legal-skill.zip`.
2. **Conectar GitHub** en la cuenta nueva (claude.ai/connect-github) e instalar la app de Claude con acceso al repositorio `tegia-legal-comando`.
   - Si la cuenta nueva usa **el mismo usuario de GitHub** (`binnssk96-sys`), no hay que mover nada.
   - Si usa **otro usuario de GitHub**: en GitHub → repositorio → Settings → Collaborators, agregar al nuevo usuario; o Settings → Transfer ownership para pasarle el repositorio.
   - Si prefieres no usar el repositorio actual: crea uno vacío en la cuenta nueva y sube el contenido de este paquete.
3. **Abrir una sesión de Claude Code con el repositorio seleccionado.** La skill dentro del repo se carga sola.
4. **Conectar Hostinger** en claude.ai → Conectores → Hostinger (la cuenta de hosting no cambia; solo se autoriza de nuevo).
5. **SendGrid:** la clave guardada en esta cuenta no viaja. En SendGrid (Settings → API Keys) crear una clave nueva con acceso completo y guardarla cuando Claude abra el **formulario seguro** (nunca pegarla en el chat). Después borrar la clave "API KEY 2V". *(Hecho el 24/11/2026: clave "API KEY V3" guardada y "API KEY 2V" borrada.)*
6. **Red del entorno de Claude Code:** cuando Claude lo pida, aprobar estos hosts permitidos: `dns.google`, `tegialegal.com`, `www.tegialegal.com`, `srv1282-files.hstgr.io` (servidor de archivos de Hostinger). `api.sendgrid.com` queda habilitado al guardar la clave.
7. **Artefactos de claude.ai** (son privados de esta cuenta; elegir una opción para cada uno):
   - *Compartirlos* con la cuenta nueva desde el menú **Compartir** de cada uno, o
   - *Recrearlos* en la cuenta nueva a partir de los archivos del paquete (Claude puede hacerlo).

   | Artefacto | Enlace en esta cuenta | Fuente en el paquete |
   |---|---|---|
   | Borrador del sitio (vigente) | https://claude.ai/artifact/4V68YxvdqiYT5ZSSkyxK3c | `sitio/borrador/v2.html` |
   | Borrador original | https://claude.ai/artifact/9HkGQ9Dqy342r9FWsn868E | `sitio/borrador/v1-original.html` |
   | Tablero del proyecto | https://claude.ai/artifact/HAa4j3zxfWYFmgweHTu2W1 | `tablero/` |
   | Textos legales de datos personales | https://claude.ai/artifact/FNwSxVtXXqDgkTspCu7YC5 | **No incluido**: exportarlo desde el menú del documento (Exportar → Markdown o Word) y guardarlo en `docs/`. Incluye un enlace a la "Política de tratamiento (v2.0)": exportarla también. |

   **Recreados en la cuenta nueva (2026-11-24):** borrador del sitio v2 → https://claude.ai/artifact/2XoLv2ppEHhSeNAoAKRc8p · tablero → https://claude.ai/artifact/V3jtWHAopWy5NxGNdNAJnb. El borrador original y los textos legales aún no.

Lo que **no cambia** con el traspaso: la cuenta de SendGrid, Hostinger (sitio, DNS, correo), Google Postmaster Tools y GitHub siguen siendo las mismas.

## 3. Estado actual

**Sprint 0 — Fundaciones: cerrado (18/18).**
- SendGrid Email API Essentials (cupo de 50.000/mes confirmado por el usuario; la API muestra 500.000, pero se planifica con 50.000). Dominio `tegialegal.com` autenticado; correo de prueba llegó a Principal.
- DNS en Hostinger: DMARC `p=none` con reportes a `dmarc@tegialegal.com` (alias de `info@`), SPF de Hostinger, DKIM de Hostinger y SendGrid, verificación de Google Postmaster Tools (verificado).
- Sitio actual (WordPress 7.1.5, Astra 4.14.3, plugins al día): plugins/temas riesgosos en cuarentena `public_html/_cuarentena-20261122/`, cabeceras de seguridad, PHP endurecido.

**Sprint 1 — Sitio web seguro: en curso.**
- Rediseño: borrador `v2.html` con 8 rondas de cambios (tuteo; sin Tegia Sport; logo exacto del manual; foto de portada sin caras; servicios → planes → áreas en pestañas → reseñas con logos de clientes → nosotros → diagnóstico; carrusel infinito; panel tecnológico; zoom 90 % en escritorio). **Textos y diseño aún no aprobados en su totalidad.**
- Plataforma decidida: **WordPress limpio** con pocos plugins y el formulario procesado aparte.
- Textos legales: resuelto quién es el responsable (María Camila Ramírez Gómez, persona natural que actúa bajo la marca Tegia Legal), dirección (Carrera 43 # 41 sur 16, Envigado), dos casillas separadas, doble confirmación y política v2.0 vigente desde el 23/11/2026. Pendientes: criterio del primer WhatsApp frente a la Ley 2300 y plazos de conservación de datos.

## 4. Lo que falta (próximos pasos)

**Del lado de Claude**
1. Seguir iterando el diseño y los textos del sitio hasta su aprobación.
2. Construir el sitio en WordPress limpio con el diseño aprobado, el formulario seguro (casillas, anti-bot, registro de consentimiento, cifrado) y la conexión a SendGrid (acuse al visitante + aviso interno).
3. Completar seguridad: CSP, WAF, MFA en Hostinger, copias de seguridad.
4. Desde ~13 de diciembre: revisar reportes DMARC y decidir paso a `quarantine`.
5. Sprints 2–5: base de datos de contactos/casos, flujos de seguimiento, warm-up, tableros de campañas, WhatsApp Business, CRM, agentes programados (ver `references/plan.md`).

**Del lado del usuario**
1. Reconfigurar "Enviar como" de `mariaramirez@tegialegal.com` en Gmail con `smtp.hostinger.com`, puerto 465 SSL (error 535 de autenticación).
2. ~~Aclarar el cupo con SendGrid~~ — resuelto el 24/11/2026: 50.000/mes.
3. Confirmar en el sitio: "respuesta en 24 horas hábiles", textos de planes ("cancela o cambia de plan"), servicios de criptoactivos y la Ley 2466 de 2025, horario de atención.
4. Precios o "desde $…" de los planes mensuales (recomendado).
5. Borrar la carpeta de cuarentena del sitio actual cuando lleve unas semanas estable.

## 5. Primer mensaje sugerido para la cuenta nueva

> Hola Claude. Este es el proyecto Tegia Legal que vengo trabajando en otra cuenta. Carga la skill `tegia-legal`, lee `docs/TRASPASO.md` y `.claude/skills/tegia-legal/references/plan.md`, y dime en un resumen corto dónde quedamos y qué sigue. Luego pídeme lo que necesites reconectar (SendGrid, Hostinger, accesos de red). Todo en español.
