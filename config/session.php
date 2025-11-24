<?php
/**
 * Portal de Trabajo UTP - Session Management
 * 
 * Gestión de sesiones seguras, tokens CSRF y control de acceso por rol.
 * Implementa medidas de seguridad para prevenir session hijacking y CSRF.
 * 
 * @author G2 - Mantinimiento de software | G3 - Ingenieria Web
 * @version 2.0
 */

// Configuración de sesión segura
if (session_status() === PHP_SESSION_NONE) {
    // Configurar parámetros de sesión antes de iniciar
    ini_set('session.cookie_httponly', 1);  // Cookie solo accesible por HTTP
    ini_set('session.use_only_cookies', 1);  // Forzar uso de cookies
    ini_set('session.cookie_secure', 0);     // Cambiar a 1 si tienes HTTPS
    ini_set('session.cookie_samesite', 'Lax'); // Protección CSRF adicional
    
    // Configurar tiempo de vida de sesión (2 horas)
    ini_set('session.gc_maxlifetime', 7200);
    
    session_start();
    
    // Regenerar ID de sesión periódicamente para prevenir session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerar cada 30 minutos
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Generar token CSRF para protección de formularios
 * 
 * @return string Token CSRF único para la sesión
 */
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validar token CSRF enviado en formulario
 * 
 * @param string $token Token a validar
 * @return bool True si el token es válido, false en caso contrario
 */
function validarTokenCSRF($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verificar que el usuario tenga sesión activa y el rol correcto
 * Redirige a index.php si no cumple los requisitos
 * 
 * @param string $rol_requerido Rol necesario: 'estudiante' o 'admin'
 */
function verificarSesion($rol_requerido) {
    // Verificar que exista sesión activa
    if (!isset($_SESSION['tipo_usuario']) || !isset($_SESSION['id_usuario'])) {
        $_SESSION['error'] = 'Debe iniciar sesión para acceder a esta página';
        header('Location: /Proyecto_Portal_De_Trabajo_UTP/index.php');
        exit();
    }
    
    // Verificar que el rol coincida
    if ($_SESSION['tipo_usuario'] !== $rol_requerido) {
        $_SESSION['error'] = 'No tiene permisos para acceder a esta página';
        header('Location: /Proyecto_Portal_De_Trabajo_UTP/index.php');
        exit();
    }
    
    // Verificar timeout de inactividad (30 minutos)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        cerrarSesion();
        $_SESSION['error'] = 'Su sesión ha expirado por inactividad';
        header('Location: /Proyecto_Portal_De_Trabajo_UTP/index.php');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

/**
 * Cerrar sesión del usuario actual
 * Destruye todas las variables de sesión y la cookie
 */
function cerrarSesion() {
    // Limpiar todas las variables de sesión
    $_SESSION = array();
    
    // Eliminar cookie de sesión
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destruir la sesión
    session_destroy();
}

/**
 * Verificar si hay un usuario autenticado (cualquier rol)
 * 
 * @return bool True si hay sesión activa, false en caso contrario
 */
function estaAutenticado() {
    return isset($_SESSION['tipo_usuario']) && isset($_SESSION['id_usuario']);
}

/**
 * Obtener información del usuario actual
 * 
 * @return array|null Información del usuario o null si no está autenticado
 */
function obtenerUsuarioActual() {
    if (!estaAutenticado()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['id_usuario'],
        'tipo' => $_SESSION['tipo_usuario'],
        'correo' => $_SESSION['correo'] ?? '',
        'nombres' => $_SESSION['nombres'] ?? '',
        'apellidos' => $_SESSION['apellidos'] ?? ''
    ];
}

/**
 * Establecer mensaje flash para mostrar en siguiente carga de página
 * 
 * @param string $tipo Tipo de mensaje: 'exito', 'error', 'info', 'warning'
 * @param string $mensaje Contenido del mensaje
 */
function setMensajeFlash($tipo, $mensaje) {
    $_SESSION['flash'][$tipo] = $mensaje;
}

/**
 * Obtener y limpiar mensajes flash
 * 
 * @return array Mensajes flash disponibles
 */
function obtenerMensajesFlash() {
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

/**
 * Sanitizar datos de entrada para prevenir XSS
 * 
 * @param string $data Dato a sanitizar
 * @return string Dato sanitizado
 */
function sanitizar($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validar correo institucional UTP
 * 
 * @param string $correo Correo a validar
 * @return bool True si es correo válido @utp.ac.pa
 */
function esCorreoUTP($correo) {
    $correo = strtolower(trim($correo));
    return filter_var($correo, FILTER_VALIDATE_EMAIL) && 
           str_ends_with($correo, '@utp.ac.pa');
}

/**
 * Redirigir según el tipo de usuario
 */
function redirigirSegunRol() {
    if (!estaAutenticado()) {
        header('Location: /Proyecto_Portal_De_Trabajo_UTP/index.php');
        exit();
    }
    
    if ($_SESSION['tipo_usuario'] === 'estudiante') {
        header('Location: /Proyecto_Portal_De_Trabajo_UTP/views/estudiante/dashboard.php');
    } else if ($_SESSION['tipo_usuario'] === 'admin') {
        header('Location: /Proyecto_Portal_De_Trabajo_UTP/views/admin/dashboard.php');
    }
    exit();
}