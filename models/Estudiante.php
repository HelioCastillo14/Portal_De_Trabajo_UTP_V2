<?php
/**
 * Portal de Trabajo UTP - Modelo Estudiante
 * 
 * Gestión de perfiles de estudiantes UTP.
 * Incluye gestión de CV, perfil y estadísticas de postulaciones.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

class Estudiante {
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
     * Buscar estudiante por ID
     * 
     * @param int $id ID del estudiante
     * @return array|null Datos del estudiante o null si no existe
     */
    public function findById($id) {
        $query = "SELECT * FROM estudiantes WHERE id_estudiante = :id AND activo = TRUE";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar estudiante por correo electrónico
     * 
     * @param string $correo Correo institucional @utp.ac.pa
     * @return array|null Datos del estudiante o null si no existe
     */
    public function findByEmail($correo) {
        $query = "SELECT * FROM estudiantes WHERE correo_utp = :correo AND activo = TRUE";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nuevo estudiante
     * 
     * @param array $datos Datos del estudiante
     * @return int ID del estudiante creado
     */
    public function create($datos) {
        $query = "INSERT INTO estudiantes 
                  (correo_utp, nombres, apellidos, carrera, descripcion_perfil) 
                  VALUES 
                  (:correo, :nombres, :apellidos, :carrera, :descripcion)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindValue(':correo', $datos['correo_utp']);
        $stmt->bindValue(':nombres', $datos['nombres']);
        $stmt->bindValue(':apellidos', $datos['apellidos']);
        $stmt->bindValue(':carrera', $datos['carrera'] ?? null);
        $stmt->bindValue(':descripcion', $datos['descripcion_perfil'] ?? null);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar datos del estudiante
     * 
     * @param int $id ID del estudiante
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $datos) {
        $campos = [];
        $valores = [];
        
        // Construir dinámicamente los campos a actualizar
        foreach ($datos as $campo => $valor) {
            if ($campo !== 'id_estudiante') {
                $campos[] = "$campo = :$campo";
                $valores[":$campo"] = $valor;
            }
        }
        
        $valores[':id'] = $id;
        
        $query = "UPDATE estudiantes SET " . implode(', ', $campos) . " WHERE id_estudiante = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Obtener estudiante con estadísticas de postulaciones
     * 
     * @param int $id ID del estudiante
     * @return array Datos del estudiante con estadísticas
     */
    public function getConPostulaciones($id) {
        $query = "SELECT 
                  e.*,
                  COUNT(p.id_postulacion) as total_postulaciones,
                  SUM(CASE WHEN p.estado = 'aceptada' THEN 1 ELSE 0 END) as postulaciones_aceptadas,
                  SUM(CASE WHEN p.estado = 'rechazada' THEN 1 ELSE 0 END) as postulaciones_rechazadas,
                  SUM(CASE WHEN p.estado = 'en_revision' THEN 1 ELSE 0 END) as postulaciones_revision
                  FROM estudiantes e
                  LEFT JOIN postulaciones p ON e.id_estudiante = p.id_estudiante
                  WHERE e.id_estudiante = :id AND e.activo = TRUE
                  GROUP BY e.id_estudiante";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar información del CV
     * 
     * @param int $id ID del estudiante
     * @param string $nombre_archivo Nombre del archivo CV
     * @param string $hash Hash SHA256 del archivo
     * @return bool True si se actualizó correctamente
     */
    public function actualizarCV($id, $nombre_archivo, $hash) {
        $query = "UPDATE estudiantes 
                  SET cv_ruta = :cv_ruta, 
                      cv_fecha_subida = NOW(), 
                      cv_hash = :cv_hash,
                      estado_cv = 'activo'
                  WHERE id_estudiante = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cv_ruta', $nombre_archivo);
        $stmt->bindParam(':cv_hash', $hash);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar CV del estudiante (cambiar estado)
     * 
     * @param int $id ID del estudiante
     * @return bool True si se actualizó correctamente
     */
    public function eliminarCV($id) {
        $query = "UPDATE estudiantes 
                  SET estado_cv = 'eliminado'
                  WHERE id_estudiante = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estudiantes con CVs expirados (más de 12 meses)
     * 
     * @return array Lista de estudiantes con CVs expirados
     */
    public function getCVsExpirados() {
        $query = "SELECT id_estudiante, correo_utp, nombres, apellidos, cv_ruta, cv_hash 
                  FROM estudiantes 
                  WHERE cv_fecha_subida < DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  AND estado_cv = 'activo' 
                  AND cv_ruta IS NOT NULL";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar todos los estudiantes
     * 
     * @param int $limite Número de registros
     * @param int $offset Posición inicial
     * @return array Lista de estudiantes
     */
    public function listarTodos($limite = 50, $offset = 0) {
        $query = "SELECT 
                  e.id_estudiante, e.correo_utp, e.nombres, e.apellidos, 
                  e.carrera, e.fecha_creacion, e.estado_cv,
                  COUNT(p.id_postulacion) as total_postulaciones
                  FROM estudiantes e
                  LEFT JOIN postulaciones p ON e.id_estudiante = p.id_estudiante
                  WHERE e.activo = TRUE
                  GROUP BY e.id_estudiante
                  ORDER BY e.fecha_creacion DESC
                  LIMIT :limite OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar estudiantes por carrera
     * 
     * @param string $carrera Nombre de la carrera
     * @return array Lista de estudiantes
     */
    public function buscarPorCarrera($carrera) {
        $query = "SELECT * FROM estudiantes 
                  WHERE carrera = :carrera AND activo = TRUE 
                  ORDER BY nombres ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':carrera', $carrera);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas generales de estudiantes
     * 
     * @return array Estadísticas
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                  COUNT(*) as total_estudiantes,
                  COUNT(DISTINCT carrera) as total_carreras,
                  SUM(CASE WHEN cv_ruta IS NOT NULL THEN 1 ELSE 0 END) as estudiantes_con_cv,
                  SUM(CASE WHEN DATE(fecha_creacion) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as registros_ultimos_30_dias
                  FROM estudiantes 
                  WHERE activo = TRUE";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si existe un estudiante con el correo dado
     * 
     * @param string $correo Correo a verificar
     * @return bool True si existe
     */
    public function existeCorreo($correo) {
        $query = "SELECT COUNT(*) as total FROM estudiantes WHERE correo_utp = :correo";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }
    
    /**
     * Desactivar estudiante (soft delete)
     * 
     * @param int $id ID del estudiante
     * @return bool True si se desactivó correctamente
     */
    public function desactivar($id) {
        $query = "UPDATE estudiantes SET activo = FALSE WHERE id_estudiante = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}