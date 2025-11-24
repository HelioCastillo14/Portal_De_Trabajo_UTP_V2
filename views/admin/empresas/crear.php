<?php
session_start();
require_once __DIR__ . '/../../../config/session.php';
verificarSesion('admin');
$page_title = 'Crear Empresa';
require_once __DIR__ . '/../../../config/config.php';
include __DIR__ . '/../../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="<?php echo BASE_URL; ?>/views/admin/empresas/index.php" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="mb-0">Crear Nueva Empresa</h1>
    </div>
    <div class="row"><div class="col-lg-8 mx-auto"><div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/EmpresaController.php?action=crear" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Nombre Legal *</label><input type="text" name="nombre_legal" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Nombre Comercial *</label><input type="text" name="nombre_comercial" class="form-control" required></div>
                <div class="col-12"><label class="form-label fw-semibold">Descripción</label><textarea name="descripcion" class="form-control" rows="3"></textarea></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Sector</label><input type="text" name="sector" class="form-control" placeholder="Tecnología, Finanzas, etc."></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Sitio Web</label><input type="url" name="sitio_web" class="form-control" placeholder="https://"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Teléfono</label><input type="tel" name="telefono" class="form-control"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email Contacto</label><input type="email" name="email_contacto" class="form-control"></div>
                <div class="col-12"><label class="form-label fw-semibold">Dirección</label><input type="text" name="direccion" class="form-control"></div>
                <div class="col-12"><label class="form-label fw-semibold">Logo</label><input type="file" name="logo" class="form-control" accept="image/*"><small class="text-muted">JPG, PNG - Máx 1MB</small></div>
            </div>
            <hr class="my-4">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-2"></i>Crear Empresa</button>
                <a href="<?php echo BASE_URL; ?>/views/admin/empresas/index.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div></div></div></div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>
EOFCREAR

# Crear empresas/editar.php (versión simplificada)
cat > /home/claude/Proyecto_Portal_De_Trabajo_UTP/views/admin/empresas/editar.php << 'EOFEDITAR'
<?php
session_start();
require_once __DIR__ . '/../../../config/session.php';
verificarSesion('admin');
$page_title = 'Editar Empresa';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../models/Empresa.php';
$db = Database::getInstance()->getConnection();
$empresaModel = new Empresa($db);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: ' . BASE_URL . '/views/admin/empresas/index.php'); exit(); }
$empresa = $empresaModel->findById($id);
if (!$empresa) { header('Location: ' . BASE_URL . '/views/admin/empresas/index.php'); exit(); }
include __DIR__ . '/../../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="<?php echo BASE_URL; ?>/views/admin/empresas/index.php" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="mb-0">Editar Empresa</h1>
    </div>
    <div class="row"><div class="col-lg-8 mx-auto"><div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form method="POST" action="<?php echo BASE_URL; ?>/controllers/EmpresaController.php?action=actualizar" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
            <input type="hidden" name="id_empresa" value="<?php echo $empresa['id_empresa']; ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Nombre Legal *</label><input type="text" name="nombre_legal" class="form-control" value="<?php echo htmlspecialchars($empresa['nombre_legal']); ?>" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Nombre Comercial *</label><input type="text" name="nombre_comercial" class="form-control" value="<?php echo htmlspecialchars($empresa['nombre_comercial']); ?>" required></div>
                <div class="col-12"><label class="form-label fw-semibold">Descripción</label><textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($empresa['descripcion']); ?></textarea></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Sector</label><input type="text" name="sector" class="form-control" value="<?php echo htmlspecialchars($empresa['sector']); ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Sitio Web</label><input type="url" name="sitio_web" class="form-control" value="<?php echo htmlspecialchars($empresa['sitio_web']); ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Teléfono</label><input type="tel" name="telefono" class="form-control" value="<?php echo htmlspecialchars($empresa['telefono']); ?>"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" name="email_contacto" class="form-control" value="<?php echo htmlspecialchars($empresa['email_contacto']); ?>"></div>
                <div class="col-12"><label class="form-label fw-semibold">Dirección</label><input type="text" name="direccion" class="form-control" value="<?php echo htmlspecialchars($empresa['direccion']); ?>"></div>
                <div class="col-md-8"><label class="form-label fw-semibold">Logo</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Estado *</label><select name="estado" class="form-select" required><option value="activa" <?php echo $empresa['estado'] === 'activa' ? 'selected' : ''; ?>>Activa</option><option value="inactiva" <?php echo $empresa['estado'] === 'inactiva' ? 'selected' : ''; ?>>Inactiva</option></select></div>
            </div>
            <hr class="my-4">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-2"></i>Guardar</button>
                <a href="<?php echo BASE_URL; ?>/views/admin/empresas/index.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div></div></div></div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>