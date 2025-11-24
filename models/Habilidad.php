<?php
/**
 * Portal de Trabajo UTP - Modelo Habilidad
 * 
 * Gestión del catálogo de habilidades técnicas y blandas.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

class Habilidad {
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
     * Buscar habilidad por ID
     * 
     * @param int $id ID de la habilidad
     * @return array|null Datos de la habilidad o null si no existe
     */
    public function findById($id) {
        $query = "SELECT * FROM habilidades WHERE id_habilidad = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nueva habilidad
     * 
     * @param array $datos Datos de la habilidad
     * @return int ID de la habilidad creada
     */
    public function create($datos) {
        $query = "INSERT INTO habilidades (nombre, categoria) VALUES (:nombre, :categoria)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':categoria', $datos['categoria']);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar habilidad
     * 
     * @param int $id ID de la habilidad
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $datos) {
        $query = "UPDATE habilidades SET nombre = :nombre, categoria = :categoria WHERE id_habilidad = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':categoria', $datos['categoria']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar habilidad
     * 
     * @param int $id ID de la habilidad
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $query = "DELETE FROM habilidades WHERE id_habilidad = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Listar todas las habilidades
     * 
     * @param string $categoria Filtrar por categoría (opcional)
     * @return array Lista de habilidades
     */
    public function listarTodas($categoria = null) {
        $query = "SELECT * FROM habilidades";
        
        if ($categoria) {
            $query .= " WHERE categoria = :categoria";
        }
        
        $query .= " ORDER BY categoria ASC, nombre ASC";
        
        $stmt = $this->db->prepare($query);
        
        if ($categoria) {
            $stmt->bindParam(':categoria', $categoria);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Listar habilidades agrupadas por categoría
     * 
     * @return array Habilidades agrupadas
     */
    public function listarPorCategoria() {
        $query = "SELECT * FROM habilidades ORDER BY categoria ASC, nombre ASC";
        
        $stmt = $this->db->query($query);
        $habilidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agrupar por categoría
        $agrupadas = [];
        foreach ($habilidades as $habilidad) {
            $categoria = $habilidad['categoria'];
            if (!isset($agrupadas[$categoria])) {
                $agrupadas[$categoria] = [];
            }
            $agrupadas[$categoria][] = $habilidad;
        }
        
        return $agrupadas;
    }
    
    /**
     * Buscar habilidades por nombre
     * 
     * @param string $termino Término de búsqueda
     * @return array Lista de habilidades que coinciden
     */
    public function buscarPorNombre($termino) {
        $query = "SELECT * FROM habilidades 
                  WHERE nombre LIKE :termino 
                  ORDER BY nombre ASC";
        
        $stmt = $this->db->prepare($query);
        $termino_busqueda = '%' . $termino . '%';
        $stmt->bindParam(':termino', $termino_busqueda);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener habilidades de una oferta específica
     * 
     * @param int $id_oferta ID de la oferta
     * @return array Lista de habilidades de la oferta
     */
    public function obtenerPorOferta($id_oferta) {
        $query = "SELECT h.* 
                  FROM habilidades h
                  INNER JOIN ofertas_habilidades oh ON h.id_habilidad = oh.id_habilidad
                  WHERE oh.id_oferta = :id_oferta
                  ORDER BY h.nombre ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_oferta', $id_oferta, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener habilidades más demandadas (las más usadas en ofertas)
     * 
     * @param int $limite Número de habilidades a retornar
     * @return array Lista de habilidades más demandadas
     */
    public function obtenerMasDemandadas($limite = 10) {
        $query = "SELECT h.*, COUNT(oh.id_oferta) as total_ofertas
                  FROM habilidades h
                  INNER JOIN ofertas_habilidades oh ON h.id_habilidad = oh.id_habilidad
                  INNER JOIN ofertas o ON oh.id_oferta = o.id_oferta
                  WHERE o.estado = 'activa'
                  GROUP BY h.id_habilidad
                  ORDER BY total_ofertas DESC
                  LIMIT :limite";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si existe una habilidad con el nombre dado
     * 
     * @param string $nombre Nombre a verificar
     * @return bool True si existe
     */
    public function existeNombre($nombre) {
        $query = "SELECT COUNT(*) as total FROM habilidades WHERE nombre = :nombre";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }
    
    /**
     * Obtener estadísticas de habilidades
     * 
     * @return array Estadísticas
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                  COUNT(*) as total_habilidades,
                  SUM(CASE WHEN categoria = 'tecnica' THEN 1 ELSE 0 END) as tecnicas,
                  SUM(CASE WHEN categoria = 'blanda' THEN 1 ELSE 0 END) as blandas,
                  SUM(CASE WHEN categoria = 'lenguaje' THEN 1 ELSE 0 END) as lenguajes,
                  SUM(CASE WHEN categoria = 'herramienta' THEN 1 ELSE 0 END) as herramientas
                  FROM habilidades";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}