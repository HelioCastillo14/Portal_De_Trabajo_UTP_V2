<?php
/**
 * Portal de Trabajo UTP - Modelo Administrador
 * 
 * Gestión de usuarios administrativos del sistema.
 * Incluye autenticación y CRUD básico.
 * 
 * @author Sistema Portal UTP
 * @version 1.0
 */

class Administrador {
    private $db;
    
    /**
     * Constructor
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Buscar administrador por ID
     * 
     * @param int $id ID del administrador
     * @return array|null Datos del administrador o null si no existe
     */
    public function findById($id) {
        $query = "SELECT * FROM administradores WHERE id_admin = :id AND activo = TRUE";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar administrador por correo electrónico
     * 
     * @param string $correo Correo electrónico
     * @return array|null Datos del administrador o null si no existe
     */
    public function findByEmail($correo) {
        $query = "SELECT * FROM administradores WHERE correo_utp = :correo AND activo = TRUE";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nuevo administrador
     * 
     * @param array $datos Datos del administrador
     * @return int ID del administrador creado
     */
    public function create($datos) {
        $query = "INSERT INTO administradores (correo_utp, nombres, apellidos, password_hash) 
                  VALUES (:correo, :nombres, :apellidos, :password_hash)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':correo', $datos['correo_utp']);
        $stmt->bindParam(':nombres', $datos['nombres']);
        $stmt->bindParam(':apellidos', $datos['apellidos']);
        $stmt->bindParam(':password_hash', $datos['password_hash']);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar datos del administrador
     * 
     * @param int $id ID del administrador
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $datos) {
        $campos = [];
        $valores = [];
        
        // Construir dinámicamente los campos a actualizar
        foreach ($datos as $campo => $valor) {
            if ($campo !== 'id_admin') {
                $campos[] = "$campo = :$campo";
                $valores[":$campo"] = $valor;
            }
        }
        
        $valores[':id'] = $id;
        
        $query = "UPDATE administradores SET " . implode(', ', $campos) . " WHERE id_admin = :id";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($valores);
    }
    
    /**
     * Validar credenciales de administrador (para login)
     * 
     * @param string $correo Correo electrónico
     * @param string $password Contraseña en texto plano
     * @return array|false Datos del admin si las credenciales son válidas, false en caso contrario
     */
    public function validarCredenciales($correo, $password) {
        $admin = $this->findByEmail($correo);
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // No retornar el hash de la contraseña
            unset($admin['password_hash']);
            return $admin;
        }
        
        return false;
    }
    
    /**
     * Cambiar contraseña de administrador
     * 
     * @param int $id ID del administrador
     * @param string $nueva_password Nueva contraseña en texto plano
     * @return bool True si se cambió correctamente
     */
    public function cambiarPassword($id, $nueva_password) {
        $password_hash = password_hash($nueva_password, PASSWORD_BCRYPT);
        
        $query = "UPDATE administradores SET password_hash = :password_hash WHERE id_admin = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Listar todos los administradores activos
     * 
     * @return array Lista de administradores
     */
    public function listarTodos() {
        $query = "SELECT id_admin, correo_utp, nombres, apellidos, fecha_creacion, activo 
                  FROM administradores 
                  WHERE activo = TRUE 
                  ORDER BY nombres ASC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Desactivar administrador (soft delete)
     * 
     * @param int $id ID del administrador
     * @return bool True si se desactivó correctamente
     */
    public function desactivar($id) {
        $query = "UPDATE administradores SET activo = FALSE WHERE id_admin = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si existe un administrador con el correo dado
     * 
     * @param string $correo Correo a verificar
     * @return bool True si existe
     */
    public function existeCorreo($correo) {
        $query = "SELECT COUNT(*) as total FROM administradores WHERE correo_utp = :correo";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }
}