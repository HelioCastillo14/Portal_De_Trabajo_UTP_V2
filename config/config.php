<?php
/**
 * Portal de Trabajo UTP - Configuration File
 * 
 * Configuraciones generales del sistema: rutas, constantes, catálogos y funciones auxiliares.
 * 
 * @author G2 - Mantinimiento de software | G3 - Ingenieria Web
 * @version 2.0
 */

// ============================================
// CONFIGURACIÓN GENERAL
// ============================================

define('BASE_URL', 'http://localhost/Proyecto_Portal_De_Trabajo_UTP');
define('SITE_NAME', 'Portal de Trabajo UTP');
define('SITE_VERSION', '1.0');

// ============================================
// CONFIGURACIÓN DE RUTAS
// ============================================

define('ROOT_PATH', __DIR__ . '/..');
define('UPLOAD_CV_PATH', ROOT_PATH . '/assets/uploads/cvs/');
define('UPLOAD_PROFILE_PATH', ROOT_PATH . '/assets/uploads/perfiles/');
define('UPLOAD_LOGOS_PATH', ROOT_PATH . '/assets/images/logos/');
define('LOGS_PATH', ROOT_PATH . '/logs/');

// ============================================
// CONFIGURACIÓN DE ARCHIVOS
// ============================================

// Tamaños máximos de archivo (en bytes)
define('MAX_CV_SIZE', 5 * 1024 * 1024);      // 5MB
define('MAX_PROFILE_SIZE', 2 * 1024 * 1024); // 2MB
define('MAX_LOGO_SIZE', 1 * 1024 * 1024);    // 1MB

// Extensiones permitidas
define('ALLOWED_CV_EXTENSIONS', ['pdf']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// ============================================
// CONFIGURACIÓN DE PAGINACIÓN
// ============================================

define('ITEMS_PER_PAGE', 10);
define('MAX_PAGINATION_LINKS', 5);

// ============================================
// CONFIGURACIÓN DE TIEMPO
// ============================================

define('SESSION_TIMEOUT', 1800);        // 30 minutos
define('CV_RETENTION_MONTHS', 12);      // 12 meses de retención
define('NOTIFICATION_LIMIT', 20);       // Notificaciones a mostrar

// ============================================
// CATÁLOGO DE CARRERAS UTP
// ============================================

$GLOBALS['carreras_utp'] = [
    'Ingeniería en Sistemas Computacionales',
    'Licenciatura en Desarrollo de Software',
    'Ingeniería en Electrónica y Telecomunicaciones',
    'Licenciatura en Administración de Empresas',
    'Ingeniería Industrial',
    'Licenciatura en Mercadeo y Comercio Internacional',
    'Ingeniería Civil',
    'Licenciatura en Contabilidad',
    'Ingeniería Eléctrica',
    'Licenciatura en Finanzas y Banca',
    'Ingeniería Mecánica',
    'Licenciatura en Logística y Transporte Multimodal',
    'Ingeniería Ambiental',
    'Licenciatura en Diseño Gráfico',
    'Otra'
];

/**
 * Obtener lista de carreras UTP
 * 
 * @return array Lista de carreras
 */
function getCarreras() {
    return $GLOBALS['carreras_utp'];
}

// ============================================
// CATÁLOGO DE MODALIDADES Y TIPOS
// ============================================

$GLOBALS['modalidades'] = [
    'remoto' => 'Remoto',
    'presencial' => 'Presencial',
    'hibrido' => 'Híbrido'
];

$GLOBALS['tipos_empleo'] = [
    'tiempo_completo' => 'Tiempo Completo',
    'medio_tiempo' => 'Medio Tiempo',
    'practicas' => 'Prácticas',
    'temporal' => 'Temporal'
];

$GLOBALS['niveles_experiencia'] = [
    'junior' => 'Junior',
    'mid' => 'Mid-level',
    'senior' => 'Senior'
];

$GLOBALS['estados_oferta'] = [
    'activa' => 'Activa',
    'cerrada' => 'Cerrada',
    'tomada' => 'Tomada'
];

$GLOBALS['estados_postulacion'] = [
    'en_revision' => 'En Revisión',
    'aceptada' => 'Aceptada',
    'rechazada' => 'Rechazada'
];

/**
 * Obtener nombre legible de modalidad
 * 
 * @param string $codigo Código de modalidad
 * @return string Nombre legible
 */
function getModalidadNombre($codigo) {
    return $GLOBALS['modalidades'][$codigo] ?? $codigo;
}

/**
 * Obtener nombre legible de tipo de empleo
 * 
 * @param string $codigo Código de tipo empleo
 * @return string Nombre legible
 */
function getTipoEmpleoNombre($codigo) {
    return $GLOBALS['tipos_empleo'][$codigo] ?? $codigo;
}

/**
 * Obtener nombre legible de nivel de experiencia
 * 
 * @param string $codigo Código de nivel
 * @return string Nombre legible
 */
function getNivelExperienciaNombre($codigo) {
    return $GLOBALS['niveles_experiencia'][$codigo] ?? $codigo;
}

// ============================================
// FUNCIONES DE FORMATO
// ============================================

/**
 * Formatear fecha en español
 * 
 * @param string $fecha Fecha en formato SQL
 * @return string Fecha formateada (ej: "23 de noviembre de 2024")
 */
function formatearFecha($fecha) {
    if (!$fecha) return '';
    
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    
    $timestamp = strtotime($fecha);
    $dia = date('d', $timestamp);
    $mes = $meses[(int)date('m', $timestamp)];
    $anio = date('Y', $timestamp);
    
    return "$dia de $mes de $anio";
}

/**
 * Calcular tiempo transcurrido desde una fecha
 * 
 * @param string $fecha Fecha en formato SQL
 * @return string Tiempo transcurrido (ej: "hace 2 días")
 */
function tiempoTranscurrido($fecha) {
    if (!$fecha) return '';
    
    $ahora = new DateTime();
    $fecha_dt = new DateTime($fecha);
    $diff = $ahora->diff($fecha_dt);
    
    if ($diff->y > 0) return 'hace ' . $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
    if ($diff->m > 0) return 'hace ' . $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
    if ($diff->d > 0) return 'hace ' . $diff->d . ' día' . ($diff->d > 1 ? 's' : '');
    if ($diff->h > 0) return 'hace ' . $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
    if ($diff->i > 0) return 'hace ' . $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '');
    return 'Justo ahora';
}

