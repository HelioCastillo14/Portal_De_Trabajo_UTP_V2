<?php
/**
 * Modelo Oferta - CORREGIDO
 * Gestión de ofertas de empleo
 */

class Oferta {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Obtener ofertas activas con filtros y paginación
     */
    public function getOfertasActivas($filtros = [], $pagina = 1, $limite = 12) {
        $offset = ($pagina - 1) * $limite;
        
        $query = "SELECT o.*, 
                  e.nombre_comercial as empresa, 
                  e.logo as logo
                  FROM ofertas o
                  INNER JOIN empresas e ON o.id_empresa = e.id_empresa
                  WHERE o.estado = 'activa' AND o.fecha_limite >= CURDATE()";
        
        $params = [];
        
        // Filtro de búsqueda
        if (!empty($filtros['busqueda'])) {
            $query .= " AND (o.titulo LIKE :busqueda OR o.descripcion LIKE :busqueda)";
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $params[':busqueda'] = $busqueda;
        }
        
        // Filtro por modalidad
        if (!empty($filtros['modalidad'])) {
            $query .= " AND o.modalidad = :modalidad";
            $params[':modalidad'] = $filtros['modalidad'];
        }
        
        // Filtro por tipo de empleo
        if (!empty($filtros['tipo_empleo'])) {
            $query .= " AND o.tipo_empleo = :tipo_empleo";
            $params[':tipo_empleo'] = $filtros['tipo_empleo'];
        }
        
        // Filtro por empresa
        if (!empty($filtros['empresa']) && is_numeric($filtros['empresa'])) {
            $query .= " AND o.id_empresa = :empresa";
            $params[':empresa'] = (int)$filtros['empresa'];
        }
        
        // Filtro por nivel de experiencia
        if (!empty($filtros['nivel_experiencia'])) {
            $query .= " AND o.nivel_experiencia = :nivel_experiencia";
            $params[':nivel_experiencia'] = $filtros['nivel_experiencia'];
        }
        
        $query .= " ORDER BY o.fecha_publicacion DESC 
                    LIMIT :limite OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        
        // CORRECCIÓN: bindValue en lugar de bindParam para valores inline
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar ofertas activas con filtros
     */
    public function contarOfertasActivas($filtros = []) {
        $query = "SELECT COUNT(*) as total
                  FROM ofertas o
                  INNER JOIN empresas e ON o.id_empresa = e.id_empresa
                  WHERE o.estado = 'activa' AND o.fecha_limite >= CURDATE()";
        
        $params = [];
        
        if (!empty($filtros['busqueda'])) {
            $query .= " AND (o.titulo LIKE :busqueda OR o.descripcion LIKE :busqueda)";
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $params[':busqueda'] = $busqueda;
        }
        
        if (!empty($filtros['modalidad'])) {
            $query .= " AND o.modalidad = :modalidad";
            $params[':modalidad'] = $filtros['modalidad'];
        }
        
        if (!empty($filtros['tipo_empleo'])) {
            $query .= " AND o.tipo_empleo = :tipo_empleo";
            $params[':tipo_empleo'] = $filtros['tipo_empleo'];
        }
        
        if (!empty($filtros['empresa']) && is_numeric($filtros['empresa'])) {
            $query .= " AND o.id_empresa = :empresa";
            $params[':empresa'] = (int)$filtros['empresa'];
        }
        
        if (!empty($filtros['nivel_experiencia'])) {
            $query .= " AND o.nivel_experiencia = :nivel_experiencia";
            $params[':nivel_experiencia'] = $filtros['nivel_experiencia'];
        }
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtener oferta por ID con información de empresa
     */
    public function findById($id) {
        $query = "SELECT o.*, 
                  e.nombre_comercial as empresa,
                  e.nombre_legal,
                  e.logo as logo,
                  e.sitio_web,
                  e.sector,
                  e.descripcion as empresa_descripcion
                  FROM ofertas o
                  INNER JOIN empresas e ON o.id_empresa = e.id_empresa
                  WHERE o.id_oferta = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener ofertas recientes
     */
    public function getOfertasRecientes($limite = 6) {
        $query = "SELECT o.*, 
                  e.nombre_comercial as empresa, 
                  e.logo as logo
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
     * Obtener habilidades de una oferta
     */
    public function getHabilidades($id_oferta) {
        $query = "SELECT h.* 
                  FROM habilidades h
                  INNER JOIN ofertas_habilidades oh ON h.id_habilidad = oh.id_habilidad
                  WHERE oh.id_oferta = :id_oferta";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id_oferta', $id_oferta, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar si un estudiante ya se postuló
     */
    public function yaSePostulo($id_oferta, $id_estudiante) {
        $query = "SELECT COUNT(*) as total 
                  FROM postulaciones 
                  WHERE id_oferta = :id_oferta AND id_estudiante = :id_estudiante";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id_oferta', $id_oferta, PDO::PARAM_INT);
        $stmt->bindValue(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['total'] ?? 0) > 0;
    }
    
    /**
     * Obtener estadísticas para el hero
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                  (SELECT COUNT(*) FROM ofertas WHERE estado = 'activa' AND fecha_limite >= CURDATE()) as ofertas_activas,
                  (SELECT COUNT(*) FROM empresas WHERE estado = 'activa') as empresas_activas,
                  (SELECT COUNT(*) FROM ofertas WHERE DATE(fecha_publicacion) = CURDATE()) as nuevas_hoy";
        
        $stmt = $this->db->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Contar ofertas activas
     */
    public function contarActivas() {
        $query = "SELECT COUNT(*) as total FROM ofertas WHERE estado = 'activa' AND fecha_limite >= CURDATE()";
        $stmt = $this->db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Contar ofertas nuevas hoy
     */
    public function contarNuevasHoy() {
        $query = "SELECT COUNT(*) as total FROM ofertas WHERE DATE(fecha_publicacion) = CURDATE()";
        $stmt = $this->db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Calcular total de páginas para paginación
     */
    public function getTotalPaginas($limite = 12, $filtros = []) {
        $total = $this->contarOfertasActivas($filtros);
        return ceil($total / $limite);
    }
    
    /**
     * Crear nueva oferta
     */
    public function crear($datos) {
        $query = "INSERT INTO ofertas 
                  (id_empresa, titulo, descripcion, requisitos, responsabilidades, beneficios,
                   salario_min, salario_max, ubicacion, modalidad, tipo_empleo, nivel_experiencia, fecha_limite)
                  VALUES 
                  (:id_empresa, :titulo, :descripcion, :requisitos, :responsabilidades, :beneficios,
                   :salario_min, :salario_max, :ubicacion, :modalidad, :tipo_empleo, :nivel_experiencia, :fecha_limite)";
        
        $stmt = $this->db->prepare($query);
        
        // Usar bindValue para todos los parámetros
        $stmt->bindValue(':id_empresa', $datos['id_empresa'], PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $datos['titulo']);
        $stmt->bindValue(':descripcion', $datos['descripcion']);
        $stmt->bindValue(':requisitos', $datos['requisitos'] ?? null);
        $stmt->bindValue(':responsabilidades', $datos['responsabilidades'] ?? null);
        $stmt->bindValue(':beneficios', $datos['beneficios'] ?? null);
        $stmt->bindValue(':salario_min', $datos['salario_min'] ?? null);
        $stmt->bindValue(':salario_max', $datos['salario_max'] ?? null);
        $stmt->bindValue(':ubicacion', $datos['ubicacion'] ?? null);
        $stmt->bindValue(':modalidad', $datos['modalidad']);
        $stmt->bindValue(':tipo_empleo', $datos['tipo_empleo']);
        $stmt->bindValue(':nivel_experiencia', $datos['nivel_experiencia']);
        $stmt->bindValue(':fecha_limite', $datos['fecha_limite'] ?? null);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar oferta
     */
    public function actualizar($id, $datos) {
        $query = "UPDATE ofertas SET 
                  id_empresa = :id_empresa,
                  titulo = :titulo,
                  descripcion = :descripcion,
                  requisitos = :requisitos,
                  responsabilidades = :responsabilidades,
                  beneficios = :beneficios,
                  salario_min = :salario_min,
                  salario_max = :salario_max,
                  ubicacion = :ubicacion,
                  modalidad = :modalidad,
                  tipo_empleo = :tipo_empleo,
                  nivel_experiencia = :nivel_experiencia,
                  fecha_limite = :fecha_limite,
                  estado = :estado
                  WHERE id_oferta = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':id_empresa', $datos['id_empresa'], PDO::PARAM_INT);
        $stmt->bindValue(':titulo', $datos['titulo']);
        $stmt->bindValue(':descripcion', $datos['descripcion']);
        $stmt->bindValue(':requisitos', $datos['requisitos'] ?? null);
        $stmt->bindValue(':responsabilidades', $datos['responsabilidades'] ?? null);
        $stmt->bindValue(':beneficios', $datos['beneficios'] ?? null);
        $stmt->bindValue(':salario_min', $datos['salario_min'] ?? null);
        $stmt->bindValue(':salario_max', $datos['salario_max'] ?? null);
        $stmt->bindValue(':ubicacion', $datos['ubicacion'] ?? null);
        $stmt->bindValue(':modalidad', $datos['modalidad']);
        $stmt->bindValue(':tipo_empleo', $datos['tipo_empleo']);
        $stmt->bindValue(':nivel_experiencia', $datos['nivel_experiencia']);
        $stmt->bindValue(':fecha_limite', $datos['fecha_limite'] ?? null);
        $stmt->bindValue(':estado', $datos['estado'] ?? 'activa');
        
        return $stmt->execute();
    }
}