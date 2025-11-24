<?php
/**
 * Portal de Trabajo UTP - Modelo Auditoria
 * 
 * Gestión de la bitácora de auditoría del sistema.
 * Registra todas las acciones críticas de usuarios (estudiantes y admins).
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

class Auditoria {
    private $db;
    
    /**
     * Constructor
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Registrar una acción en la bitácora
     * 
     * @param array $datos Datos de la acción a registrar
     * @return int ID del registro de auditoría creado
     */
    public function registrar($datos) {
        try {
            $query = "INSERT INTO bitacora_auditoria 
                     (tipo_usuario, id_usuario, accion, tabla_afectada, id_registro_afectado, 
                      datos_anteriores, datos_nuevos, ip_address, user_agent) 
                     VALUES 
                     (:tipo_usuario, :id_usuario, :accion, :tabla_afectada, :id_registro_afectado, 
                      :datos_anteriores, :datos_nuevos, :ip_address, :user_agent)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':tipo_usuario', $datos['tipo_usuario']);
            $stmt->bindParam(':id_usuario', $datos['id_usuario']);
            $stmt->bindParam(':accion', $datos['accion']);
            $stmt->bindParam(':tabla_afectada', $datos['tabla_afectada'] ?? null);
            $stmt->bindParam(':id_registro_afectado', $datos['id_registro_afectado'] ?? null);
            $stmt->bindParam(':datos_anteriores', $datos['datos_anteriores'] ?? null);
            $stmt->bindParam(':datos_nuevos', $datos['datos_nuevos'] ?? null);
            $stmt->bindParam(':ip_address', $datos['ip_address'] ?? null);
            $stmt->bindParam(':user_agent', $datos['user_agent'] ?? null);
            
            $stmt->execute();
            return $this->db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Error al registrar auditoría: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener registros de auditoría con filtros
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $limite Número de registros a retornar
     * @param int $offset Posición inicial
     * @return array Lista de registros de auditoría
     */
    public function obtenerRegistros($filtros = [], $limite = 50, $offset = 0) {
        $query = "SELECT * FROM bitacora_auditoria WHERE 1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['tipo_usuario'])) {
            $query .= " AND tipo_usuario = :tipo_usuario";
            $params[':tipo_usuario'] = $filtros['tipo_usuario'];
        }
        
        if (!empty($filtros['id_usuario'])) {
            $query .= " AND id_usuario = :id_usuario";
            $params[':id_usuario'] = $filtros['id_usuario'];
        }
        
        if (!empty($filtros['accion'])) {
            $query .= " AND accion = :accion";
            $params[':accion'] = $filtros['accion'];
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $query .= " AND fecha_hora >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $query .= " AND fecha_hora <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }
        
        $query .= " ORDER BY fecha_hora DESC LIMIT :limite OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener acciones por usuario
     * 
     * @param string $tipo_usuario Tipo: 'estudiante' o 'admin'
     * @param int $id_usuario ID del usuario
     * @param int $limite Número de registros
     * @return array Lista de acciones del usuario
     */
    public function obtenerPorUsuario($tipo_usuario, $id_usuario, $limite = 20) {
        $query = "SELECT * FROM bitacora_auditoria 
                  WHERE tipo_usuario = :tipo_usuario AND id_usuario = :id_usuario 
                  ORDER BY fecha_hora DESC 
                  LIMIT :limite";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tipo_usuario', $tipo_usuario);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de auditoría
     * 
     * @return array Estadísticas generales
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                  COUNT(*) as total_registros,
                  COUNT(DISTINCT id_usuario) as usuarios_activos,
                  (SELECT COUNT(*) FROM bitacora_auditoria 
                   WHERE DATE(fecha_hora) = CURDATE()) as registros_hoy,
                  (SELECT accion FROM bitacora_auditoria 
                   GROUP BY accion ORDER BY COUNT(*) DESC LIMIT 1) as accion_mas_comun
                  FROM bitacora_auditoria";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Limpiar registros antiguos (más de 12 meses)
     * 
     * @return int Número de registros eliminados
     */
    public function limpiarRegistrosAntiguos() {
        $query = "DELETE FROM bitacora_auditoria 
                  WHERE fecha_hora < DATE_SUB(NOW(), INTERVAL 12 MONTH)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}