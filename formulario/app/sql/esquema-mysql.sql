-- Formulario seguro de diagnóstico — Tegia Legal
-- Base de datos propia, separada de la de WordPress. Fechas en UTC.
-- El usuario de la aplicación solo necesita SELECT, INSERT y UPDATE sobre estas tablas.

CREATE TABLE IF NOT EXISTS contactos (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  correo         VARCHAR(254) NOT NULL,
  nombre         VARCHAR(120) NOT NULL,
  telefono       VARCHAR(30)  NOT NULL,
  creado_en      DATETIME     NOT NULL,
  actualizado_en DATETIME     NOT NULL,
  UNIQUE KEY uq_contactos_correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS consultas (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  contacto_id       BIGINT UNSIGNED NOT NULL,
  area              VARCHAR(60)  NOT NULL,
  relato_cifrado    TEXT         NULL,
  prefiere_whatsapp TINYINT(1)   NOT NULL DEFAULT 0,
  estado            VARCHAR(20)  NOT NULL DEFAULT 'nueva',
  origen            VARCHAR(60)  NOT NULL,
  creada_en         DATETIME     NOT NULL,
  acuse_enviado     TINYINT(1)   NULL,
  aviso_enviado     TINYINT(1)   NULL,
  KEY ix_consultas_contacto (contacto_id),
  KEY ix_consultas_creada (creada_en),
  CONSTRAINT fk_consultas_contacto FOREIGN KEY (contacto_id) REFERENCES contactos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Prueba de cada autorización (Ley 1581, numeral 7 de la política).
CREATE TABLE IF NOT EXISTS autorizaciones (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  contacto_id     BIGINT UNSIGNED NOT NULL,
  consulta_id     BIGINT UNSIGNED NOT NULL,
  tipo            VARCHAR(20)  NOT NULL,          -- atencion | correos | whatsapp
  version_texto   VARCHAR(40)  NOT NULL,
  texto           TEXT         NOT NULL,
  texto_sha256    CHAR(64)     NOT NULL,
  otorgada_en     DATETIME     NOT NULL,
  ip              VARCHAR(45)  NOT NULL,
  navegador       VARCHAR(255) NOT NULL,
  canal           VARCHAR(60)  NOT NULL,
  token_sha256    CHAR(64)     NULL,              -- solo correos: doble confirmación
  token_expira_en DATETIME     NULL,
  confirmada_en   DATETIME     NULL,
  ip_confirmacion VARCHAR(45)  NULL,
  revocada_en     DATETIME     NULL,
  UNIQUE KEY uq_autorizaciones_token (token_sha256),
  KEY ix_autorizaciones_contacto (contacto_id),
  CONSTRAINT fk_autorizaciones_contacto FOREIGN KEY (contacto_id) REFERENCES contactos (id),
  CONSTRAINT fk_autorizaciones_consulta FOREIGN KEY (consulta_id) REFERENCES consultas (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Límite de envíos por IP. Guarda un HMAC de la IP, no la IP.
CREATE TABLE IF NOT EXISTS intentos (
  id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip_hmac   CHAR(64) NOT NULL,
  creado_en DATETIME NOT NULL,
  KEY ix_intentos_ip (ip_hmac, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
