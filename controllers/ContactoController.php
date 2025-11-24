<?php
/**
 * Portal de Trabajo UTP - Contacto Controller
 * 
 * Gestión del formulario de contacto público.
 * Guarda mensajes en archivo de texto (logs/contacto.txt).
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

class ContactoController {
    
    /**
     * Guardar mensaje de contacto
     */
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/public/contacto.php');
                exit();
            }
            
            // Sanitizar datos
            $nombre = sanitizar($_POST['nombre'] ?? '');
            $correo = filter_var($_POST['correo'] ?? '', FILTER_SANITIZE_EMAIL);
            $asunto = sanitizar($_POST['asunto'] ?? '');
            $mensaje = sanitizar($_POST['mensaje'] ?? '');
            $captcha = (int)($_POST['captcha'] ?? 0);
            
            // Validar campos requeridos
            if (empty($nombre) || empty($correo) || empty($asunto) || empty($mensaje)) {
                $_SESSION['error'] = 'Todos los campos son obligatorios';
                header('Location: ' . BASE_URL . '/views/public/contacto.php');
                exit();
            }
            
            // Validar email
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Correo electrónico inválido';
                header('Location: ' . BASE_URL . '/views/public/contacto.php');
                exit();
            }
            
            // Validar captcha simple (5 + 3 = 8)
            if ($captcha !== 8) {
                $_SESSION['error'] = 'Captcha incorrecto. Por favor intenta nuevamente';
                header('Location: ' . BASE_URL . '/views/public/contacto.php');
                exit();
            }
            
            // Validar longitud del mensaje
            if (strlen($mensaje) < 10) {
                $_SESSION['error'] = 'El mensaje debe tener al menos 10 caracteres';
                header('Location: ' . BASE_URL . '/views/public/contacto.php');
                exit();
            }
            
            if (strlen($mensaje) > 1000) {
                $_SESSION['error'] = 'El mensaje no puede superar los 1000 caracteres';
                header('Location: ' . BASE_URL . '/views/public/contacto.php');
                exit();
            }
            
            // Construir contenido del mensaje
            $contenido = $this->formatearMensaje($nombre, $correo, $asunto, $mensaje);
            
            // Guardar en archivo
            $archivo = LOGS_PATH . 'contacto.txt';
            
            try {
                // Crear directorio logs si no existe
                if (!is_dir(LOGS_PATH)) {
                    mkdir(LOGS_PATH, 0755, true);
                }
                
                // Agregar mensaje al archivo
                if (file_put_contents($archivo, $contenido, FILE_APPEND | LOCK_EX)) {
                    $_SESSION['exito'] = '¡Mensaje enviado correctamente! Nos pondremos en contacto pronto.';
                    
                    // Limpiar campos del formulario
                    unset($_SESSION['contacto_data']);
                } else {
                    $_SESSION['error'] = 'Error al guardar el mensaje. Por favor intenta más tarde.';
                }
            } catch (Exception $e) {
                error_log("Error al guardar mensaje de contacto: " . $e->getMessage());
                $_SESSION['error'] = 'Error al procesar tu mensaje. Por favor intenta más tarde.';
            }
            
            header('Location: ' . BASE_URL . '/views/public/contacto.php');
            exit();
        }
    }
    
    /**
     * Formatear mensaje para guardar en archivo
     * 
     * @param string $nombre Nombre del remitente
     * @param string $correo Correo del remitente
     * @param string $asunto Asunto del mensaje
     * @param string $mensaje Contenido del mensaje
     * @return string Mensaje formateado
     */
    private function formatearMensaje($nombre, $correo, $asunto, $mensaje) {
        $fecha = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
        
        $separador = str_repeat('=', 80);
        
        $contenido = "\n$separador\n";
        $contenido .= "NUEVO MENSAJE DE CONTACTO\n";
        $contenido .= "$separador\n";
        $contenido .= "Fecha: $fecha\n";
        $contenido .= "Nombre: $nombre\n";
        $contenido .= "Correo: $correo\n";
        $contenido .= "Asunto: $asunto\n";
        $contenido .= "IP: $ip\n";
        $contenido .= "Navegador: $user_agent\n";
        $contenido .= "$separador\n";
        $contenido .= "Mensaje:\n";
        $contenido .= wordwrap($mensaje, 75) . "\n";
        $contenido .= "$separador\n\n";
        
        return $contenido;
    }
    
    /**
     * Obtener mensajes de contacto (solo admin)
     */
    public function listarMensajes() {
        verificarSesion('admin');
        
        $archivo = LOGS_PATH . 'contacto.txt';
        
        if (file_exists($archivo)) {
            $contenido = file_get_contents($archivo);
            
            // Dividir por separadores para obtener mensajes individuales
            $mensajes = explode(str_repeat('=', 80), $contenido);
            
            // Filtrar mensajes vacíos
            $mensajes = array_filter($mensajes, function($msg) {
                return trim($msg) !== '';
            });
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'total' => count($mensajes),
                'mensajes' => array_values($mensajes)
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'total' => 0,
                'mensajes' => []
            ]);
        }
        exit();
    }
    
    /**
     * Limpiar archivo de mensajes antiguos (solo admin)
     */
    public function limpiarMensajes() {
        verificarSesion('admin');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar CSRF
            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/views/admin/dashboard.php');
                exit();
            }
            
            $archivo = LOGS_PATH . 'contacto.txt';
            
            // Crear backup antes de limpiar
            if (file_exists($archivo)) {
                $backup = LOGS_PATH . 'contacto_backup_' . date('Y-m-d_His') . '.txt';
                copy($archivo, $backup);
                
                // Limpiar archivo
                file_put_contents($archivo, '');
                
                $_SESSION['exito'] = 'Archivo de mensajes limpiado. Backup creado: ' . basename($backup);
            } else {
                $_SESSION['info'] = 'No hay mensajes para limpiar';
            }
            
            header('Location: ' . BASE_URL . '/views/admin/dashboard.php');
            exit();
        }
    }
    
    /**
     * Descargar archivo de mensajes (solo admin)
     */
    public function descargarMensajes() {
        verificarSesion('admin');
        
        $archivo = LOGS_PATH . 'contacto.txt';
        
        if (file_exists($archivo)) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="mensajes_contacto_' . date('Y-m-d') . '.txt"');
            header('Content-Length: ' . filesize($archivo));
            
            readfile($archivo);
            exit();
        } else {
            $_SESSION['error'] = 'No hay mensajes para descargar';
            header('Location: ' . BASE_URL . '/views/admin/dashboard.php');
            exit();
        }
    }
}

// Manejar acciones desde URL
if (isset($_GET['action'])) {
    $controller = new ContactoController();
    $action = $_GET['action'];
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}