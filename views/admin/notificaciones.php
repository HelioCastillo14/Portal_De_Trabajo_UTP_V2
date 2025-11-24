<?php
session_start();
require_once __DIR__ . '/../../config/session.php';
verificarSesion('admin');
$page_title = 'Notificaciones';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Notificacion.php';
$db = Database::getInstance()->getConnection();
$notificacionModel = new Notificacion($db);
$notificaciones = $notificacionModel->getNotificacionesUsuario('admin', $_SESSION['id_usuario'], 50);
include __DIR__ . '/../layout/header_admin.php';
?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Notificaciones</h1>
        <?php if (!empty($notificaciones)): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>/controllers/NotificacionController.php?action=marcarTodasLeidas">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary">Marcar todas como leídas</button>
            </form>
        <?php endif; ?>
    </div>
    <?php if (!empty($notificaciones)): ?>
        <div class="list-group">
            <?php foreach ($notificaciones as $notif): ?>
                <div class="list-group-item <?php echo $notif['leida'] ? '' : 'bg-light border-start border-primary border-3'; ?>">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?php echo htmlspecialchars($notif['titulo']); ?></h6>
                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                            <small class="text-muted"><?php echo tiempoTranscurrido($notif['fecha_creacion']); ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5"><i class="bi bi-bell-slash" style="font-size: 64px; color: var(--gris-claro);"></i><p class="text-muted mt-3">No tienes notificaciones</p></div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>