/**
 * Formatear salario
 * 
 * @param float $salario_min Salario mínimo
 * @param float $salario_max Salario máximo
 * @return string Salario formateado (ej: "$1,500 - $2,500")
 */
function formatearSalario($salario_min, $salario_max) {
    if (!$salario_min && !$salario_max) {
        return 'A convenir';
    }
    
    if ($salario_min && $salario_max) {
        return '$' . number_format($salario_min, 0) . ' - $' . number_format($salario_max, 0);
    }
    
    if ($salario_min) {
        return 'Desde $' . number_format($salario_min, 0);
    }
    
    return 'Hasta $' . number_format($salario_max, 0);
}

/**
 * Acortar texto con puntos suspensivos
 * 
 * @param string $texto Texto a acortar
 * @param int $longitud Longitud máxima
 * @return string Texto acortado
 */
function acortarTexto($texto, $longitud = 100) {
    if (strlen($texto) <= $longitud) {
        return $texto;
    }
    return substr($texto, 0, $longitud) . '...';
}

// ============================================
// FUNCIONES DE ARCHIVO
// ============================================

/**
 * Sanitizar nombre de archivo
 * 
 * @param string $nombre Nombre original del archivo
 * @return string Nombre sanitizado
 */
function sanitizarNombreArchivo($nombre) {
    // Remover caracteres especiales, mantener solo alfanuméricos, guiones y puntos
    $nombre = preg_replace('/[^a-zA-Z0-9._-]/', '', $nombre);
    return $nombre;
}

/**
 * Generar nombre único para archivo
 * 
 * @param string $nombre_original Nombre original del archivo
 * @param int $id_usuario ID del usuario
 * @return string Nombre único generado
 */
function generarNombreArchivoUnico($nombre_original, $id_usuario) {
    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
    return $id_usuario . '_' . time() . '.' . strtolower($extension);
}

/**
 * Validar extensión de archivo
 * 
 * @param string $nombre_archivo Nombre del archivo
 * @param array $extensiones_permitidas Array de extensiones permitidas
 * @return bool True si la extensión es válida
 */
function validarExtension($nombre_archivo, $extensiones_permitidas) {
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
    return in_array($extension, $extensiones_permitidas);
}

/**
 * Obtener tamaño de archivo legible
 * 
 * @param int $bytes Tamaño en bytes
 * @return string Tamaño formateado (ej: "2.5 MB")
 */
function formatearTamanoArchivo($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// ============================================
// FUNCIONES DE LOG
// ============================================

/**
 * Escribir log de error
 * 
 * @param string $mensaje Mensaje de error
 * @param string $archivo Archivo donde ocurrió el error (opcional)
 */
function logError($mensaje, $archivo = '') {
    $fecha = date('Y-m-d H:i:s');
    $log = "[$fecha] ";
    if ($archivo) {
        $log .= "[$archivo] ";
    }
    $log .= $mensaje . PHP_EOL;
    
    file_put_contents(LOGS_PATH . 'error.log', $log, FILE_APPEND);
}

/**
 * Escribir log de actividad
 * 
 * @param string $actividad Descripción de la actividad
 * @param int $id_usuario ID del usuario
 * @param string $tipo_usuario Tipo de usuario (estudiante/admin)
 */
function logActividad($actividad, $id_usuario, $tipo_usuario) {
    $fecha = date('Y-m-d H:i:s');
    $log = "[$fecha] $tipo_usuario #$id_usuario: $actividad" . PHP_EOL;
    
    file_put_contents(LOGS_PATH . 'actividad.log', $log, FILE_APPEND);
}

// ============================================
// CONFIGURACIÓN DE ZONA HORARIA
// ============================================

date_default_timezone_set('America/Panama');

// ============================================
// CONFIGURACIÓN DE ERRORES (cambiar en producción)
// ============================================

if ($_SERVER['SERVER_NAME'] === 'localhost') {
    // Desarrollo: Mostrar errores
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Producción: No mostrar errores, solo loguear
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . 'php_errors.log');
}