<?php
/**
 * Portal de Trabajo UTP - Modelo Empresa
 * 
 * Gestión de empresas que publican ofertas laborales.
 * CRUD completo y búsquedas.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

class Empresa {
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
     * Buscar empresa por ID
     * 
     * @param int $id ID de la empresa
     * @return array|null Datos de la empresa o null si no existe
     */
    public function findById($id) {
        $query = "SELECT * FROM empresas WHERE id_empresa = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nueva empresa
     * 
     * @param array $datos Datos de la empresa
     * @return int ID de la empresa creada
     */
    public function create($datos) {
        $query = "INSERT INTO empresas 
                  (nombre_legal, nombre_comercial, logo, descripcion, sector, 
                   sitio_web, telefono, email_contacto, direccion) 
                  VALUES 
                  (:nombre_legal, :nombre_comercial, :logo, :descripcion, :sector, 
                   :sitio_web, :telefono, :email_contacto, :direccion)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':nombre_legal', $datos['nombre_legal']);
        $stmt->bindParam(':nombre_comercial', $datos['nombre_comercial'] ?? null);
        $stmt->bindParam(':logo', $datos['logo'] ?? 'placeholder-logo.png');
        $stmt->bindParam(':descripcion', $datos['descripcion'] ?? null);
        $stmt->bindParam(':sector', $datos['sector'] ?? null);
        $stmt->bindParam(':sitio_web', $datos['sitio_web'] ?? null);
        $stmt->bindParam(':telefono', $datos['telefono'] ?? null);
        $stmt->bindParam(':email_contacto', $datos['email_contacto'] ?? null);
        $stmt->bindParam(':direccion', $datos['direccion'] ?? null);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar datos de la empresa
     * 
     * @param int $id ID de la empresa
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $datos) {
        $campos = [];
        $valores = [];
        
        foreach ($datos as $campo => $valor) {
            if ($campo !== 'id_empresa') {
                $campos[] = "$campo = :$campo";
                $valores[":$campo"] = $valor;
            }
        }
        
        $valores[':id'] = $id;
        
        $query = "UPDATE empresas SET " . implode(', ', $campos) . " WHERE id_empresa = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Eliminar empresa
     * 
     * @param int $id ID de la empresa
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $query = "DELETE FROM empresas WHERE id_empresa = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Listar todas las empresas
     * 
     * @param string $estado Filtrar por estado (activa, inactiva, bloqueada)
     * @param int $limite Número de registros
     * @param int $offset Posición inicial
     * @return array Lista de empresas
     */
    public function listarTodas($estado = null, $limite = 50, $offset = 0) {
        $query = "SELECT e.*, 
                  COUNT(o.id_oferta) as total_ofertas,
                  SUM(CASE WHEN o.estado = 'activa' THEN 1 ELSE 0 END) as ofertas_activas
                  FROM empresas e
                  LEFT JOIN ofertas o ON e.id_empresa = o.id_empresa";
        
        if ($estado) {
            $query .= " WHERE e.estado = :estado";
        }
        
        $query .= " GROUP BY e.id_empresa 
                    ORDER BY e.nombre_comercial ASC 
                    LIMIT :limite OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        
        if ($estado) {
            $stmt->bindParam(':estado', $estado);
        }
        
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar empresas activas (para dropdown)
     * 
     * @return array Lista simple de empresas activas
     */
    public function listarActivas() {
        $query = "SELECT id_empresa, nombre_comercial, logo 
                  FROM empresas 
                  WHERE estado = 'activa' 
                  ORDER BY nombre_comercial ASC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar empresas por sector
     * 
     * @param string $sector Sector a buscar
     * @return array Lista de empresas
     */
    public function buscarPorSector($sector) {
        $query = "SELECT * FROM empresas 
                  WHERE sector = :sector AND estado = 'activa' 
                  ORDER BY nombre_comercial ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':sector', $sector);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar empresas por nombre
     * 
     * @param string $termino Término de búsqueda
     * @return array Lista de empresas
     */
    public function buscarPorNombre($termino) {
        $query = "SELECT * FROM empresas 
                  WHERE (nombre_legal LIKE :termino OR nombre_comercial LIKE :termino)
                  AND estado = 'activa'
                  ORDER BY nombre_comercial ASC";
        
        $stmt = $this->db->prepare($query);
        $termino_busqueda = '%' . $termino . '%';
        $stmt->bindParam(':termino', $termino_busqueda);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cambiar estado de la empresa
     * 
     * @param int $id ID de la empresa
     * @param string $nuevo_estado Estado: activa, inactiva, bloqueada
     * @return bool True si se cambió correctamente
     */
    public function cambiarEstado($id, $nuevo_estado) {
        $estados_validos = ['activa', 'inactiva', 'bloqueada'];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            return false;
        }
        
        $query = "UPDATE empresas SET estado = :estado WHERE id_empresa = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener empresa con sus ofertas
     * 
     * @param int $id ID de la empresa
     * @return array Datos de la empresa con sus ofertas
     */
    public function getConOfertas($id) {
        // Datos de la empresa
        $empresa = $this->findById($id);
        
        if (!$empresa) {
            return null;
        }
        
        // Obtener ofertas de la empresa
        $query = "SELECT o.*, 
                  COUNT(p.id_postulacion) as total_postulaciones
                  FROM ofertas o
                  LEFT JOIN postulaciones p ON o.id_oferta = p.id_oferta
                  WHERE o.id_empresa = :id_empresa
                  GROUP BY o.id_oferta
                  ORDER BY o.fecha_publicacion DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_empresa', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $empresa['ofertas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $empresa;
    }
    
    /**
     * Obtener estadísticas de empresas
     * 
     * @return array Estadísticas generales
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                  COUNT(*) as total_empresas,
                  SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as empresas_activas,
                  SUM(CASE WHEN estado = 'inactiva' THEN 1 ELSE 0 END) as empresas_inactivas,
                  COUNT(DISTINCT sector) as total_sectores
                  FROM empresas";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener lista de sectores únicos
     * 
     * @return array Lista de sectores
     */
    public function obtenerSectores() {
        $query = "SELECT DISTINCT sector 
                  FROM empresas 
                  WHERE sector IS NOT NULL 
                  ORDER BY sector ASC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}