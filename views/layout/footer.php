<!-- FOOTER -->
<footer class="footer mt-5">
    <div class="container">
        <div class="row py-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <h5 class="fw-bold mb-3" style="color: var(--verde-principal);">Portal de Trabajo UTP</h5>
                <p class="text-muted small">
                    Conectando talento UTP con las mejores oportunidades laborales de Panamá.
                </p>
            </div>
            
            <div class="col-md-4 mb-3 mb-md-0">
                <h6 class="fw-bold mb-3">Enlaces Rápidos</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/index.php" class="text-muted text-decoration-none small">
                            <i class="bi bi-house me-2"></i>Inicio
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/views/public/ofertas_publicas.php" class="text-muted text-decoration-none small">
                            <i class="bi bi-briefcase me-2"></i>Empleos
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo BASE_URL; ?>/views/public/contacto.php" class="text-muted text-decoration-none small">
                            <i class="bi bi-envelope me-2"></i>Contacto
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="col-md-4">
                <h6 class="fw-bold mb-3">Contacto</h6>
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt me-2"></i>
                    Avenida Manuel Espinosa Batista<br>
                    Campus Víctor Levi Sasso<br>
                    Panamá, República de Panamá
                </p>
                <p class="text-muted small mb-2">
                    <i class="bi bi-telephone me-2"></i>
                    +507 560-3000
                </p>
                <p class="text-muted small">
                    <i class="bi bi-envelope me-2"></i>
                    info@utp.ac.pa
                </p>
            </div>
        </div>
        
        <hr class="my-3">
        
        <div class="row py-3">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                <small class="text-muted">
                    © <?php echo date('Y'); ?> Universidad Tecnológica de Panamá. Todos los derechos reservados.
                </small>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <small class="text-muted">
                    Desarrollado por estudiantes UTP
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript Global -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>

</body>
</html>