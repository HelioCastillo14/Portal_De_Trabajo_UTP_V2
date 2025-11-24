<?php
session_start();
require_once __DIR__ . '/../../config/session.php';
verificarSesion('admin');
$page_title = 'Exportar Datos';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
include __DIR__ . '/../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <h1 class="mb-4">Exportar Datos a CSV</h1>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i>Postulaciones</h5>
                    <p class="text-muted mb-4">Exporta las postulaciones por rango de fechas</p>
                    <form action="<?php echo BASE_URL; ?>/controllers/ExportController.php?action=exportarPostulaciones" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-download me-2"></i>Exportar Postulaciones</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="bi bi-person text-info me-2"></i>Estudiantes</h5>
                    <p class="text-muted mb-4">Exporta todos los estudiantes registrados</p>
                    <a href="<?php echo BASE_URL; ?>/controllers/ExportController.php?action=exportarEstudiantes" class="btn btn-info w-100"><i class="bi bi-download me-2"></i>Exportar Estudiantes</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="bi bi-briefcase text-warning me-2"></i>Ofertas</h5>
                    <p class="text-muted mb-4">Exporta todas las ofertas laborales</p>
                    <form action="<?php echo BASE_URL; ?>/controllers/ExportController.php?action=exportarOfertas" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Filtrar por Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todas</option>
                                <option value="activa">Activas</option>
                                <option value="cerrada">Cerradas</option>
                                <option value="tomada">Tomadas</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning w-100"><i class="bi bi-download me-2"></i>Exportar Ofertas</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="bi bi-building text-success me-2"></i>Empresas</h5>
                    <p class="text-muted mb-4">Exporta todas las empresas registradas</p>
                    <a href="<?php echo BASE_URL; ?>/controllers/ExportController.php?action=exportarEmpresas" class="btn btn-success w-100"><i class="bi bi-download me-2"></i>Exportar Empresas</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>