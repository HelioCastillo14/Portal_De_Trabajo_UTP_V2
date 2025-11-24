<?php
session_start();
require_once __DIR__ . '/../../../config/session.php';
verificarSesion('admin');
$page_title = 'Gestionar Empresas';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../models/Empresa.php';
$db = Database::getInstance()->getConnection();
$empresaModel = new Empresa($db);
$estado = $_GET['estado'] ?? null;
$empresas = $empresaModel->listarTodas($estado, 100, 0);
include __DIR__ . '/../../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestionar Empresas</h1>
        <a href="<?php echo BASE_URL; ?>/views/admin/empresas/crear.php" class="btn btn-success"><i class="bi bi-plus-circle me-2"></i>Nueva Empresa</a>
    </div>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="?" class="btn btn-<?php echo !$estado ? 'primary' : 'outline-primary'; ?>">Todas</a>
                <a href="?estado=activa" class="btn btn-<?php echo $estado === 'activa' ? 'success' : 'outline-success'; ?>">Activas</a>
                <a href="?estado=inactiva" class="btn btn-<?php echo $estado === 'inactiva' ? 'secondary' : 'outline-secondary'; ?>">Inactivas</a>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <?php if (!empty($empresas)): ?>
            <?php foreach ($empresas as $emp): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="company-logo">
                                    <?php if ($emp['logo'] && $emp['logo'] !== 'placeholder-logo.png'): ?>
                                        <img src="<?php echo BASE_URL; ?>/assets/images/logos/<?php echo htmlspecialchars($emp['logo']); ?>" alt="">
                                    <?php else: ?>
                                        <i class="bi bi-building"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($emp['nombre_comercial']); ?></h5>
                                    <p class="text-muted small mb-2"><?php echo htmlspecialchars($emp['sector']); ?></p>
                                    <span class="badge bg-<?php echo $emp['estado'] === 'activa' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($emp['estado']); ?></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><?php echo $emp['total_ofertas']; ?> ofertas</small>
                                <div>
                                    <a href="<?php echo BASE_URL; ?>/views/admin/empresas/editar.php?id=<?php echo $emp['id_empresa']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5"><p class="text-muted">No hay empresas registradas</p></div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>