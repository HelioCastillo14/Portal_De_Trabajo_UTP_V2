<?php
/**
 * Portal de Trabajo UTP - Admin Controller
 * 
 * Dashboard administrativo con estadísticas y métricas del sistema.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Estudiante.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Oferta.php';
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../models/Auditoria.php';

class AdminController {
    private $db;
    private $estudianteModel;
    private $empresaModel;
    private $ofertaModel;
    private $postulacionModel;
    private $auditoriaModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->estudianteModel = new Estudiante($this->db);
        $this->empresaModel = new Empresa($this->db);
        $this->ofertaModel = new Oferta($this->db);
        $this->postulacionModel = new Postulacion($this->db);
        $this->auditoriaModel = new Auditoria($this->db);
    }
    
    /**
     * Obtener datos del dashboard admin
     */
    public function obtenerDatosDashboard() {
        verificarSesion('admin');
        
        // Estadísticas generales
        $stats = [
            'estudiantes' => $this->estudianteModel->obtenerEstadisticas(),
            'empresas' => $this->empresaModel->obtenerEstadisticas(),
            'ofertas' => $this->ofertaModel->obtenerEstadisticas(),
            'postulaciones' => $this->postulacionModel->obtenerEstadisticas()
        ];
        
        // Postulaciones recientes
        $postulaciones_recientes = $this->postulacionModel->getPostulacionesRecientes(10);
        
        // Ofertas recientes
        $ofertas_recientes = $this->ofertaModel->getOfertasRecientes(5);
        
        return [
            'stats' => $stats,
            'postulaciones_recientes' => $postulaciones_recientes,
            'ofertas_recientes' => $ofertas_recientes
        ];
    }
    
    /**
     * Generar reporte de postulaciones por mes
     */
    public function reportePostulacionesMes() {
        verificarSesion('admin');
        
        $mes = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');
        
        $fecha_inicio = $mes . '-01';
        $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
        
        $postulaciones = $this->postulacionModel->getPostulacionesPorRango($fecha_inicio, $fecha_fin);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'mes' => $mes,
            'total' => count($postulaciones),
            'postulaciones' => $postulaciones
        ]);
        exit();
    }
    
    /**
     * Obtener métricas para gráficos (API JSON)
     */
    public function obtenerMetricas() {
        verificarSesion('admin');
        
        $tipo = $_GET['tipo'] ?? 'general';
        
        switch ($tipo) {
            case 'postulaciones_por_estado':
                $stats = $this->postulacionModel->obtenerEstadisticas();
                $metricas = [
                    'labels' => ['En Revisión', 'Aceptadas', 'Rechazadas'],
                    'data' => [
                        $stats['en_revision'],
                        $stats['aceptadas'],
                        $stats['rechazadas']
                    ]
                ];
                break;
                
            case 'ofertas_por_modalidad':
                $query = "SELECT modalidad, COUNT(*) as total 
                          FROM ofertas 
                          WHERE estado = 'activa' 
                          GROUP BY modalidad";
                $stmt = $this->db->query($query);
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $metricas = [
                    'labels' => array_column($resultados, 'modalidad'),
                    'data' => array_column($resultados, 'total')
                ];
                break;
                
            case 'postulaciones_ultimos_7_dias':
                $query = "SELECT DATE(fecha_postulacion) as fecha, COUNT(*) as total 
                          FROM postulaciones 
                          WHERE fecha_postulacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                          GROUP BY DATE(fecha_postulacion)
                          ORDER BY fecha ASC";
                $stmt = $this->db->query($query);
                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $metricas = [
                    'labels' => array_column($resultados, 'fecha'),
                    'data' => array_column($resultados, 'total')
                ];
                break;
                
            default:
                $metricas = ['error' => 'Tipo de métrica no válido'];
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'metricas' => $metricas
        ]);
        exit();
    }
    
    /**
     * Obtener resumen de actividad reciente
     */
    public function obtenerActividadReciente() {
        verificarSesion('admin');
        
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
        
        $actividades = $this->auditoriaModel->obtenerRegistros([], $limite);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'actividades' => $actividades
        ]);
        exit();
    }
    
    /**
     * Buscar en el sistema (búsqueda global)
     */
    public function busquedaGlobal() {
        verificarSesion('admin');
        
        $termino = $_GET['q'] ?? '';
        
        if (strlen($termino) < 3) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Mínimo 3 caracteres'
            ]);
            exit();
        }
        
        $resultados = [];
        
        // Buscar en estudiantes
        $query = "SELECT id_estudiante as id, 
                  CONCAT(nombres, ' ', apellidos) as nombre, 
                  correo_utp as detalle,
                  'estudiante' as tipo
                  FROM estudiantes 
                  WHERE (nombres LIKE :termino OR apellidos LIKE :termino OR correo_utp LIKE :termino)
                  AND activo = TRUE
                  LIMIT 5";
        $stmt = $this->db->prepare($query);
        $termino_busqueda = '%' . $termino . '%';
        $stmt->bindParam(':termino', $termino_busqueda);
        $stmt->execute();
        $resultados['estudiantes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar en empresas
        $query = "SELECT id_empresa as id, 
                  nombre_comercial as nombre, 
                  sector as detalle,
                  'empresa' as tipo
                  FROM empresas 
                  WHERE nombre_comercial LIKE :termino OR nombre_legal LIKE :termino
                  LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':termino', $termino_busqueda);
        $stmt->execute();
        $resultados['empresas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Buscar en ofertas
        $query = "SELECT id_oferta as id, 
                  titulo as nombre, 
                  ubicacion as detalle,
                  'oferta' as tipo
                  FROM ofertas 
                  WHERE titulo LIKE :termino OR descripcion LIKE :termino
                  LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':termino', $termino_busqueda);
        $stmt->execute();
        $resultados['ofertas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'resultados' => $resultados
        ]);
        exit();
    }
    
    /**
     * Generar reporte general (para descargar)
     */
    public function generarReporteGeneral() {
        verificarSesion('admin');
        
        $stats = [
            'estudiantes' => $this->estudianteModel->obtenerEstadisticas(),
            'empresas' => $this->empresaModel->obtenerEstadisticas(),
            'ofertas' => $this->ofertaModel->obtenerEstadisticas(),
            'postulaciones' => $this->postulacionModel->obtenerEstadisticas()
        ];
        
        // Registrar generación de reporte en auditoría
        $this->auditoriaModel->registrar([
            'tipo_usuario' => 'admin',
            'id_usuario' => $_SESSION['id_usuario'],
            'accion' => 'generar_reporte_general',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'reporte' => $stats,
            'fecha_generacion' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
}

// Manejar acciones desde URL
if (isset($_GET['action'])) {
    $controller = new AdminController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}