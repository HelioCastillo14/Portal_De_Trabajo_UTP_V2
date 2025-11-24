<?php
/**
 * Portal de Trabajo UTP - Empresa Controller
 * 
 * Gestión completa de empresas: CRUD y subida de logos.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Auditoria.php';

class EmpresaController {
    private $db;
    private $empresaModel;
    private $auditoriaModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->empresaModel = new Empresa($this->db);
        $this->auditoriaModel = new Auditoria($this->db);
    }
    
    /**
     * Crear nueva empresa (admin)
     */
    public function crear() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/empresas/crear.php');
                exit();
            }
            
            // Sanitizar datos
            $datos = [
                'nombre_legal' => sanitizar($_POST['nombre_legal']),
                'nombre_comercial' => sanitizar($_POST['nombre_comercial']),
                'descripcion' => sanitizar($_POST['descripcion'] ?? ''),
                'sector' => sanitizar($_POST['sector'] ?? ''),
                'sitio_web' => sanitizar($_POST['sitio_web'] ?? ''),
                'telefono' => sanitizar($_POST['telefono'] ?? ''),
                'email_contacto' => sanitizar($_POST['email_contacto'] ?? ''),
                'direccion' => sanitizar($_POST['direccion'] ?? ''),
                'logo' => 'placeholder-logo.png'
            ];
            
            // Validar campos requeridos
            if (empty($datos['nombre_legal'])) {
                $_SESSION['error'] = 'El nombre legal es obligatorio';
                header('Location: ' . BASE_URL . '/views/admin/empresas/crear.php');
                exit();
            }
            
            // Procesar logo si se subió
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $resultado_logo = $this->procesarLogo($_FILES['logo']);
                if ($resultado_logo['success']) {
                    $datos['logo'] = $resultado_logo['nombre_archivo'];
                } else {
                    $_SESSION['error'] = $resultado_logo['error'];
                    header('Location: ' . BASE_URL . '/views/admin/empresas/crear.php');
                    exit();
                }
            }
            
            // Crear empresa
            $id_empresa = $this->empresaModel->create($datos);
            
            if ($id_empresa) {
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'crear_empresa',
                    'tabla_afectada' => 'empresas',
                    'id_registro_afectado' => $id_empresa,
                    'datos_nuevos' => json_encode($datos),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Empresa creada exitosamente';
                header('Location: ' . BASE_URL . '/views/admin/empresas/index.php');
                exit();
            } else {
                $_SESSION['error'] = 'Error al crear la empresa';
                header('Location: ' . BASE_URL . '/views/admin/empresas/crear.php');
                exit();
            }
        }
    }
    
    /**
     * Actualizar empresa (admin)
     */
    public function actualizar() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/empresas/index.php');
                exit();
            }
            
            $id_empresa = (int)$_POST['id_empresa'];
            
            // Obtener datos anteriores para auditoría
            $empresa_anterior = $this->empresaModel->findById($id_empresa);
            
            // Sanitizar datos
            $datos = [
                'nombre_legal' => sanitizar($_POST['nombre_legal']),
                'nombre_comercial' => sanitizar($_POST['nombre_comercial']),
                'descripcion' => sanitizar($_POST['descripcion'] ?? ''),
                'sector' => sanitizar($_POST['sector'] ?? ''),
                'sitio_web' => sanitizar($_POST['sitio_web'] ?? ''),
                'telefono' => sanitizar($_POST['telefono'] ?? ''),
                'email_contacto' => sanitizar($_POST['email_contacto'] ?? ''),
                'direccion' => sanitizar($_POST['direccion'] ?? ''),
                'estado' => $_POST['estado'] ?? 'activa'
            ];
            
            // Procesar logo si se subió uno nuevo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $resultado_logo = $this->procesarLogo($_FILES['logo'], $id_empresa);
                if ($resultado_logo['success']) {
                    $datos['logo'] = $resultado_logo['nombre_archivo'];
                    
                    // Eliminar logo anterior si no es placeholder
                    if ($empresa_anterior['logo'] !== 'placeholder-logo.png') {
                        $ruta_anterior = UPLOAD_LOGOS_PATH . $empresa_anterior['logo'];
                        if (file_exists($ruta_anterior)) {
                            unlink($ruta_anterior);
                        }
                    }
                }
            }
            
            // Actualizar empresa
            if ($this->empresaModel->update($id_empresa, $datos)) {
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'actualizar_empresa',
                    'tabla_afectada' => 'empresas',
                    'id_registro_afectado' => $id_empresa,
                    'datos_anteriores' => json_encode($empresa_anterior),
                    'datos_nuevos' => json_encode($datos),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Empresa actualizada correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar la empresa';
            }
            
            header('Location: ' . BASE_URL . '/views/admin/empresas/index.php');
            exit();
        }
    }
    
    /**
     * Eliminar empresa (admin)
     */
    public function eliminar() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/empresas/index.php');
                exit();
            }
            
            $id_empresa = (int)$_POST['id_empresa'];
            
            // Obtener datos para auditoría
            $empresa = $this->empresaModel->findById($id_empresa);
            
            // Verificar si tiene ofertas activas
            $ofertas = $this->empresaModel->getConOfertas($id_empresa);
            $tiene_ofertas_activas = false;
            
            if (!empty($ofertas['ofertas'])) {
                foreach ($ofertas['ofertas'] as $oferta) {
                    if ($oferta['estado_oferta'] === 'activa') {
                        $tiene_ofertas_activas = true;
                        break;
                    }
                }
            }
            
            if ($tiene_ofertas_activas) {
                $_SESSION['error'] = 'No se puede eliminar una empresa con ofertas activas';
                header('Location: ' . BASE_URL . '/views/admin/empresas/index.php');
                exit();
            }
            
            // Eliminar empresa
            if ($this->empresaModel->delete($id_empresa)) {
                // Eliminar logo si no es placeholder
                if ($empresa['logo'] !== 'placeholder-logo.png') {
                    $ruta_logo = UPLOAD_LOGOS_PATH . $empresa['logo'];
                    if (file_exists($ruta_logo)) {
                        unlink($ruta_logo);
                    }
                }
                
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'eliminar_empresa',
                    'tabla_afectada' => 'empresas',
                    'id_registro_afectado' => $id_empresa,
                    'datos_anteriores' => json_encode($empresa),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Empresa eliminada correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar la empresa';
            }
            
            header('Location: ' . BASE_URL . '/views/admin/empresas/index.php');
            exit();
        }
    }
    
    /**
     * Procesar subida de logo
     * 
     * @param array $archivo Archivo $_FILES['logo']
     * @param int $id_empresa ID de la empresa (opcional, para nombre del archivo)
     * @return array Resultado con success y nombre_archivo o error
     */
    private function procesarLogo($archivo, $id_empresa = null) {
        // Validar tipo de archivo
        $extensiones_permitidas = ALLOWED_IMAGE_EXTENSIONS;
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensiones_permitidas)) {
            return ['success' => false, 'error' => 'Solo se permiten imágenes JPG, PNG o JPEG'];
        }
        
        // Validar tamaño (1MB)
        if ($archivo['size'] > MAX_LOGO_SIZE) {
            return ['success' => false, 'error' => 'La imagen no puede superar 1MB'];
        }
        
        // Validar que sea imagen real
        $check = getimagesize($archivo['tmp_name']);
        if ($check === false) {
            return ['success' => false, 'error' => 'El archivo no es una imagen válida'];
        }
        
        // Generar nombre único
        $nombre_archivo = ($id_empresa ? 'empresa_' . $id_empresa : 'empresa_' . uniqid()) . '.' . $extension;
        $ruta_destino = UPLOAD_LOGOS_PATH . $nombre_archivo;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            return ['success' => true, 'nombre_archivo' => $nombre_archivo];
        } else {
            return ['success' => false, 'error' => 'Error al guardar el logo'];
        }
    }
    
    /**
     * Cambiar estado de empresa (admin)
     */
    public function cambiarEstado() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/empresas/index.php');
                exit();
            }
            
            $id_empresa = (int)$_POST['id_empresa'];
            $nuevo_estado = $_POST['estado'];
            
            $empresa_anterior = $this->empresaModel->findById($id_empresa);
            
            if ($this->empresaModel->cambiarEstado($id_empresa, $nuevo_estado)) {
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'cambiar_estado_empresa',
                    'tabla_afectada' => 'empresas',
                    'id_registro_afectado' => $id_empresa,
                    'datos_anteriores' => json_encode(['estado' => $empresa_anterior['estado']]),
                    'datos_nuevos' => json_encode(['estado' => $nuevo_estado]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Estado actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al cambiar el estado';
            }
            
            header('Location: ' . BASE_URL . '/views/admin/empresas/index.php');
            exit();
        }
    }
}

// Manejar acciones desde URL
if (isset($_GET['action'])) {
    $controller = new EmpresaController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}