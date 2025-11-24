<?php
/**
 * Portal de Trabajo UTP - Export Controller
 * 
 * Exportación de datos del sistema a formato CSV.
 * Solo accesible por administradores.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../models/Estudiante.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Oferta.php';
require_once __DIR__ . '/../models/Auditoria.php';

class ExportController {
    private $db;
    private $postulacionModel;
    private $estudianteModel;
    private $empresaModel;
    private $ofertaModel;
    private $auditoriaModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->postulacionModel = new Postulacion($this->db);
        $this->estudianteModel = new Estudiante($this->db);
        $this->empresaModel = new Empresa($this->db);
        $this->ofertaModel = new Oferta($this->db);
        $this->auditoriaModel = new Auditoria($this->db);
    }
    
    /**
     * Exportar postulaciones a CSV
     */
    public function exportarPostulaciones() {
        verificarSesion('admin');
        
        // Obtener filtros de fecha
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes actual
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d'); // Hoy
        
        // Validar fechas
        if (!$this->validarFecha($fecha_inicio) || !$this->validarFecha($fecha_fin)) {
            $_SESSION['error'] = 'Fechas inválidas';
            header('Location: ' . BASE_URL . '/views/admin/exportar.php');
            exit();
        }
        
        // Obtener postulaciones
        $postulaciones = $this->postulacionModel->getPostulacionesPorRango($fecha_inicio, $fecha_fin);
        
        // Configurar headers para descarga CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="postulaciones_' . date('Y-m-d_His') . '.csv"');
        
        // Crear archivo de salida
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 (para que Excel lea correctamente los acentos)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir cabeceras
        fputcsv($output, [
            'ID Postulación',
            'Fecha Postulación',
            'Estudiante',
            'Correo Estudiante',
            'Carrera',
            'Oferta',
            'Empresa',
            'Estado',
            'Fecha Actualización'
        ]);
        
        // Escribir datos
        foreach ($postulaciones as $p) {
            fputcsv($output, [
                $p['id_postulacion'],
                date('d/m/Y H:i', strtotime($p['fecha_postulacion'])),
                $p['nombres_estudiante'] . ' ' . $p['apellidos_estudiante'],
                $p['correo_estudiante'],
                $p['carrera'],
                $p['titulo_oferta'],
                $p['nombre_empresa'],
                $this->traducirEstado($p['estado']),
                date('d/m/Y H:i', strtotime($p['fecha_actualizacion']))
            ]);
        }
        
        fclose($output);
        
        // Registrar exportación en auditoría
        $this->auditoriaModel->registrar([
            'tipo_usuario' => 'admin',
            'id_usuario' => $_SESSION['id_usuario'],
            'accion' => 'exportacion_postulaciones_csv',
            'tabla_afectada' => 'postulaciones',
            'datos_nuevos' => json_encode([
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'total_registros' => count($postulaciones)
            ]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        exit();
    }
    
    /**
     * Exportar estudiantes a CSV
     */
    public function exportarEstudiantes() {
        verificarSesion('admin');
        
        // Obtener estudiantes
        $estudiantes = $this->estudianteModel->listarTodos(1000, 0); // Máximo 1000
        
        // Configurar headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="estudiantes_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeceras
        fputcsv($output, [
            'ID',
            'Nombres',
            'Apellidos',
            'Correo',
            'Carrera',
            'Tiene CV',
            'Total Postulaciones',
            'Fecha Registro'
        ]);
        
        // Datos
        foreach ($estudiantes as $e) {
            fputcsv($output, [
                $e['id_estudiante'],
                $e['nombres'],
                $e['apellidos'],
                $e['correo_utp'],
                $e['carrera'],
                ($e['estado_cv'] === 'activo' ? 'Sí' : 'No'),
                $e['total_postulaciones'],
                date('d/m/Y', strtotime($e['fecha_creacion']))
            ]);
        }
        
        fclose($output);
        
        // Auditoría
        $this->auditoriaModel->registrar([
            'tipo_usuario' => 'admin',
            'id_usuario' => $_SESSION['id_usuario'],
            'accion' => 'exportacion_estudiantes_csv',
            'tabla_afectada' => 'estudiantes',
            'datos_nuevos' => json_encode(['total_registros' => count($estudiantes)]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        exit();
    }
    
    /**
     * Exportar ofertas a CSV
     */
    public function exportarOfertas() {
        verificarSesion('admin');
        
        $estado = $_GET['estado'] ?? ''; // Filtrar por estado (opcional)
        
        // Obtener ofertas
        $filtros = [];
        if (!empty($estado)) {
            $filtros['estado'] = $estado;
        }
        
        $ofertas = $this->ofertaModel->getOfertasActivas($filtros, 1, 1000);
        
        // Configurar headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ofertas_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeceras
        fputcsv($output, [
            'ID',
            'Título',
            'Empresa',
            'Modalidad',
            'Tipo Empleo',
            'Nivel',
            'Ubicación',
            'Salario Min',
            'Salario Max',
            'Fecha Límite',
            'Estado',
            'Total Postulaciones',
            'Fecha Publicación'
        ]);
        
        // Datos
        foreach ($ofertas as $o) {
            fputcsv($output, [
                $o['id_oferta'],
                $o['titulo'],
                $o['empresa'],
                ucfirst($o['modalidad']),
                str_replace('_', ' ', ucfirst($o['tipo_empleo'])),
                ucfirst($o['nivel_experiencia']),
                $o['ubicacion'],
                $o['salario_min'] ? '$' . number_format($o['salario_min'], 2) : 'N/A',
                $o['salario_max'] ? '$' . number_format($o['salario_max'], 2) : 'N/A',
                date('d/m/Y', strtotime($o['fecha_limite'])),
                ucfirst($o['estado']),
                $o['total_postulaciones'] ?? 0,
                date('d/m/Y', strtotime($o['fecha_publicacion']))
            ]);
        }
        
        fclose($output);
        
        // Auditoría
        $this->auditoriaModel->registrar([
            'tipo_usuario' => 'admin',
            'id_usuario' => $_SESSION['id_usuario'],
            'accion' => 'exportacion_ofertas_csv',
            'tabla_afectada' => 'ofertas',
            'datos_nuevos' => json_encode(['total_registros' => count($ofertas), 'filtros' => $filtros]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        exit();
    }
    
    /**
     * Exportar empresas a CSV
     */
    public function exportarEmpresas() {
        verificarSesion('admin');
        
        $empresas = $this->empresaModel->listarTodas(null, 1000, 0);
        
        // Configurar headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="empresas_' . date('Y-m-d_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeceras
        fputcsv($output, [
            'ID',
            'Nombre Legal',
            'Nombre Comercial',
            'Sector',
            'Email',
            'Teléfono',
            'Sitio Web',
            'Total Ofertas',
            'Ofertas Activas',
            'Estado',
            'Fecha Registro'
        ]);
        
        // Datos
        foreach ($empresas as $e) {
            fputcsv($output, [
                $e['id_empresa'],
                $e['nombre_legal'],
                $e['nombre_comercial'],
                $e['sector'],
                $e['email_contacto'],
                $e['telefono'],
                $e['sitio_web'],
                $e['total_ofertas'],
                $e['ofertas_activas'],
                ucfirst($e['estado']),
                date('d/m/Y', strtotime($e['fecha_registro']))
            ]);
        }
        
        fclose($output);
        
        // Auditoría
        $this->auditoriaModel->registrar([
            'tipo_usuario' => 'admin',
            'id_usuario' => $_SESSION['id_usuario'],
            'accion' => 'exportacion_empresas_csv',
            'tabla_afectada' => 'empresas',
            'datos_nuevos' => json_encode(['total_registros' => count($empresas)]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        exit();
    }
    
    /**
     * Validar formato de fecha
     * 
     * @param string $fecha Fecha en formato Y-m-d
     * @return bool True si es válida
     */
    private function validarFecha($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
    
    /**
     * Traducir estado de postulación a español
     * 
     * @param string $estado Estado en inglés
     * @return string Estado en español
     */
    private function traducirEstado($estado) {
        $estados = [
            'en_revision' => 'En Revisión',
            'aceptada' => 'Aceptada',
            'rechazada' => 'Rechazada'
        ];
        
        return $estados[$estado] ?? $estado;
    }
}

// Manejar acciones desde URL
if (isset($_GET['action'])) {
    $controller = new ExportController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}