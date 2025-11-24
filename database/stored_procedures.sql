-- ============================================
-- PORTAL DE TRABAJO UTP - STORED PROCEDURES
-- ============================================
-- Descripción: Procedimientos almacenados del sistema
-- ============================================

USE portal_trabajo_utp;

-- Eliminar procedimiento si existe
DROP PROCEDURE IF EXISTS sp_buscar_ofertas_por_rol;

-- ============================================
-- PROCEDIMIENTO: sp_buscar_ofertas_por_rol
-- Descripción: Búsqueda de ofertas por rol/tecnología
-- Parámetros: 
--   - rol_busqueda: Término de búsqueda (titulo, descripcion, habilidad)
-- Retorna: Lista de ofertas que coincidan con la búsqueda
-- ============================================

DELIMITER $$

CREATE PROCEDURE sp_buscar_ofertas_por_rol(IN rol_busqueda VARCHAR(200))
BEGIN
    SELECT 
        o.id_oferta,
        o.titulo,
        o.descripcion,
        o.modalidad,
        o.tipo_empleo,
        o.nivel_experiencia,
        o.salario_min,
        o.salario_max,
        o.ubicacion,
        o.fecha_limite,
        o.estado,
        e.nombre_comercial AS empresa,
        e.logo AS empresa_logo,
        e.sector,
        GROUP_CONCAT(DISTINCT h.nombre SEPARATOR ', ') AS habilidades,
        o.fecha_publicacion
    FROM ofertas o
    INNER JOIN empresas e ON o.id_empresa = e.id_empresa
    LEFT JOIN ofertas_habilidades oh ON o.id_oferta = oh.id_oferta
    LEFT JOIN habilidades h ON oh.id_habilidad = h.id_habilidad
    WHERE 
        o.estado = 'activa' 
        AND o.fecha_limite >= CURDATE()
        AND (
            o.titulo LIKE CONCAT('%', rol_busqueda, '%')
            OR o.descripcion LIKE CONCAT('%', rol_busqueda, '%')
            OR h.nombre LIKE CONCAT('%', rol_busqueda, '%')
            OR e.nombre_comercial LIKE CONCAT('%', rol_busqueda, '%')
        )
    GROUP BY o.id_oferta
    ORDER BY o.fecha_publicacion DESC;
END$$

DELIMITER ;

-- ============================================
-- VERIFICACIÓN
-- ============================================
SELECT 'PROCEDIMIENTO ALMACENADO CREADO EXITOSAMENTE' AS mensaje;

-- Ejemplo de uso:
-- CALL sp_buscar_ofertas_por_rol('PHP');
-- CALL sp_buscar_ofertas_por_rol('Desarrollador');
-- CALL sp_buscar_ofertas_por_rol('remoto');