<?php
/**
 * Portal de Trabajo UTP - Oferta Controller
 * 
 * Gestión completa de ofertas: CRUD, búsqueda, filtros, asociación de habilidades.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Oferta.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Habilidad.php';
require_once __DIR__ . '/../models/Auditoria.php';

class OfertaController {
    private $db;
    private $ofertaModel;
    private $empresaModel;
    private $habilidadModel;
    private $auditoriaModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ofertaModel = new Oferta($this->db);
        $this->empresaModel = new Empresa($this->db);
        $this->habilidadModel = new Habilidad($this->db);
        $this->auditoriaModel = new Auditoria($this->db);
    }
    
    /**
     * Crear nueva oferta (admin)
     */
    public function crear() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/ofertas/crear.php');
                exit();
            }
            
            // Sanitizar datos
            $datos = [
                'id_empresa' => (int)$_POST['id_empresa'],
                'titulo' => sanitizar($_POST['titulo']),
                'descripcion' => sanitizar($_POST['descripcion']),
                'requisitos' => sanitizar($_POST['requisitos'] ?? ''),
                'responsabilidades' => sanitizar($_POST['responsabilidades'] ?? ''),
                'beneficios' => sanitizar($_POST['beneficios'] ?? ''),
                'salario_min' => !empty($_POST['salario_min']) ? (float)$_POST['salario_min'] : null,
                'salario_max' => !empty($_POST['salario_max']) ? (float)$_POST['salario_max'] : null,
                'modalidad' => $_POST['modalidad'],
                'tipo_empleo' => $_POST['tipo_empleo'],
                'nivel_experiencia' => $_POST['nivel_experiencia'],
                'ubicacion' => sanitizar($_POST['ubicacion'] ?? ''),
                'fecha_limite' => $_POST['fecha_limite']
            ];
            
            // Validar campos requeridos
            if (empty($datos['titulo']) || empty($datos['descripcion']) || empty($datos['fecha_limite'])) {
                $_SESSION['error'] = 'Complete todos los campos obligatorios';
                header('Location: ' . BASE_URL . '/views/admin/ofertas/crear.php');
                exit();
            }
            
            // Validar fecha límite (debe ser futura)
            if ($datos['fecha_limite'] < date('Y-m-d')) {
                $_SESSION['error'] = 'La fecha límite debe ser futura';
                header('Location: ' . BASE_URL . '/views/admin/ofertas/crear.php');
                exit();
            }
            
            // Crear oferta
            $id_oferta = $this->ofertaModel->crear($datos);
            
            if ($id_oferta) {
                // Asociar habilidades si hay
                if (!empty($_POST['habilidades']) && is_array($_POST['habilidades'])) {
                    $this->ofertaModel->asociarHabilidades($id_oferta, $_POST['habilidades']);
                }
                
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'crear_oferta',
                    'tabla_afectada' => 'ofertas',
                    'id_registro_afectado' => $id_oferta,
                    'datos_nuevos' => json_encode($datos),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Oferta creada exitosamente';
                header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php');
                exit();
            } else {
                $_SESSION['error'] = 'Error al crear la oferta';
                header('Location: ' . BASE_URL . '/views/admin/ofertas/crear.php');
                exit();
            }
        }
    }
    
    /**
     * Actualizar oferta (admin)
     */
    public function actualizar() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php');
                exit();
            }
            
            $id_oferta = (int)$_POST['id_oferta'];
            
            // Obtener datos anteriores para auditoría
            $oferta_anterior = $this->ofertaModel->findById($id_oferta);
            
            // Sanitizar datos
            $datos = [
                'id_empresa' => (int)$_POST['id_empresa'],
                'titulo' => sanitizar($_POST['titulo']),
                'descripcion' => sanitizar($_POST['descripcion']),
                'requisitos' => sanitizar($_POST['requisitos'] ?? ''),
                'responsabilidades' => sanitizar($_POST['responsabilidades'] ?? ''),
                'beneficios' => sanitizar($_POST['beneficios'] ?? ''),
                'salario_min' => !empty($_POST['salario_min']) ? (float)$_POST['salario_min'] : null,
                'salario_max' => !empty($_POST['salario_max']) ? (float)$_POST['salario_max'] : null,
                'modalidad' => $_POST['modalidad'],
                'tipo_empleo' => $_POST['tipo_empleo'],
                'nivel_experiencia' => $_POST['nivel_experiencia'],
                'ubicacion' => sanitizar($_POST['ubicacion'] ?? ''),
                'fecha_limite' => $_POST['fecha_limite'],
                'estado' => $_POST['estado'] ?? 'activa'
            ];
            
            // Actualizar oferta
            if ($this->ofertaModel->update($id_oferta, $datos)) {
                // Actualizar habilidades
                if (isset($_POST['habilidades']) && is_array($_POST['habilidades'])) {
                    $this->ofertaModel->asociarHabilidades($id_oferta, $_POST['habilidades']);
                }
                
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'actualizar_oferta',
                    'tabla_afectada' => 'ofertas',
                    'id_registro_afectado' => $id_oferta,
                    'datos_anteriores' => json_encode($oferta_anterior),
                    'datos_nuevos' => json_encode($datos),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Oferta actualizada correctamente';
            } else {
                $_SESSION['error'] = 'Error al actualizar la oferta';
            }
            
            header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php');
            exit();
        }
    }
    
    /**
     * Eliminar oferta (admin)
     */
    public function eliminar() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php');
                exit();
            }
            
            $id_oferta = (int)$_POST['id_oferta'];
            
            // Obtener datos para auditoría
            $oferta = $this->ofertaModel->findById($id_oferta);
            
            // Eliminar oferta
            if ($this->ofertaModel->delete($id_oferta)) {
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'eliminar_oferta',
                    'tabla_afectada' => 'ofertas',
                    'id_registro_afectado' => $id_oferta,
                    'datos_anteriores' => json_encode($oferta),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Oferta eliminada correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar la oferta';
            }
            
            header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php');
            exit();
        }
    }
    
    /**
     * Buscar ofertas con filtros (API JSON para AJAX)
     */
    public function buscar() {
        // Esta función puede ser usada sin autenticación para búsqueda pública
        
        $filtros = [
            'busqueda' => $_GET['q'] ?? '',
            'modalidad' => $_GET['modalidad'] ?? '',
            'tipo_empleo' => $_GET['tipo_empleo'] ?? '',
            'nivel' => $_GET['nivel'] ?? '',
            'empresa' => $_GET['empresa'] ?? ''
        ];
        
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 10;
        
        $ofertas = $this->ofertaModel->getOfertasActivas($filtros, $pagina, $limite);
        $total_paginas = $this->ofertaModel->getTotalPaginas($limite, $filtros);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'ofertas' => $ofertas,
            'pagina' => $pagina,
            'total_paginas' => $total_paginas
        ]);
        exit();
    }
    
    /**
     * Obtener detalle de oferta (API JSON)
     */
    public function detalleJSON() {
        $id_oferta = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id_oferta > 0) {
            $oferta = $this->ofertaModel->findById($id_oferta);
            $habilidades = $this->ofertaModel->getHabilidades($id_oferta);
            
            if ($oferta) {
                $oferta['habilidades'] = $habilidades;
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'oferta' => $oferta
                ]);
                exit();
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Oferta no encontrada'
        ]);
        exit();
    }
    
    /**
     * Cambiar estado de oferta (admin)
     */
    public function cambiarEstado() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php');
                exit();
            }
            
            $id_oferta = (int)$_POST['id_oferta'];
            $nuevo_estado = $_POST['estado'];
            
            $oferta_anterior = $this->ofertaModel->findById($id_oferta);
            
            if ($this->ofertaModel->cambiarEstado($id_oferta, $nuevo_estado)) {
                // Auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'cambiar_estado_oferta',
                    'tabla_afectada' => 'ofertas',
                    'id_registro_afectado' => $id_oferta,
                    'datos_anteriores' => json_encode(['estado' => $oferta_anterior['estado']]),
                    'datos_nuevos' => json_encode(['estado' => $nuevo_estado]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = 'Estado actualizado correctamente';
            } else {
                $_SESSION['error'] = 'Error al cambiar el estado';
            }
            
            header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php');
            exit();
        }
    }
    
    /**
     * Listar ofertas para admin (con filtros)
     */
    public function listarAdmin() {
        verificarSesion('admin');
        
        $filtros = [
            'busqueda' => $_GET['q'] ?? '',
            'empresa' => $_GET['empresa'] ?? '',
            'estado' => $_GET['estado'] ?? ''
        ];
        
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        
        // Aquí se llamaría al modelo para obtener las ofertas
        // Esta función es para usar en las vistas admin
        
        return [
            'filtros' => $filtros,
            'pagina' => $pagina
        ];
    }
}

// Manejar acciones desde URL
if (isset($_GET['action'])) {
    $controller = new OfertaController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}