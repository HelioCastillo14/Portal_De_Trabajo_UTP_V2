<?php
/**
 * Portal de Trabajo UTP - Database Connection
 * 
 * Clase singleton para gestionar la conexión a MySQL usando PDO.
 * Implementa el patrón Singleton para asegurar una única instancia de conexión.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Credenciales de conexión (ajustar según tu configuración)
    private $host = 'localhost';
    private $db_name = 'portal_trabajo_utp';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    /**
     * Constructor privado para prevenir instanciación directa
     * Establece la conexión PDO con manejo de errores
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->charset
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            // Loguear error en producción, mostrar en desarrollo
            error_log("Error de conexión a BD: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
        }
    }
    
    /**
     * Prevenir clonación del objeto
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton.");
    }
    
    /**
     * Obtener la instancia única de la clase
     * 
     * @return Database Instancia única de Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener la conexión PDO
     * 
     * @return PDO Objeto de conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Cerrar conexión (opcional, PDO lo hace automáticamente)
     */
    public function closeConnection() {
        $this->connection = null;
    }
}