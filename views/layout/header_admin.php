<?php
/**
 * Header Admin - Portal de Trabajo UTP
 * Navbar para administradores
 */
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: ' . BASE_URL . '/views/auth/login_admin.php');
    exit();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Notificacion.php';

$db = Database::getInstance()->getConnection();
$notificacionModel = new Notificacion($db);

// Obtener notificaciones no leídas
$notificaciones_no_leidas = $notificacionModel->contarNoLeidas('admin', $_SESSION['id_usuario']);
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
    
    <title><?php echo $page_title ?? 'Admin - Portal UTP'; ?></title>
</head>
<body>

<!-- NAVBAR ADMIN -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top border-bottom">
    <div class="container-fluid px-4">
        <!-- Logo y Título -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>/views/admin/dashboard.php">
            <img src="<?php echo BASE_URL; ?>/assets/images/utp-logo.png" 
                 alt="Logo UTP" 
                 class="navbar-logo"
                 onerror="this.style.display='none'">
            <div class="navbar-title" style="color: #2557F5;">
                Panel Administrativo<br>
                <small style="font-size: 11px; color: var(--gris-medio);">Portal de Trabajo UTP</small>
            </div>
        </a>
        
        <!-- Toggle para móvil -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Menú -->
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/views/admin/dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/views/admin/ofertas/index.php">
                        <i class="bi bi-briefcase me-1"></i>Ofertas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/views/admin/empresas/index.php">
                        <i class="bi bi-building me-1"></i>Empresas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/views/admin/postulaciones/gestionar.php">
                        <i class="bi bi-file-earmark-text me-1"></i>Postulaciones
                    </a>
                </li>
                
                <!-- Notificaciones -->
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <?php if ($notificaciones_no_leidas > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $notificaciones_no_leidas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                        <li class="dropdown-header">
                            Notificaciones
                            <?php if ($notificaciones_no_leidas > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo $notificaciones_no_leidas; ?></span>
                            <?php endif; ?>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-center small" href="<?php echo BASE_URL; ?>/views/admin/notificaciones.php">
                                Ver todas las notificaciones
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Perfil Admin -->
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 32px; height: 32px;">
                            <i class="bi bi-person text-primary"></i>
                        </div>
                        <span class="d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION['nombres']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <?php echo htmlspecialchars($_SESSION['nombres'] . ' ' . $_SESSION['apellidos']); ?>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($_SESSION['correo']); ?></small>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>/views/admin/exportar.php">
                                <i class="bi bi-download me-2"></i>Exportar Datos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>/views/admin/auditoria.php">
                                <i class="bi bi-clock-history me-2"></i>Auditoría
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/controllers/AuthController.php?action=logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php
// Mostrar mensajes flash
if (isset($_SESSION['exito'])) {
    echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-check-circle me-2"></i>' . htmlspecialchars($_SESSION['exito']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['exito']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>' . htmlspecialchars($_SESSION['error']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error']);
}

if (isset($_SESSION['info'])) {
    echo '<div class="alert alert-info alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-info-circle me-2"></i>' . htmlspecialchars($_SESSION['info']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['info']);
}
?>