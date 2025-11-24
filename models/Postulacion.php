<?php
/**
 * Portal de Trabajo UTP - Modelo Postulacion
 * 
 * Gestión de postulaciones de estudiantes a ofertas.
 * Incluye cambio de estados y estadísticas.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

class Postulacion {
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
     * Buscar postulación por ID con datos completos
     * 
     * @param int $id ID de la postulación
     * @return array|null Datos de la postulación o null si no existe
     */
    public function findById($id) {
        $query = "SELECT p.*, 
                  e.nombres, e.apellidos, e.correo_utp, e.carrera, e.cv_ruta, e.foto_perfil,
                  o.titulo as oferta_titulo, o.id_empresa,
                  emp.nombre_comercial as empresa_nombre
                  FROM postulaciones p
                  INNER JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                  INNER JOIN ofertas o ON p.id_oferta = o.id_oferta
                  INNER JOIN empresas emp ON o.id_empresa = emp.id_empresa
                  WHERE p.id_postulacion = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nueva postulación
     * 
     * @param array $datos Datos de la postulación
     * @return int ID de la postulación creada
     */
    public function create($datos) {
        $query = "INSERT INTO postulaciones (id_estudiante, id_oferta, estado) 
                  VALUES (:id_estudiante, :id_oferta, :estado)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id_estudiante', $datos['id_estudiante']);
        $stmt->bindParam(':id_oferta', $datos['id_oferta']);
        $stmt->bindParam(':estado', $datos['estado']);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar estado o datos de postulación
     * 
     * @param int $id ID de la postulación
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $datos) {
        $campos = [];
        $valores = [];
        
        foreach ($datos as $campo => $valor) {
            if ($campo !== 'id_postulacion') {
                $campos[] = "$campo = :$campo";
                $valores[":$campo"] = $valor;
            }
        }
        
        $valores[':id'] = $id;
        
        $query = "UPDATE postulaciones SET " . implode(', ', $campos) . " WHERE id_postulacion = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Cambiar estado de postulación
     * 
     * @param int $id ID de la postulación
     * @param string $nuevo_estado Estado: en_revision, aceptada, rechazada
     * @return bool True si se cambió correctamente
     */
    public function cambiarEstado($id, $nuevo_estado) {
        $estados_validos = ['en_revision', 'aceptada', 'rechazada'];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            return false;
        }
        
        $query = "UPDATE postulaciones SET estado = :estado WHERE id_postulacion = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si existe postulación (evitar duplicados)
     * 
     * @param int $id_estudiante ID del estudiante
     * @param int $id_oferta ID de la oferta
     * @return bool True si ya existe la postulación
     */
    public function existePostulacion($id_estudiante, $id_oferta) {
        $query = "SELECT COUNT(*) as total 
                  FROM postulaciones 
                  WHERE id_estudiante = :estudiante AND id_oferta = :oferta";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':estudiante', $id_estudiante);
        $stmt->bindParam(':oferta', $id_oferta);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }
    
    /**
     * Obtener postulaciones de un estudiante
     * 
     * @param int $id_estudiante ID del estudiante
     * @param int $limite Número de registros
     * @param int $offset Posición inicial
     * @return array Lista de postulaciones
     */
    public function getPostulacionesEstudiante($id_estudiante, $limite = 10, $offset = 0) {
        $query = "SELECT p.*, 
                  o.titulo as titulo_oferta, o.modalidad, o.tipo_empleo, o.ubicacion, o.estado as estado_oferta,
                  e.nombre_comercial as nombre_empresa, e.logo
                  FROM postulaciones p
                  INNER JOIN ofertas o ON p.id_oferta = o.id_oferta
                  INNER JOIN empresas e ON o.id_empresa = e.id_empresa
                  WHERE p.id_estudiante = :id_estudiante
                  ORDER BY p.fecha_postulacion DESC
                  LIMIT :limite OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_estudiante', $id_estudiante);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener postulaciones de una oferta
     * 
     * @param int $id_oferta ID de la oferta
     * @return array Lista de postulaciones con datos del estudiante
     */
    public function getPostulacionesPorOferta($id_oferta) {
        $query = "SELECT p.*, 
                  e.nombres, e.apellidos, e.correo_utp, e.carrera, e.cv_ruta, e.foto_perfil
                  FROM postulaciones p
                  INNER JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                  WHERE p.id_oferta = :id_oferta
                  ORDER BY p.fecha_postulacion DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_oferta', $id_oferta);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener postulaciones por rango de fechas (para exportar)
     * 
     * @param string $fecha_inicio Fecha inicio
     * @param string $fecha_fin Fecha fin
     * @return array Lista de postulaciones
     */
    public function getPostulacionesPorRango($fecha_inicio, $fecha_fin) {
        $query = "SELECT p.*, 
                  e.nombres as nombres_estudiante, 
                  e.apellidos as apellidos_estudiante, 
                  e.correo_utp as correo_estudiante, 
                  e.carrera,
                  o.titulo as titulo_oferta,
                  emp.nombre_comercial as nombre_empresa
                  FROM postulaciones p
                  INNER JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                  INNER JOIN ofertas o ON p.id_oferta = o.id_oferta
                  INNER JOIN empresas emp ON o.id_empresa = emp.id_empresa
                  WHERE p.fecha_postulacion BETWEEN :fecha_inicio AND :fecha_fin
                  ORDER BY p.fecha_postulacion DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
        $stmt->bindParam(':fecha_fin', $fecha_fin);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener todas las postulaciones con filtros
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $limite Número de registros
     * @param int $offset Posición inicial
     * @return array Lista de postulaciones
     */
    public function listarTodas($filtros = [], $limite = 50, $offset = 0) {
        $query = "SELECT p.*, 
                  e.nombres, e.apellidos, e.correo_utp, e.carrera,
                  o.titulo as oferta_titulo,
                  emp.nombre_comercial as empresa_nombre
                  FROM postulaciones p
                  INNER JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                  INNER JOIN ofertas o ON p.id_oferta = o.id_oferta
                  INNER JOIN empresas emp ON o.id_empresa = emp.id_empresa
                  WHERE 1=1";
        
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $query .= " AND p.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['id_empresa'])) {
            $query .= " AND o.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $query .= " AND p.fecha_postulacion >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        
        $query .= " ORDER BY p.fecha_postulacion DESC LIMIT :limite OFFSET :offset";
        
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
     * Obtener estadísticas de postulaciones
     * 
     * @return array Estadísticas generales
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN estado = 'en_revision' THEN 1 ELSE 0 END) as en_revision,
                  SUM(CASE WHEN estado = 'aceptada' THEN 1 ELSE 0 END) as aceptadas,
                  SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                  SUM(CASE WHEN DATE(fecha_postulacion) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as ultimos_30_dias,
                  SUM(CASE WHEN DATE(fecha_postulacion) = CURDATE() THEN 1 ELSE 0 END) as hoy
                  FROM postulaciones";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener postulaciones recientes (para dashboard admin)
     * 
     * @param int $limite Número de postulaciones
     * @return array Lista de postulaciones recientes
     */
    public function getPostulacionesRecientes($limite = 10) {
        $query = "SELECT p.*, 
                  e.nombres, e.apellidos, e.foto_perfil,
                  o.titulo as oferta_titulo,
                  emp.nombre_comercial as empresa_nombre
                  FROM postulaciones p
                  INNER JOIN estudiantes e ON p.id_estudiante = e.id_estudiante
                  INNER JOIN ofertas o ON p.id_oferta = o.id_oferta
                  INNER JOIN empresas emp ON o.id_empresa = emp.id_empresa
                  ORDER BY p.fecha_postulacion DESC
                  LIMIT :limite";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Eliminar postulación
     * 
     * @param int $id ID de la postulación
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $query = "DELETE FROM postulaciones WHERE id_postulacion = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Contar postulaciones de un estudiante
     * 
     * @param int $id_estudiante ID del estudiante
     * @return int Número de postulaciones
     */
    public function contarPorEstudiante($id_estudiante) {
        $query = "SELECT COUNT(*) as total 
                  FROM postulaciones 
                  WHERE id_estudiante = :id_estudiante";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_estudiante', $id_estudiante);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
    }
}