<?php
/**
 * Portal de Trabajo UTP - Estudiante Controller
 * 
 * Gestión de perfil del estudiante, subida de CV y visualización de postulaciones.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Estudiante.php';
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../models/Auditoria.php';

class EstudianteController {
    private $db;
    private $estudianteModel;
    private $postulacionModel;
    private $auditoriaModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->estudianteModel = new Estudiante($this->db);
        $this->postulacionModel = new Postulacion($this->db);
        $this->auditoriaModel = new Auditoria($this->db);
    }
    
    /**
     * Actualizar perfil del estudiante
     */
    public function actualizarPerfil() {
        verificarSesion('estudiante');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
                exit();
            }
            
            $id_estudiante = $_SESSION['id_usuario'];
            
            // Datos del estudiante anterior (para auditoría)
            $estudiante_anterior = $this->estudianteModel->findById($id_estudiante);
            
            // Sanitizar datos
            $datos = [
                'nombres' => sanitizar($_POST['nombres']),
                'apellidos' => sanitizar($_POST['apellidos']),
                'carrera' => sanitizar($_POST['carrera']),
                'descripcion_perfil' => sanitizar($_POST['descripcion_perfil'] ?? '')
            ];
            
            // Validar campos requeridos
            if (empty($datos['nombres']) || empty($datos['apellidos'])) {
                $_SESSION['error'] = 'Nombres y apellidos son obligatorios';
                header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
                exit();
            }
            
            // Actualizar perfil
            if ($this->estudianteModel->update($id_estudiante, $datos)) {
                // Actualizar sesión
                $_SESSION['nombres'] = $datos['nombres'];
                $_SESSION['apellidos'] = $datos['apellidos'];
                
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'estudiante',
                    'id_usuario' => $id_estudiante,
                    'accion' => 'actualizar_perfil',
                    'tabla_afectada' => 'estudiantes',
                    'id_registro_afectado' => $id_estudiante,
                    'datos_anteriores' => json_encode($estudiante_anterior),
                    'datos_nuevos' => json_encode($datos),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Perfil actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el perfil';
            }
            
            header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
            exit();
        }
    }
    
    /**
     * Subir o actualizar CV del estudiante
     */
    public function subirCV() {
        verificarSesion('estudiante');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
                exit();
            }
            
            $id_estudiante = $_SESSION['id_usuario'];
            
            // Validar que se haya subido un archivo
            if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = 'Error al subir el archivo';
                header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
                exit();
            }
            
            $archivo = $_FILES['cv'];
            
            // Validar tipo de archivo (debe ser PDF)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $archivo['tmp_name']);
            finfo_close($finfo);
            
            if ($mime !== 'application/pdf') {
                $_SESSION['error'] = 'Solo se permiten archivos PDF';
                header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
                exit();
            }
            
            // Validar tamaño (5MB máximo)
            if ($archivo['size'] > MAX_CV_SIZE) {
                $_SESSION['error'] = 'El archivo no puede superar los 5MB';
                header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
                exit();
            }
            
            // Generar nombre único
            $nombre_archivo = generarNombreArchivoUnico($archivo['name'], $id_estudiante);
            $ruta_destino = UPLOAD_CV_PATH . $nombre_archivo;
            
            // Eliminar CV anterior si existe
            $estudiante = $this->estudianteModel->findById($id_estudiante);
            if ($estudiante['cv_ruta']) {
                $ruta_anterior = UPLOAD_CV_PATH . $estudiante['cv_ruta'];
                if (file_exists($ruta_anterior)) {
                    unlink($ruta_anterior);
                }
            }
            
            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                // Calcular hash del archivo
                $cv_hash = hash_file('sha256', $ruta_destino);
                
                // Actualizar base de datos
                if ($this->estudianteModel->actualizarCV($id_estudiante, $nombre_archivo, $cv_hash)) {
                    // Auditoría
                    $this->auditoriaModel->registrar([
                        'tipo_usuario' => 'estudiante',
                        'id_usuario' => $id_estudiante,
                        'accion' => 'subida_cv',
                        'tabla_afectada' => 'estudiantes',
                        'id_registro_afectado' => $id_estudiante,
                        'datos_nuevos' => json_encode(['cv_ruta' => $nombre_archivo, 'cv_hash' => $cv_hash]),
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]);
                    
                    $_SESSION['exito'] = 'CV subido correctamente';
                } else {
                    $_SESSION['error'] = 'Error al guardar CV en la base de datos';
                }
            } else {
                $_SESSION['error'] = 'Error al guardar el archivo';
            }
            
            header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
            exit();
        }
    }
    
    /**
     * Eliminar CV del estudiante
     */
    public function eliminarCV() {
        verificarSesion('estudiante');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
                exit();
            }
            
            $id_estudiante = $_SESSION['id_usuario'];
            $estudiante = $this->estudianteModel->findById($id_estudiante);
            
            if ($estudiante['cv_ruta']) {
                // Eliminar archivo físico
                $ruta_archivo = UPLOAD_CV_PATH . $estudiante['cv_ruta'];
                if (file_exists($ruta_archivo)) {
                    unlink($ruta_archivo);
                }
                
                // Actualizar estado en BD
                if ($this->estudianteModel->eliminarCV($id_estudiante)) {
                    // Auditoría
                    $this->auditoriaModel->registrar([
                        'tipo_usuario' => 'estudiante',
                        'id_usuario' => $id_estudiante,
                        'accion' => 'eliminar_cv',
                        'tabla_afectada' => 'estudiantes',
                        'id_registro_afectado' => $id_estudiante,
                        'datos_anteriores' => json_encode(['cv_ruta' => $estudiante['cv_ruta']]),
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]);
                    
                    $_SESSION['exito'] = 'CV eliminado correctamente';
                } else {
                    $_SESSION['error'] = 'Error al eliminar el CV';
                }
            }
            
            header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
            exit();
        }
    }
    
    /**
     * Obtener datos del perfil (para dashboard)
     */
    public function obtenerDatosDashboard() {
        verificarSesion('estudiante');
        
        $id_estudiante = $_SESSION['id_usuario'];
        
        // Obtener datos del estudiante con estadísticas
        $estudiante = $this->estudianteModel->getConPostulaciones($id_estudiante);
        
        // Obtener postulaciones recientes
        $postulaciones_recientes = $this->postulacionModel->getPostulacionesEstudiante($id_estudiante, 5);
        
        return [
            'estudiante' => $estudiante,
            'postulaciones_recientes' => $postulaciones_recientes
        ];
    }
    
    /**
     * Descargar CV propio
     */
    public function descargarCV() {
        verificarSesion('estudiante');
        
        $id_estudiante = $_SESSION['id_usuario'];
        $estudiante = $this->estudianteModel->findById($id_estudiante);
        
        if ($estudiante['cv_ruta'] && $estudiante['estado_cv'] === 'activo') {
            $ruta_archivo = UPLOAD_CV_PATH . $estudiante['cv_ruta'];
            
            if (file_exists($ruta_archivo)) {
                // Headers para descarga
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="CV_' . $estudiante['nombres'] . '_' . $estudiante['apellidos'] . '.pdf"');
                header('Content-Length: ' . filesize($ruta_archivo));
                
                readfile($ruta_archivo);
                exit();
            }
        }
        
        $_SESSION['error'] = 'CV no disponible';
        header('Location: ' . BASE_URL . '/views/estudiante/perfil.php');
        exit();
    }
}

// Manejar acciones desde URL
if (isset($_GET['action'])) {
    $controller = new EstudianteController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}