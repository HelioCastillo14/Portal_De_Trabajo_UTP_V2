<?php
/**
 * Portal de Trabajo UTP - Notificacion Controller
 * 
 * Gestión de notificaciones internas para estudiantes y administradores.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Notificacion.php';

class NotificacionController {
    private $db;
    private $notificacionModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->notificacionModel = new Notificacion($this->db);
    }
    
    /**
     * Obtener notificaciones del usuario actual (API JSON)
     */
    public function obtenerMisNotificaciones() {
        if (!estaAutenticado()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit();
        }
        
        $tipo_usuario = $_SESSION['tipo_usuario'];
        $id_usuario = $_SESSION['id_usuario'];
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
        
        $notificaciones = $this->notificacionModel->getNotificacionesUsuario($tipo_usuario, $id_usuario, $limite);
        $no_leidas = $this->notificacionModel->contarNoLeidas($tipo_usuario, $id_usuario);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'notificaciones' => $notificaciones,
            'total_no_leidas' => $no_leidas
        ]);
        exit();
    }
    
    /**
     * Obtener contador de notificaciones no leídas (API JSON)
     */
    public function contarNoLeidas() {
        if (!estaAutenticado()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'total' => 0]);
            exit();
        }
        
        $tipo_usuario = $_SESSION['tipo_usuario'];
        $id_usuario = $_SESSION['id_usuario'];
        
        $total = $this->notificacionModel->contarNoLeidas($tipo_usuario, $id_usuario);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'total' => $total
        ]);
        exit();
    }
    
    /**
     * Marcar notificación como leída
     */
    public function marcarLeida() {
        if (!estaAutenticado()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_notificacion = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id_notificacion > 0) {
                // Verificar que la notificación pertenece al usuario
                $notificacion = $this->notificacionModel->findById($id_notificacion);
                
                if ($notificacion && 
                    $notificacion['tipo_usuario'] === $_SESSION['tipo_usuario'] && 
                    $notificacion['id_usuario'] == $_SESSION['id_usuario']) {
                    
                    if ($this->notificacionModel->marcarComoLeida($id_notificacion)) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true]);
                        exit();
                    }
                }
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error al marcar notificación']);
        exit();
    }
    
    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasLeidas() {
        if (!estaAutenticado()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tipo_usuario = $_SESSION['tipo_usuario'];
            $id_usuario = $_SESSION['id_usuario'];
            
            if ($this->notificacionModel->marcarTodasComoLeidas($tipo_usuario, $id_usuario)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit();
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error al marcar notificaciones']);
        exit();
    }
    
    /**
     * Eliminar notificación
     */
    public function eliminar() {
        if (!estaAutenticado()) {
            $_SESSION['error'] = 'Debe iniciar sesión';
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                $this->redirigirSegunRol();
                exit();
            }
            
            $id_notificacion = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id_notificacion > 0) {
                // Verificar que la notificación pertenece al usuario
                $notificacion = $this->notificacionModel->findById($id_notificacion);
                
                if ($notificacion && 
                    $notificacion['tipo_usuario'] === $_SESSION['tipo_usuario'] && 
                    $notificacion['id_usuario'] == $_SESSION['id_usuario']) {
                    
                    if ($this->notificacionModel->delete($id_notificacion)) {
                        $_SESSION['exito'] = 'Notificación eliminada';
                    } else {
                        $_SESSION['error'] = 'Error al eliminar notificación';
                    }
                }
            }
            
            $this->redirigirSegunRol();
        }
    }
    
    /**
     * Eliminar todas las notificaciones del usuario
     */
    public function eliminarTodas() {
        if (!estaAutenticado()) {
            $_SESSION['error'] = 'Debe iniciar sesión';
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                $this->redirigirSegunRol();
                exit();
            }
            
            $tipo_usuario = $_SESSION['tipo_usuario'];
            $id_usuario = $_SESSION['id_usuario'];
            
            if ($this->notificacionModel->eliminarTodasUsuario($tipo_usuario, $id_usuario)) {
                $_SESSION['exito'] = 'Todas las notificaciones eliminadas';
            } else {
                $_SESSION['error'] = 'Error al eliminar notificaciones';
            }
            
            $this->redirigirSegunRol();
        }
    }
    
    /**
     * Redirigir según el rol del usuario
     */
    private function redirigirSegunRol() {
        if ($_SESSION['tipo_usuario'] === 'estudiante') {
            header('Location: ' . BASE_URL . '/views/estudiante/notificaciones.php');
        } else {
            header('Location: ' . BASE_URL . '/views/admin/notificaciones.php');
        }
        exit();
    }
}

// Manejar acciones desde URL
if (isset($_GET['action'])) {
    $controller = new NotificacionController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}