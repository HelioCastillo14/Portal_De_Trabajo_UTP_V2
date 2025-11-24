<?php
session_start();
require_once __DIR__ . '/../../config/session.php';
verificarSesion('admin');
$page_title = 'Dashboard Admin';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AdminController.php';
$controller = new AdminController();
$datos = $controller->obtenerDatosDashboard();
include __DIR__ . '/../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <h1 class="mb-4">Dashboard Administrativo</h1>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 rounded p-3"><i class="bi bi-person text-primary fs-2"></i></div>
                        <div>
                            <h3 class="mb-0"><?php echo $datos['stats']['estudiantes']['total_estudiantes']; ?></h3>
                            <p class="text-muted mb-0 small">Estudiantes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 rounded p-3"><i class="bi bi-building text-success fs-2"></i></div>
                        <div>
                            <h3 class="mb-0"><?php echo $datos['stats']['empresas']['empresas_activas']; ?></h3>
                            <p class="text-muted mb-0 small">Empresas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning bg-opacity-10 rounded p-3"><i class="bi bi-briefcase text-warning fs-2"></i></div>
                        <div>
                            <h3 class="mb-0"><?php echo $datos['stats']['ofertas']['ofertas_activas']; ?></h3>
                            <p class="text-muted mb-0 small">Ofertas Activas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-info bg-opacity-10 rounded p-3"><i class="bi bi-file-earmark-text text-info fs-2"></i></div>
                        <div>
                            <h3 class="mb-0"><?php echo $datos['stats']['postulaciones']['total']; ?></h3>
                            <p class="text-muted mb-0 small">Postulaciones</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-4">Acciones Rápidas</h5>
                    <div class="row g-3">
                        <div class="col-md-3"><a href="<?php echo BASE_URL; ?>/views/admin/ofertas/crear.php" class="btn btn-success w-100"><i class="bi bi-plus-circle me-2"></i>Nueva Oferta</a></div>
                        <div class="col-md-3"><a href="<?php echo BASE_URL; ?>/views/admin/empresas/crear.php" class="btn btn-primary w-100"><i class="bi bi-building me-2"></i>Nueva Empresa</a></div>
                        <div class="col-md-3"><a href="<?php echo BASE_URL; ?>/views/admin/postulaciones/gestionar.php" class="btn btn-warning w-100"><i class="bi bi-file-earmark-text me-2"></i>Postulaciones</a></div>
                        <div class="col-md-3"><a href="<?php echo BASE_URL; ?>/views/admin/exportar.php" class="btn btn-outline-secondary w-100"><i class="bi bi-download me-2"></i>Exportar</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>