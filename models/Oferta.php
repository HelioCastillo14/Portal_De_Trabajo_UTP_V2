<?php
/**
 * Portal de Trabajo UTP - Modelo Oferta
 * 
 * Gestión completa de ofertas laborales.
 * Incluye búsqueda avanzada, filtros, paginación y asociación con habilidades.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

class Oferta {
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
     * Buscar oferta por ID con información de empresa
     * 
     * @param int $id ID de la oferta
     * @return array|null Datos de la oferta o null si no existe
     */
    public function findById($id) {
        $query = "SELECT o.*, 
                  e.nombre_comercial as empresa_nombre, 
                  e.logo as empresa_logo,
                  e.sitio_web, 
                  e.sector,
                  e.descripcion as empresa_descripcion
                  FROM ofertas o
                  INNER JOIN empresas e ON o.id_empresa = e.id_empresa
                  WHERE o.id_oferta = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nueva oferta
     * 
     * @param array $datos Datos de la oferta
     * @return int ID de la oferta creada
     */
    public function create($datos) {
        $query = "INSERT INTO ofertas 
                  (id_empresa, titulo, descripcion, requisitos, responsabilidades, 
                   beneficios, salario_min, salario_max, modalidad, tipo_empleo, 
                   nivel_experiencia, ubicacion, fecha_limite) 
                  VALUES 
                  (:id_empresa, :titulo, :descripcion, :requisitos, :responsabilidades, 
                   :beneficios, :salario_min, :salario_max, :modalidad, :tipo_empleo, 
                   :nivel_experiencia, :ubicacion, :fecha_limite)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id_empresa', $datos['id_empresa']);
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':requisitos', $datos['requisitos'] ?? null);
        $stmt->bindParam(':responsabilidades', $datos['responsabilidades'] ?? null);
        $stmt->bindParam(':beneficios', $datos['beneficios'] ?? null);
        $stmt->bindParam(':salario_min', $datos['salario_min'] ?? null);
        $stmt->bindParam(':salario_max', $datos['salario_max'] ?? null);
        $stmt->bindParam(':modalidad', $datos['modalidad']);
        $stmt->bindParam(':tipo_empleo', $datos['tipo_empleo']);
        $stmt->bindParam(':nivel_experiencia', $datos['nivel_experiencia']);
        $stmt->bindParam(':ubicacion', $datos['ubicacion'] ?? null);
        $stmt->bindParam(':fecha_limite', $datos['fecha_limite']);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar oferta
     * 
     * @param int $id ID de la oferta
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $datos) {
        $campos = [];
        $valores = [];
        
        foreach ($datos as $campo => $valor) {
            if ($campo !== 'id_oferta') {
                $campos[] = "$campo = :$campo";
                $valores[":$campo"] = $valor;
            }
        }
        
        $valores[':id'] = $id;
        
        $query = "UPDATE ofertas SET " . implode(', ', $campos) . " WHERE id_oferta = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Eliminar oferta
     * 
     * @param int $id ID de la oferta
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $query = "DELETE FROM ofertas WHERE id_oferta = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener ofertas activas con filtros y paginación
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $pagina Número de página
     * @param int $limite Ofertas por página
     * @return array Lista de ofertas
     */
    public function getOfertasActivas($filtros = [], $pagina = 1, $limite = 10) {
        $offset = ($pagina - 1) * $limite;
        
        $query = "SELECT o.*, 
                  e.nombre_comercial as empresa, 
                  e.logo as empresa_logo,
                  e.sector,
                  COUNT(DISTINCT p.id_postulacion) as total_postulaciones
                  FROM ofertas o
                  INNER JOIN empresas e ON o.id_empresa = e.id_empresa
                  LEFT JOIN postulaciones p ON o.id_oferta = p.id_oferta
                  WHERE o.estado = 'activa' AND o.fecha_limite >= CURDATE()";
        
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['modalidad'])) {
            $query .= " AND o.modalidad = :modalidad";
            $params[':modalidad'] = $filtros['modalidad'];
        }
        
        if (!empty($filtros['tipo_empleo'])) {
            $query .= " AND o.tipo_empleo = :tipo_empleo";
            $params[':tipo_empleo'] = $filtros['tipo_empleo'];
        }
        
        if (!empty($filtros['nivel'])) {
            $query .= " AND o.nivel_experiencia = :nivel";
            $params[':nivel'] = $filtros['nivel'];
        }
        
        if (!empty($filtros['empresa'])) {
            $query .= " AND o.id_empresa = :empresa";
            $params[':empresa'] = $filtros['empresa'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $query .= " AND (o.titulo LIKE :busqueda OR o.descripcion LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }
        
        if (!empty($filtros['salario_min'])) {
            $query .= " AND o.salario_max >= :salario_min";
            $params[':salario_min'] = $filtros['salario_min'];
        }
        
        $query .= " GROUP BY o.id_oferta 
                    ORDER BY o.fecha_publicacion DESC 
                    LIMIT :limite OFFSET :offset";
        
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
     * Buscar ofertas por rol usando procedimiento almacenado
     * 
     * @param string $rol Término de búsqueda
     * @return array Lista de ofertas que coinciden
     */
    public function buscarPorRol($rol) {
        $stmt = $this->db->prepare("CALL sp_buscar_ofertas_por_rol(:rol)");
        $stmt->bindParam(':rol', $rol);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener total de páginas según filtros
     * 
     * @param int $limite Ofertas por página
     * @param array $filtros Filtros aplicados
     * @return int Número total de páginas
     */
    public function getTotalPaginas($limite = 10, $filtros = []) {
        $query = "SELECT COUNT(DISTINCT o.id_oferta) as total 
                  FROM ofertas o
                  WHERE o.estado = 'activa' AND o.fecha_limite >= CURDATE()";
        
        $params = [];
        
        if (!empty($filtros['modalidad'])) {
            $query .= " AND o.modalidad = :modalidad";
            $params[':modalidad'] = $filtros['modalidad'];
        }
        
        if (!empty($filtros['tipo_empleo'])) {
            $query .= " AND o.tipo_empleo = :tipo_empleo";
            $params[':tipo_empleo'] = $filtros['tipo_empleo'];
        }
        
        if (!empty($filtros['nivel'])) {
            $query .= " AND o.nivel_experiencia = :nivel";
            $params[':nivel'] = $filtros['nivel'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $query .= " AND (o.titulo LIKE :busqueda OR o.descripcion LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        return ceil($total / $limite);
    }
    
    /**
     * Asociar habilidades a una oferta
     * 
     * @param int $id_oferta ID de la oferta
     * @param array $habilidades Array de IDs de habilidades
     * @return bool True si se asociaron correctamente
     */
    public function asociarHabilidades($id_oferta, $habilidades) {
        try {
            // Eliminar asociaciones anteriores
            $query = "DELETE FROM ofertas_habilidades WHERE id_oferta = :id_oferta";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_oferta', $id_oferta);
            $stmt->execute();
            
            // Insertar nuevas asociaciones
            if (!empty($habilidades)) {
                $query = "INSERT INTO ofertas_habilidades (id_oferta, id_habilidad) 
                          VALUES (:id_oferta, :id_habilidad)";
                $stmt = $this->db->prepare($query);
                
                foreach ($habilidades as $id_habilidad) {
                    $stmt->bindParam(':id_oferta', $id_oferta);
                    $stmt->bindParam(':id_habilidad', $id_habilidad);
                    $stmt->execute();
                }
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error al asociar habilidades: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener habilidades de una oferta
     * 
     * @param int $id_oferta ID de la oferta
     * @return array Lista de habilidades
     */
    public function getHabilidades($id_oferta) {
        $query = "SELECT h.* 
                  FROM habilidades h
                  INNER JOIN ofertas_habilidades oh ON h.id_habilidad = oh.id_habilidad
                  WHERE oh.id_oferta = :id_oferta
                  ORDER BY h.nombre ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_oferta', $id_oferta);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cerrar ofertas vencidas (cambiar estado a 'cerrada')
     * Para usar en cron job
     * 
     * @return int Número de ofertas cerradas
     */
    public function cerrarOfertasVencidas() {
        $query = "UPDATE ofertas 
                  SET estado = 'cerrada' 
                  WHERE fecha_limite < CURDATE() 
                  AND estado = 'activa'";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->rowCount();
    }
    
    /**
     * Cambiar estado de la oferta
     * 
     * @param int $id ID de la oferta
     * @param string $nuevo_estado Estado: activa, cerrada, tomada
     * @return bool True si se cambió correctamente
     */
    public function cambiarEstado($id, $nuevo_estado) {
        $estados_validos = ['activa', 'cerrada', 'tomada'];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            return false;
        }
        
        $query = "UPDATE ofertas SET estado = :estado WHERE id_oferta = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':estado', $nuevo_estado);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener ofertas de una empresa
     * 
     * @param int $id_empresa ID de la empresa
     * @param string $estado Filtrar por estado (opcional)
     * @return array Lista de ofertas
     */
    public function getOfertasPorEmpresa($id_empresa, $estado = null) {
        $query = "SELECT o.*, COUNT(p.id_postulacion) as total_postulaciones
                  FROM ofertas o
                  LEFT JOIN postulaciones p ON o.id_oferta = p.id_oferta
                  WHERE o.id_empresa = :id_empresa";
        
        if ($estado) {
            $query .= " AND o.estado = :estado";
        }
        
        $query .= " GROUP BY o.id_oferta ORDER BY o.fecha_publicacion DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_empresa', $id_empresa);
        
        if ($estado) {
            $stmt->bindParam(':estado', $estado);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de ofertas
     * 
     * @return array Estadísticas generales
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                  COUNT(*) as total_ofertas,
                  SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as ofertas_activas,
                  SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as ofertas_cerradas,
                  SUM(CASE WHEN estado = 'tomada' THEN 1 ELSE 0 END) as ofertas_tomadas,
                  SUM(CASE WHEN DATE(fecha_publicacion) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as publicadas_ultimos_30_dias,
                  AVG(salario_max) as salario_promedio
                  FROM ofertas";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener ofertas más recientes (para homepage)
     * 
     * @param int $limite Número de ofertas
     * @return array Lista de ofertas recientes
     */
    public function getOfertasRecientes($limite = 6) {
        $query = "SELECT o.*, 
                  e.nombre_comercial as empresa, 
                  e.logo as empresa_logo
                  FROM ofertas o
                  INNER JOIN empresas e ON o.id_empresa = e.id_empresa
                  WHERE o.estado = 'activa' AND o.fecha_limite >= CURDATE()
                  ORDER BY o.fecha_publicacion DESC
                  LIMIT :limite";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si un estudiante ya se postuló a una oferta
     * 
     * @param int $id_oferta ID de la oferta
     * @param int $id_estudiante ID del estudiante
     * @return bool True si ya se postuló
     */
    public function yaSePostulo($id_oferta, $id_estudiante) {
        $query = "SELECT COUNT(*) as total 
                  FROM postulaciones 
                  WHERE id_oferta = :id_oferta AND id_estudiante = :id_estudiante";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_oferta', $id_oferta);
        $stmt->bindParam(':id_estudiante', $id_estudiante);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }
}