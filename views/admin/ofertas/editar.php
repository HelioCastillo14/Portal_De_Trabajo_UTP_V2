<?php
session_start();
require_once __DIR__ . '/../../../config/session.php';
verificarSesion('admin');
$page_title = 'Editar Oferta';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../models/Oferta.php';
require_once __DIR__ . '/../../../models/Empresa.php';
require_once __DIR__ . '/../../../models/Habilidad.php';
$db = Database::getInstance()->getConnection();
$ofertaModel = new Oferta($db);
$empresaModel = new Empresa($db);
$habilidadModel = new Habilidad($db);
$id_oferta = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_oferta <= 0) { header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php'); exit(); }
$oferta = $ofertaModel->findById($id_oferta);
if (!$oferta) { header('Location: ' . BASE_URL . '/views/admin/ofertas/index.php'); exit(); }
$empresas = $empresaModel->listarTodas();
$habilidades = $habilidadModel->listarTodas();
$habilidades_oferta = $ofertaModel->getHabilidades($id_oferta);
$ids_habilidades = array_column($habilidades_oferta, 'id_habilidad');
include __DIR__ . '/../../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="<?php echo BASE_URL; ?>/views/admin/ofertas/index.php" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="mb-0">Editar Oferta</h1>
    </div>
    <div class="row">
        <div class="col-lg-10 col-xl-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/OfertaController.php?action=actualizar">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        <input type="hidden" name="id_oferta" value="<?php echo $oferta['id_oferta']; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Empresa *</label>
                                <select name="id_empresa" class="form-select" required>
                                    <?php foreach ($empresas as $emp): ?>
                                        <option value="<?php echo $emp['id_empresa']; ?>" <?php echo $oferta['id_empresa'] == $emp['id_empresa'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($emp['nombre_comercial']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha Límite *</label>
                                <input type="date" name="fecha_limite" class="form-control" value="<?php echo $oferta['fecha_limite']; ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Título *</label>
                                <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($oferta['titulo']); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción *</label>
                                <textarea name="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($oferta['descripcion']); ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Requisitos</label>
                                <textarea name="requisitos" class="form-control" rows="3"><?php echo htmlspecialchars($oferta['requisitos']); ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Responsabilidades</label>
                                <textarea name="responsabilidades" class="form-control" rows="3"><?php echo htmlspecialchars($oferta['responsabilidades']); ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Beneficios</label>
                                <textarea name="beneficios" class="form-control" rows="3"><?php echo htmlspecialchars($oferta['beneficios']); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Salario Mínimo</label>
                                <input type="number" name="salario_min" class="form-control" value="<?php echo $oferta['salario_min']; ?>" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Salario Máximo</label>
                                <input type="number" name="salario_max" class="form-control" value="<?php echo $oferta['salario_max']; ?>" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Modalidad *</label>
                                <select name="modalidad" class="form-select" required>
                                    <option value="remoto" <?php echo $oferta['modalidad'] === 'remoto' ? 'selected' : ''; ?>>Remoto</option>
                                    <option value="presencial" <?php echo $oferta['modalidad'] === 'presencial' ? 'selected' : ''; ?>>Presencial</option>
                                    <option value="hibrido" <?php echo $oferta['modalidad'] === 'hibrido' ? 'selected' : ''; ?>>Híbrido</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tipo *</label>
                                <select name="tipo_empleo" class="form-select" required>
                                    <option value="tiempo_completo" <?php echo $oferta['tipo_empleo'] === 'tiempo_completo' ? 'selected' : ''; ?>>Tiempo Completo</option>
                                    <option value="medio_tiempo" <?php echo $oferta['tipo_empleo'] === 'medio_tiempo' ? 'selected' : ''; ?>>Medio Tiempo</option>
                                    <option value="pasantia" <?php echo $oferta['tipo_empleo'] === 'pasantia' ? 'selected' : ''; ?>>Pasantía</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nivel *</label>
                                <select name="nivel_experiencia" class="form-select" required>
                                    <option value="junior" <?php echo $oferta['nivel_experiencia'] === 'junior' ? 'selected' : ''; ?>>Junior</option>
                                    <option value="mid" <?php echo $oferta['nivel_experiencia'] === 'mid' ? 'selected' : ''; ?>>Mid-Level</option>
                                    <option value="senior" <?php echo $oferta['nivel_experiencia'] === 'senior' ? 'selected' : ''; ?>>Senior</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Ubicación</label>
                                <input type="text" name="ubicacion" class="form-control" value="<?php echo htmlspecialchars($oferta['ubicacion']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Estado *</label>
                                <select name="estado" class="form-select" required>
                                    <option value="activa" <?php echo $oferta['estado'] === 'activa' ? 'selected' : ''; ?>>Activa</option>
                                    <option value="cerrada" <?php echo $oferta['estado'] === 'cerrada' ? 'selected' : ''; ?>>Cerrada</option>
                                    <option value="tomada" <?php echo $oferta['estado'] === 'tomada' ? 'selected' : ''; ?>>Tomada</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Habilidades</label>
                                <select name="habilidades[]" class="form-select" multiple size="8">
                                    <?php foreach ($habilidades as $hab): ?>
                                        <option value="<?php echo $hab['id_habilidad']; ?>" <?php echo in_array($hab['id_habilidad'], $ids_habilidades) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($hab['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-2"></i>Guardar Cambios</button>
                            <a href="<?php echo BASE_URL; ?>/views/admin/ofertas/index.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../layout/footer.php'; ?>