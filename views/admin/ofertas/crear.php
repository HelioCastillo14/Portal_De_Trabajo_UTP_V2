<?php
session_start();
require_once __DIR__ . '/../../../config/session.php';
verificarSesion('admin');
$page_title = 'Crear Oferta';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../models/Empresa.php';
require_once __DIR__ . '/../../../models/Habilidad.php';
$db = Database::getInstance()->getConnection();
$empresaModel = new Empresa($db);
$habilidadModel = new Habilidad($db);
$empresas = $empresaModel->listarActivas();
$habilidades = $habilidadModel->listarTodas();
include __DIR__ . '/../../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="<?php echo BASE_URL; ?>/views/admin/ofertas/index.php" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="mb-0">Crear Nueva Oferta</h1>
    </div>
    <div class="row">
        <div class="col-lg-10 col-xl-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/OfertaController.php?action=crear">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Empresa *</label>
                                <select name="id_empresa" class="form-select" required>
                                    <option value="">Seleccione una empresa</option>
                                    <?php foreach ($empresas as $emp): ?>
                                        <option value="<?php echo $emp['id_empresa']; ?>"><?php echo htmlspecialchars($emp['nombre_comercial']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha Límite *</label>
                                <input type="date" name="fecha_limite" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Título del Puesto *</label>
                                <input type="text" name="titulo" class="form-control" placeholder="Ej: Desarrollador Full Stack" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción *</label>
                                <textarea name="descripcion" class="form-control" rows="4" placeholder="Describe la oferta laboral..." required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Requisitos</label>
                                <textarea name="requisitos" class="form-control" rows="3" placeholder="Lista los requisitos del puesto..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Responsabilidades</label>
                                <textarea name="responsabilidades" class="form-control" rows="3" placeholder="Describe las responsabilidades..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Beneficios</label>
                                <textarea name="beneficios" class="form-control" rows="3" placeholder="Lista los beneficios del puesto..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Salario Mínimo</label>
                                <input type="number" name="salario_min" class="form-control" placeholder="1500" min="0" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Salario Máximo</label>
                                <input type="number" name="salario_max" class="form-control" placeholder="2500" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Modalidad *</label>
                                <select name="modalidad" class="form-select" required>
                                    <option value="remoto">Remoto</option>
                                    <option value="presencial">Presencial</option>
                                    <option value="hibrido">Híbrido</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tipo de Empleo *</label>
                                <select name="tipo_empleo" class="form-select" required>
                                    <option value="tiempo_completo">Tiempo Completo</option>
                                    <option value="medio_tiempo">Medio Tiempo</option>
                                    <option value="pasantia">Pasantía</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nivel *</label>
                                <select name="nivel_experiencia" class="form-select" required>
                                    <option value="junior">Junior</option>
                                    <option value="mid">Mid-Level</option>
                                    <option value="senior">Senior</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Ubicación</label>
                                <input type="text" name="ubicacion" class="form-control" placeholder="Ciudad de Panamá">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Habilidades Requeridas</label>
                                <select name="habilidades[]" class="form-select" multiple size="8">
                                    <?php foreach ($habilidades as $hab): ?>
                                        <option value="<?php echo $hab['id_habilidad']; ?>"><?php echo htmlspecialchars($hab['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Mantén presionado Ctrl (Windows) o Cmd (Mac) para seleccionar múltiples</small>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-2"></i>Crear Oferta
                            </button>
                            <a href="<?php echo BASE_URL; ?>/views/admin/ofertas/index.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>