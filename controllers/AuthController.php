<?php
/**
 * Portal de Trabajo UTP - Auth Controller
 * 
 * Gestión de autenticación: login y logout para estudiantes y administradores.
 * Incluye validación de dominio @utp.ac.pa para estudiantes.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Estudiante.php';
require_once __DIR__ . '/../models/Administrador.php';
require_once __DIR__ . '/../models/Auditoria.php';

class AuthController {
    private $db;
    private $estudianteModel;
    private $administradorModel;
    private $auditoriaModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->estudianteModel = new Estudiante($this->db);
        $this->administradorModel = new Administrador($this->db);
        $this->auditoriaModel = new Auditoria($this->db);
    }
    
    /**
     * Login de estudiantes (sin contraseña, solo validación de dominio @utp.ac.pa)
     */
    public function loginEstudiante() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar token CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/auth/login_estudiante.php');
                exit();
            }
            
            // Sanitizar y validar correo
            $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
            $correo = strtolower(trim($correo));
            
            // Validar que sea correo UTP
            if (!esCorreoUTP($correo)) {
                $_SESSION['error'] = 'Solo se permiten correos institucionales @utp.ac.pa';
                header('Location: ' . BASE_URL . '/views/auth/login_estudiante.php');
                exit();
            }
            
            // Buscar estudiante
            $estudiante = $this->estudianteModel->findByEmail($correo);
            
            // Si no existe, crear perfil automáticamente (primer login)
            if (!$estudiante) {
                $nombres = sanitizar($_POST['nombres'] ?? '');
                $apellidos = sanitizar($_POST['apellidos'] ?? '');
                
                if (empty($nombres) || empty($apellidos)) {
                    $_SESSION['error'] = 'Debe proporcionar nombres y apellidos';
                    header('Location: ' . BASE_URL . '/views/auth/login_estudiante.php');
                    exit();
                }
                
                $id_estudiante = $this->estudianteModel->create([
                    'correo_utp' => $correo,
                    'nombres' => $nombres,
                    'apellidos' => $apellidos
                ]);
                
                $estudiante = $this->estudianteModel->findById($id_estudiante);
                
                // Registrar en auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'estudiante',
                    'id_usuario' => $id_estudiante,
                    'accion' => 'registro_primer_login',
                    'tabla_afectada' => 'estudiantes',
                    'id_registro_afectado' => $id_estudiante,
                    'datos_nuevos' => json_encode(['correo' => $correo]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
            }
            
            // Crear sesión
            $_SESSION['tipo_usuario'] = 'estudiante';
            $_SESSION['id_usuario'] = $estudiante['id_estudiante'];
            $_SESSION['correo'] = $estudiante['correo_utp'];
            $_SESSION['nombres'] = $estudiante['nombres'];
            $_SESSION['apellidos'] = $estudiante['apellidos'];
            $_SESSION['last_activity'] = time();
            
            // Registrar login en auditoría
            $this->auditoriaModel->registrar([
                'tipo_usuario' => 'estudiante',
                'id_usuario' => $estudiante['id_estudiante'],
                'accion' => 'login',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            $_SESSION['exito'] = '¡Bienvenido ' . $estudiante['nombres'] . '!';
            header('Location: ' . BASE_URL . '/views/estudiante/dashboard.php');
            exit();
        }
    }
    
    /**
     * Login de administradores (con email y contraseña)
     */
    public function loginAdmin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar token CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/auth/login_admin.php');
                exit();
            }
            
            // Sanitizar datos
            $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
            $correo = strtolower(trim($correo));
            $password = $_POST['password'] ?? '';
            
            // Validar campos
            if (empty($correo) || empty($password)) {
                $_SESSION['error'] = 'Debe proporcionar correo y contraseña';
                header('Location: ' . BASE_URL . '/views/auth/login_admin.php');
                exit();
            }
            
            // Validar credenciales
            $admin = $this->administradorModel->validarCredenciales($correo, $password);
            
            if ($admin) {
                // Crear sesión
                $_SESSION['tipo_usuario'] = 'admin';
                $_SESSION['id_usuario'] = $admin['id_admin'];
                $_SESSION['correo'] = $admin['correo_utp'];
                $_SESSION['nombres'] = $admin['nombres'];
                $_SESSION['apellidos'] = $admin['apellidos'];
                $_SESSION['last_activity'] = time();
                
                // Registrar login en auditoría
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => $admin['id_admin'],
                    'accion' => 'login_admin',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $_SESSION['exito'] = '¡Bienvenido ' . $admin['nombres'] . '!';
                header('Location: ' . BASE_URL . '/views/admin/dashboard.php');
                exit();
            } else {
                // Credenciales inválidas
                $_SESSION['error'] = 'Correo o contraseña incorrectos';
                
                // Registrar intento fallido (opcional)
                $this->auditoriaModel->registrar([
                    'tipo_usuario' => 'admin',
                    'id_usuario' => 0,
                    'accion' => 'login_fallido',
                    'datos_nuevos' => json_encode(['correo' => $correo]),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                header('Location: ' . BASE_URL . '/views/auth/login_admin.php');
                exit();
            }
        }
    }
    
    /**
     * Logout general (estudiantes y admins)
     */
    public function logout() {
        if (estaAutenticado()) {
            // Registrar logout en auditoría
            $this->auditoriaModel->registrar([
                'tipo_usuario' => $_SESSION['tipo_usuario'],
                'id_usuario' => $_SESSION['id_usuario'],
                'accion' => 'logout',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        }
        
        // Cerrar sesión
        cerrarSesion();
        
        // Redirigir a homepage
        $_SESSION['exito'] = 'Sesión cerrada exitosamente';
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
    
    /**
     * Verificar si hay sesión activa y redirigir según rol
     */
    public function verificarYRedirigir() {
        if (estaAutenticado()) {
            redirigirSegunRol();
        } else {
            header('Location: ' . BASE_URL . '/index.php');
            exit();
        }
    }
}

// Manejar acciones desde URL
if (isset($_GET['action'])) {
    $controller = new AuthController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}