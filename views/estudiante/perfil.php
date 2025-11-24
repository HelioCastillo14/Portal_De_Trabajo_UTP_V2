<?php
session_start();
require_once __DIR__ . '/../../config/session.php';
verificarSesion('estudiante');
$page_title = 'Mi Perfil - Estudiante';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Estudiante.php';

$db = Database::getInstance()->getConnection();
$estudianteModel = new Estudiante($db);
$id_estudiante = $_SESSION['id_usuario'];
$estudiante = $estudianteModel->findById($id_estudiante);

include __DIR__ . '/../layout/header_estudiante.php';
?>

<div class="container py-5">
    <h1 class="mb-4" style="font-size: 32px; font-weight: 700;">Mi Perfil</h1>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Información Personal -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-4 fw-bold">Información Personal</h5>
                    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/EstudianteController.php?action=actualizarPerfil">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombres *</label>
                                <input type="text" name="nombres" class="form-control" value="<?php echo htmlspecialchars($estudiante['nombres']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Apellidos *</label>
                                <input type="text" name="apellidos" class="form-control" value="<?php echo htmlspecialchars($estudiante['apellidos']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Correo UTP</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($estudiante['correo_utp']); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Carrera</label>
                                <select name="carrera" class="form-select">
                                    <option value="">Selecciona tu carrera</option>
                                    <?php foreach (CARRERAS_UTP as $carrera): ?>
                                        <option value="<?php echo $carrera; ?>" <?php echo $estudiante['carrera'] === $carrera ? 'selected' : ''; ?>>
                                            <?php echo $carrera; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Descripción del Perfil</label>
                                <textarea name="descripcion_perfil" class="form-control" rows="4" placeholder="Cuéntanos sobre ti, tus habilidades y experiencia..."><?php echo htmlspecialchars($estudiante['descripcion_perfil'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success mt-3">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- CV -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-4 fw-bold">Currículum Vitae (CV)</h5>
                    
                    <?php if ($estudiante['estado_cv'] === 'activo'): ?>
                        <div class="alert alert-success border-0 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-file-pdf fs-1"></i>
                                <div class="flex-grow-1">
                                    <strong>CV Activo</strong>
                                    <p class="mb-0 small">Subido el <?php echo formatearFecha($estudiante['cv_fecha_subida']); ?></p>
                                </div>
                                <div>
                                    <a href="<?php echo BASE_URL; ?>/controllers/EstudianteController.php?action=descargarCV" class="btn btn-sm btn-outline-success me-2">
                                        <i class="bi bi-download"></i> Descargar
                                    </a>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/EstudianteController.php?action=eliminarCV" style="display: inline;" onsubmit="return confirm('¿Seguro que deseas eliminar tu CV?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>/controllers/EstudianteController.php?action=subirCV" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <?php echo $estudiante['estado_cv'] === 'activo' ? 'Actualizar CV' : 'Subir CV'; ?>
                            </label>
                            <input type="file" name="cv" class="form-control" accept=".pdf" required>
                            <small class="text-muted">Solo archivos PDF, máximo 5MB</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-2"></i><?php echo $estudiante['estado_cv'] === 'activo' ? 'Actualizar CV' : 'Subir CV'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="bg-success-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="bi bi-person-circle text-success" style="font-size: 64px;"></i>
                    </div>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?></h5>
                    <p class="text-muted mb-3"><?php echo htmlspecialchars($estudiante['correo_utp']); ?></p>
                    <?php if ($estudiante['carrera']): ?>
                        <p class="small mb-3">
                            <i class="bi bi-book me-1"></i>
                            <?php echo htmlspecialchars($estudiante['carrera']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="text-start">
                        <h6 class="fw-bold mb-3">Estado del Perfil</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small">Completitud</span>
                            <?php
                            $completitud = 0;
                            if (!empty($estudiante['nombres'])) $completitud += 25;
                            if (!empty($estudiante['carrera'])) $completitud += 25;
                            if (!empty($estudiante['descripcion_perfil'])) $completitud += 25;
                            if ($estudiante['estado_cv'] === 'activo') $completitud += 25;
                            ?>
                            <span class="small fw-bold"><?php echo $completitud; ?>%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $completitud; ?>%"></div>
                        </div>
                        <small class="text-muted">
                            <?php if ($completitud === 100): ?>
                                ¡Perfil completo! Estás listo para postularte.
                            <?php else: ?>
                                Completa tu perfil para mejorar tus oportunidades.
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>