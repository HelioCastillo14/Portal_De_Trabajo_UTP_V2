<?php
/**
 * Portal de Trabajo UTP - Postulacion Controller
 * 
 * Gestión de postulaciones: crear, cambiar estados, ver postulaciones.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../models/Oferta.php';
require_once __DIR__ . '/../models/Notificacion.php';
require_once __DIR__ . '/../models/Auditoria.php';

class PostulacionController {
    private $db;
    private $postulacionModel;
    private $ofertaModel;
    private $notificacionModel;
    private $auditoriaModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->postulacionModel = new Postulacion($this->db);
        $this->ofertaModel = new Oferta($this->db);
        $this->notificacionModel = new Notificacion($this->db);
        $this->auditoriaModel = new Auditoria($this->db);
    }
    
    /**
     * Postular a una oferta (estudiante)
     */
    public function postular() {
        verificarSesion('estudiante');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/estudiante/ofertas.php');
                exit();
            }
            
            $id_estudiante = $_SESSION['id_usuario'];
            $id_oferta = (int)$_POST['id_oferta'];
            
            // Verificar que no exista postulación previa
            if ($this->postulacionModel->existePostulacion($id_estudiante, $id_oferta)) {
                $_SESSION['error'] = 'Ya te has postulado a esta oferta';
                header('Location: ' . BASE_URL . '/views/estudiante/ofertas.php');
                exit();
            }
            
            // Verificar que la oferta esté activa y no vencida
            $oferta = $this->ofertaModel->findById($id_oferta);
            
            if (!$oferta || $oferta['estado'] !== 'activa' || $oferta['fecha_limite'] < date('Y-m-d')) {
                $_SESSION['error'] = 'Esta oferta ya no está disponible';
                header('Location: ' . BASE_URL . '/views/estudiante/ofertas.php');
                exit();
            }
            
            // Crear postulación
            $id_postulacion = $this->postulacionModel->create([
                'id_estudiante' => $id_estudiante,
                'id_oferta' => $id_oferta,
                'estado' => 'en_revision'
            ]);
            
            if ($id_postulacion) {
                // Crear notificación para todos los admins
                $this->notificacionModel->notificarTodosAdmins(
                    'Nueva postulación recibida',
                    $_SESSION['nombres'] . ' ' . $_SESSION['apellidos'] . ' se ha postulado a: ' . $oferta['titulo'],
                    'postulacion_nueva',
                    $id_postulacion
                );
                
                // Crear notificación para el estudiante
                $this->notificacionModel->create([
                    'tipo_usuario' => 'estudiante',
                    'id_usuario' => $id_estudiante,
                    'titulo' => 'Postulación enviada',
                    'mensaje' => 'Tu postulación a "' . $oferta['titulo'] . '" ha sido registrada exitosamente',
                    'tipo' => 'sistema',
                    'id_relacionado' => $id_postulacion
                ]);
                
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'estudiante',
                    'id_usuario' => $id_estudiante,
                    'accion' => 'postulacion_creada',
                    'tabla_afectada' => 'postulaciones',
                    'id_registro_afectado' => $id_postulacion,
                    'datos_nuevos' => json_encode(['id_oferta' => $id_oferta, 'estado' => 'en_revision']),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = '¡Postulación enviada con éxito!';
                header('Location: ' . BASE_URL . '/views/estudiante/mis_postulaciones.php');
                exit();
            } else {
                $_SESSION['error'] = 'Error al enviar la postulación';
                header('Location: ' . BASE_URL . '/views/estudiante/ofertas.php');
                exit();
            }
        }
    }
    
    /**
     * Cambiar estado de postulación (solo admin)
     */
    public function cambiarEstado() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/postulaciones/gestionar.php');
                exit();
            }
            
            $id_postulacion = (int)$_POST['id_postulacion'];
            $nuevo_estado = $_POST['estado'];
            
            // Validar estado
            $estados_validos = ['en_revision', 'aceptada', 'rechazada'];
            if (!in_array($nuevo_estado, $estados_validos)) {
                $_SESSION['error'] = 'Estado inválido';
                header('Location: ' . BASE_URL . '/views/admin/postulaciones/gestionar.php');
                exit();
            }
            
            // Obtener datos de la postulación
            $postulacion = $this->postulacionModel->findById($id_postulacion);
            $estado_anterior = $postulacion['estado'];
            
            // Actualizar estado
            if ($this->postulacionModel->cambiarEstado($id_postulacion, $nuevo_estado)) {
                // Obtener datos de la oferta
                $oferta = $this->ofertaModel->findById($postulacion['id_oferta']);
                
                // Mensajes según estado
                $mensajes = [
                    'aceptada' => 'Tu postulación a "' . $oferta['titulo'] . '" ha sido ACEPTADA. ¡Felicitaciones!',
                    'rechazada' => 'Tu postulación a "' . $oferta['titulo'] . '" ha sido revisada',
                    'en_revision' => 'Tu postulación a "' . $oferta['titulo'] . '" está en revisión'
                ];
                
                // Crear notificación para el estudiante
                $this->notificacionModel->create([
                    'tipo_usuario' => 'estudiante',
                    'id_usuario' => $postulacion['id_estudiante'],
                    'titulo' => 'Actualización de postulación',
                    'mensaje' => $mensajes[$nuevo_estado],
                    'tipo' => 'cambio_estado',
                    'id_relacionado' => $id_postulacion
                ]);
                
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'cambio_estado_postulacion',
                    'tabla_afectada' => 'postulaciones',
                    'id_registro_afectado' => $id_postulacion,
                    'datos_anteriores' => json_encode(['estado' => $estado_anterior]),
                    'datos_nuevos' => json_encode(['estado' => $nuevo_estado]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Estado actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar el estado';
            }
            
            // Redirigir según donde se hizo el cambio
            $redirect = $_POST['redirect'] ?? 'gestionar';
            if ($redirect === 'detalle_oferta') {
                header('Location: ' . BASE_URL . '/views/admin/ofertas/detalle.php?id=' . $postulacion['id_oferta']);
            } else {
                header('Location: ' . BASE_URL . '/views/admin/postulaciones/gestionar.php');
            }
            exit();
        }
    }
    
    /**
     * Cancelar postulación (estudiante)
     */
    public function cancelar() {
        verificarSesion('estudiante');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/estudiante/mis_postulaciones.php');
                exit();
            }
            
            $id_postulacion = (int)$_POST['id_postulacion'];
            $id_estudiante = $_SESSION['id_usuario'];
            
            // Verificar que la postulación pertenece al estudiante
            $postulacion = $this->postulacionModel->findById($id_postulacion);
            
            if ($postulacion['id_estudiante'] != $id_estudiante) {
                $_SESSION['error'] = 'No tienes permiso para cancelar esta postulación';
                header('Location: ' . BASE_URL . '/views/estudiante/mis_postulaciones.php');
                exit();
            }
            
            // Solo se puede cancelar si está en revisión
            if ($postulacion['estado'] !== 'en_revision') {
                $_SESSION['error'] = 'Solo puedes cancelar postulaciones en revisión';
                header('Location: ' . BASE_URL . '/views/estudiante/mis_postulaciones.php');
                exit();
            }
            
            // Eliminar postulación
            if ($this->postulacionModel->delete($id_postulacion)) {
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'estudiante',
                    'id_usuario' => $id_estudiante,
                    'accion' => 'cancelar_postulacion',
                    'tabla_afectada' => 'postulaciones',
                    'id_registro_afectado' => $id_postulacion,
                    'datos_anteriores' => json_encode($postulacion),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Postulación cancelada correctamente';
            } else {
                $_SESSION['error'] = 'Error al cancelar la postulación';
            }
            
            header('Location: ' . BASE_URL . '/views/estudiante/mis_postulaciones.php');
            exit();
        }
    }
    
    /**
     * Obtener postulaciones del estudiante (API JSON para AJAX)
     */
    public function misPostulacionesJSON() {
        verificarSesion('estudiante');
        
        $id_estudiante = $_SESSION['id_usuario'];
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 10;
        $offset = ($pagina - 1) * $limite;
        
        $postulaciones = $this->postulacionModel->getPostulacionesEstudiante($id_estudiante, $limite, $offset);
        $total = $this->postulacionModel->contarPorEstudiante($id_estudiante);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'postulaciones' => $postulaciones,
            'total' => $total,
            'pagina' => $pagina,
            'total_paginas' => ceil($total / $limite)
        ]);
        exit();
    }
}

// Manejar acciones desde URL
if (isset($_GET['action'])) {
    $controller = new PostulacionController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}