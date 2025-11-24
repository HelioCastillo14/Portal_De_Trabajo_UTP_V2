<?php
/**
 * Header Público - Portal de Trabajo UTP
 * Navbar para usuarios no autenticados
 */
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons 1.11 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/general.css">
    
    <title><?php echo $page_title ?? 'Portal de Trabajo UTP'; ?></title>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container">
        <!-- Logo y Título -->
        <a class="navbar-brand d-flex align-items-center gap-3" href="<?php echo BASE_URL; ?>/index.php">
            <img src="<?php echo BASE_URL; ?>/assets/images/utp-logo.png" 
                 alt="Logo UTP" 
                 class="navbar-logo"
                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/placeholder-logo.png'">
            <div class="navbar-title">
                Portal de Trabajo<br>
                Universidad Tecnológica de Panamá
            </div>
        </a>
        
        <!-- Toggle para móvil -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Menú -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/views/public/ofertas_publicas.php">Empleos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/views/public/contacto.php">Contacto</a>
                </li>
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-success" href="<?php echo BASE_URL; ?>/views/auth/login_estudiante.php">
                        Ingresar como Estudiante
                    </a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link text-muted" href="<?php echo BASE_URL; ?>/views/auth/login_admin.php">
                        <small>Admin</small>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php
// Mostrar mensajes flash si existen
if (isset($_SESSION['exito'])) {
    echo '<div class="alert alert-success alert-dismissible fade show mx-auto" style="max-width: 800px; margin-top: 20px;" role="alert">
            <i class="bi bi-check-circle me-2"></i>' . htmlspecialchars($_SESSION['exito']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['exito']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show mx-auto" style="max-width: 800px; margin-top: 20px;" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>' . htmlspecialchars($_SESSION['error']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error']);
}

if (isset($_SESSION['info'])) {
    echo '<div class="alert alert-info alert-dismissible fade show mx-auto" style="max-width: 800px; margin-top: 20px;" role="alert">
            <i class="bi bi-info-circle me-2"></i>' . htmlspecialchars($_SESSION['info']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['info']);
}
?>