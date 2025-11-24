-- ============================================
-- PORTAL DE TRABAJO UTP - SCHEMA DATABASE
-- ============================================
-- Descripción: Creación de todas las tablas del sistema
-- Autor: Sistema Portal UTP
-- Fecha: 2024
-- Base de datos: portal_trabajo_utp
-- Codificación: utf8mb4_unicode_ci
-- ============================================

-- Usar la base de datos
USE portal_trabajo_utp;

-- ============================================
-- TABLA: estudiantes
-- Descripción: Almacena información de estudiantes UTP
-- ============================================
CREATE TABLE estudiantes (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    correo_utp VARCHAR(100) UNIQUE NOT NULL COMMENT 'Correo institucional @utp.ac.pa',
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    carrera VARCHAR(100) COMMENT 'Carrera académica del estudiante',
    descripcion_perfil TEXT COMMENT 'Biografía o descripción personal',
    foto_perfil VARCHAR(255) DEFAULT 'placeholder-profile.png',
    cv_ruta VARCHAR(255) COMMENT 'Nombre del archivo PDF del CV',
    cv_fecha_subida DATETIME COMMENT 'Fecha de última actualización del CV',
    cv_hash VARCHAR(64) COMMENT 'Hash SHA256 del archivo para integridad',
    estado_cv ENUM('activo', 'eliminado', 'expirado') DEFAULT 'activo' COMMENT 'Estado del CV (se elimina físicamente después de 12 meses)',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE COMMENT 'Indica si el estudiante está activo',
    INDEX idx_correo (correo_utp),
    INDEX idx_carrera (carrera),
    INDEX idx_estado_cv (estado_cv)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de estudiantes registrados';

-- ============================================
-- TABLA: administradores
-- Descripción: Usuarios administrativos del sistema
-- ============================================
CREATE TABLE administradores (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    correo_utp VARCHAR(100) UNIQUE NOT NULL COMMENT 'Correo institucional del administrador',
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada con bcrypt',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_correo (correo_utp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de administradores del sistema';

-- ============================================
-- TABLA: empresas
-- Descripción: Empresas que publican ofertas laborales
-- ============================================
CREATE TABLE empresas (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nombre_legal VARCHAR(200) NOT NULL COMMENT 'Razón social de la empresa',
    nombre_comercial VARCHAR(200) COMMENT 'Nombre comercial o marca',
    logo VARCHAR(255) DEFAULT 'placeholder-logo.png' COMMENT 'Nombre del archivo de logo',
    descripcion TEXT COMMENT 'Descripción de la empresa',
    sector VARCHAR(100) COMMENT 'Sector industrial (ej: FinTech, Banca, Tecnología)',
    sitio_web VARCHAR(255) COMMENT 'URL del sitio web corporativo',
    telefono VARCHAR(20),
    email_contacto VARCHAR(100),
    direccion TEXT,
    estado ENUM('activa', 'inactiva', 'bloqueada') DEFAULT 'activa',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_sector (sector)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de empresas registradas';

-- ============================================
-- TABLA: ofertas
-- Descripción: Ofertas de trabajo publicadas por empresas
-- ============================================
CREATE TABLE ofertas (
    id_oferta INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    titulo VARCHAR(200) NOT NULL COMMENT 'Título del puesto',
    descripcion TEXT NOT NULL COMMENT 'Descripción detallada del puesto',
    requisitos TEXT COMMENT 'Requisitos del candidato',
    responsabilidades TEXT COMMENT 'Responsabilidades del puesto',
    beneficios TEXT COMMENT 'Beneficios ofrecidos',
    salario_min DECIMAL(10,2) COMMENT 'Salario mínimo mensual en USD',
    salario_max DECIMAL(10,2) COMMENT 'Salario máximo mensual en USD',
    modalidad ENUM('remoto', 'presencial', 'hibrido') NOT NULL,
    tipo_empleo ENUM('tiempo_completo', 'medio_tiempo', 'practicas', 'temporal') NOT NULL,
    nivel_experiencia ENUM('junior', 'mid', 'senior') NOT NULL,
    ubicacion VARCHAR(200) COMMENT 'Ubicación física del trabajo',
    fecha_limite DATE NOT NULL COMMENT 'Fecha límite para postulaciones',
    estado ENUM('activa', 'cerrada', 'tomada') DEFAULT 'activa',
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE,
    INDEX idx_estado (estado),
    INDEX idx_fecha_limite (fecha_limite),
    INDEX idx_empresa (id_empresa),
    INDEX idx_modalidad (modalidad),
    INDEX idx_tipo_empleo (tipo_empleo),
    INDEX idx_nivel (nivel_experiencia),
    FULLTEXT INDEX idx_busqueda (titulo, descripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de ofertas laborales';

-- ============================================
-- TABLA: habilidades
-- Descripción: Catálogo de habilidades técnicas y blandas
-- ============================================
CREATE TABLE habilidades (
    id_habilidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) UNIQUE NOT NULL COMMENT 'Nombre de la habilidad',
    categoria ENUM('tecnica', 'blanda', 'lenguaje', 'herramienta') NOT NULL,
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de habilidades';

-- ============================================
-- TABLA: ofertas_habilidades (Relación N:M)
-- Descripción: Asociación entre ofertas y habilidades requeridas
-- ============================================
CREATE TABLE ofertas_habilidades (
    id_oferta INT NOT NULL,
    id_habilidad INT NOT NULL,
    PRIMARY KEY (id_oferta, id_habilidad),
    FOREIGN KEY (id_oferta) REFERENCES ofertas(id_oferta) ON DELETE CASCADE,
    FOREIGN KEY (id_habilidad) REFERENCES habilidades(id_habilidad) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla intermedia ofertas-habilidades';

-- ============================================
-- TABLA: postulaciones
-- Descripción: Postulaciones de estudiantes a ofertas
-- ============================================
CREATE TABLE postulaciones (
    id_postulacion INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_oferta INT NOT NULL,
    estado ENUM('en_revision', 'aceptada', 'rechazada') DEFAULT 'en_revision',
    fecha_postulacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_postulacion (id_estudiante, id_oferta) COMMENT 'Un estudiante solo puede postularse una vez por oferta',
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante) ON DELETE CASCADE,
    FOREIGN KEY (id_oferta) REFERENCES ofertas(id_oferta) ON DELETE CASCADE,
    INDEX idx_estudiante (id_estudiante),
    INDEX idx_oferta (id_oferta),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_postulacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de postulaciones';

-- ============================================
-- TABLA: notificaciones
-- Descripción: Sistema de notificaciones internas
-- ============================================
CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    tipo_usuario ENUM('estudiante', 'admin') NOT NULL COMMENT 'Tipo de destinatario',
    id_usuario INT NOT NULL COMMENT 'ID del estudiante o admin destinatario',
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('postulacion_nueva', 'cambio_estado', 'sistema') NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    id_relacionado INT COMMENT 'ID de la postulación u oferta relacionada',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (tipo_usuario, id_usuario, leida),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de notificaciones internas';

-- ============================================
-- TABLA: bitacora_auditoria
-- Descripción: Registro de auditoría de acciones críticas
-- ============================================
CREATE TABLE bitacora_auditoria (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    tipo_usuario ENUM('estudiante', 'admin') NOT NULL,
    id_usuario INT NOT NULL COMMENT 'ID del usuario que realizó la acción',
    accion VARCHAR(100) NOT NULL COMMENT 'Tipo de acción (login, crear_oferta, cambiar_estado, etc)',
    tabla_afectada VARCHAR(50) COMMENT 'Nombre de la tabla afectada',
    id_registro_afectado INT COMMENT 'ID del registro modificado',
    datos_anteriores JSON COMMENT 'Estado anterior del registro (para UPDATE)',
    datos_nuevos JSON COMMENT 'Estado nuevo del registro',
    ip_address VARCHAR(45) COMMENT 'Dirección IP del usuario',
    user_agent TEXT COMMENT 'Navegador y sistema operativo',
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (tipo_usuario, id_usuario),
    INDEX idx_fecha (fecha_hora),
    INDEX idx_accion (accion),
    INDEX idx_tabla (tabla_afectada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bitácora de auditoría del sistema';

-- ============================================
-- FIN DEL SCHEMA
-- ============================================

-- Mensaje de confirmación
SELECT 'SCHEMA CREADO EXITOSAMENTE - 9 tablas creadas' AS mensaje;