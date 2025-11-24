<?php
/**
 * Portal de Trabajo UTP - Modelo Notificacion
 * 
 * Sistema de notificaciones internas para estudiantes y administradores.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

class Notificacion {
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
     * Crear nueva notificación
     * 
     * @param array $datos Datos de la notificación
     * @return int ID de la notificación creada
     */
    public function create($datos) {
        $query = "INSERT INTO notificaciones 
                  (tipo_usuario, id_usuario, titulo, mensaje, tipo, id_relacionado) 
                  VALUES 
                  (:tipo_usuario, :id_usuario, :titulo, :mensaje, :tipo, :id_relacionado)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindValue(':tipo_usuario', $datos['tipo_usuario']);
        $stmt->bindValue(':id_usuario', $datos['id_usuario']);
        $stmt->bindValue(':titulo', $datos['titulo']);
        $stmt->bindValue(':mensaje', $datos['mensaje']);
        $stmt->bindValue(':tipo', $datos['tipo']);
        $stmt->bindValue(':id_relacionado', $datos['id_relacionado'] ?? null);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Obtener notificaciones de un usuario
     * 
     * @param string $tipo_usuario Tipo: estudiante o admin
     * @param int $id_usuario ID del usuario
     * @param int $limite Número de notificaciones
     * @return array Lista de notificaciones
     */
    public function getNotificacionesUsuario($tipo_usuario, $id_usuario, $limite = 20) {
        $query = "SELECT * FROM notificaciones 
                  WHERE tipo_usuario = :tipo_usuario AND id_usuario = :id_usuario
                  ORDER BY fecha_creacion DESC
                  LIMIT :limite";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tipo_usuario', $tipo_usuario);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener notificaciones no leídas de un usuario
     * 
     * @param string $tipo_usuario Tipo: estudiante o admin
     * @param int $id_usuario ID del usuario
     * @return array Lista de notificaciones no leídas
     */
    public function getNoLeidas($tipo_usuario, $id_usuario) {
        $query = "SELECT * FROM notificaciones 
                  WHERE tipo_usuario = :tipo_usuario 
                  AND id_usuario = :id_usuario 
                  AND leida = FALSE
                  ORDER BY fecha_creacion DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tipo_usuario', $tipo_usuario);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar notificaciones no leídas
     * 
     * @param string $tipo_usuario Tipo: estudiante o admin
     * @param int $id_usuario ID del usuario
     * @return int Número de notificaciones no leídas
     */
    public function contarNoLeidas($tipo_usuario, $id_usuario) {
        $query = "SELECT COUNT(*) as total FROM notificaciones 
                  WHERE tipo_usuario = :tipo_usuario 
                  AND id_usuario = :id_usuario 
                  AND leida = FALSE";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tipo_usuario', $tipo_usuario);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }
    
    /**
     * Marcar notificación como leída
     * 
     * @param int $id ID de la notificación
     * @return bool True si se marcó correctamente
     */
    public function marcarComoLeida($id) {
        $query = "UPDATE notificaciones SET leida = TRUE WHERE id_notificacion = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Marcar todas las notificaciones de un usuario como leídas
     * 
     * @param string $tipo_usuario Tipo: estudiante o admin
     * @param int $id_usuario ID del usuario
     * @return bool True si se marcaron correctamente
     */
    public function marcarTodasComoLeidas($tipo_usuario, $id_usuario) {
        $query = "UPDATE notificaciones SET leida = TRUE 
                  WHERE tipo_usuario = :tipo_usuario AND id_usuario = :id_usuario";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tipo_usuario', $tipo_usuario);
        $stmt->bindParam(':id_usuario', $id_usuario);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar notificación
     * 
     * @param int $id ID de la notificación
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $query = "DELETE FROM notificaciones WHERE id_notificacion = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar todas las notificaciones de un usuario
     * 
     * @param string $tipo_usuario Tipo: estudiante o admin
     * @param int $id_usuario ID del usuario
     * @return bool True si se eliminaron correctamente
     */
    public function eliminarTodasUsuario($tipo_usuario, $id_usuario) {
        $query = "DELETE FROM notificaciones 
                  WHERE tipo_usuario = :tipo_usuario AND id_usuario = :id_usuario";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tipo_usuario', $tipo_usuario);
        $stmt->bindParam(':id_usuario', $id_usuario);
        
        return $stmt->execute();
    }
    
    /**
     * Crear notificación para todos los administradores
     * 
     * @param string $titulo Título de la notificación
     * @param string $mensaje Mensaje
     * @param string $tipo Tipo de notificación
     * @param int $id_relacionado ID relacionado (opcional)
     * @return int Número de notificaciones creadas
     */
    public function notificarTodosAdmins($titulo, $mensaje, $tipo, $id_relacionado = null) {
        // Obtener todos los admins activos
        $query_admins = "SELECT id_admin FROM administradores WHERE activo = TRUE";
        $stmt_admins = $this->db->query($query_admins);
        $admins = $stmt_admins->fetchAll(PDO::FETCH_ASSOC);
        
        $contador = 0;
        
        // Crear notificación para cada admin
        foreach ($admins as $admin) {
            $this->create([
                'tipo_usuario' => 'admin',
                'id_usuario' => $admin['id_admin'],
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tipo' => $tipo,
                'id_relacionado' => $id_relacionado
            ]);
            $contador++;
        }
        
        return $contador;
    }
    
    /**
     * Limpiar notificaciones antiguas (más de 90 días)
     * 
     * @return int Número de notificaciones eliminadas
     */
    public function limpiarAntiguasLeidas() {
        $query = "DELETE FROM notificaciones 
                  WHERE leida = TRUE 
                  AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 90 DAY)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
    
    /**
     * Obtener estadísticas de notificaciones
     * 
     * @return array Estadísticas
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                  COUNT(*) as total_notificaciones,
                  SUM(CASE WHEN leida = TRUE THEN 1 ELSE 0 END) as leidas,
                  SUM(CASE WHEN leida = FALSE THEN 1 ELSE 0 END) as no_leidas,
                  SUM(CASE WHEN tipo = 'postulacion_nueva' THEN 1 ELSE 0 END) as postulaciones_nuevas,
                  SUM(CASE WHEN tipo = 'cambio_estado' THEN 1 ELSE 0 END) as cambios_estado,
                  SUM(CASE WHEN tipo = 'sistema' THEN 1 ELSE 0 END) as sistema
                  FROM notificaciones";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar notificación por ID
     * 
     * @param int $id ID de la notificación
     * @return array|null Datos de la notificación o null si no existe
     */
    public function findById($id) {
        $query = "SELECT * FROM notificaciones WHERE id_notificacion = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}