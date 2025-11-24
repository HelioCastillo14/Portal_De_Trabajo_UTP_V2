/**
 * POSTULACION.JS - Funcionalidad para postulaciones
 */

document.addEventListener('DOMContentLoaded', function() {
  // Confirmar postulación
  const formPostular = document.getElementById('formPostular');
  if (formPostular) {
      formPostular.addEventListener('submit', function(e) {
          if (!confirm('¿Estás seguro que deseas postularte a esta oferta?')) {
              e.preventDefault();
              return false;
          }
          
          // Deshabilitar botón para evitar doble envío
          const btn = this.querySelector('button[type="submit"]');
          if (btn) {
              btn.disabled = true;
              btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Postulando...';
          }
      });
  }
  
  // Confirmar cancelación de postulación
  const formsCancelar = document.querySelectorAll('form[action*="cancelar"]');
  formsCancelar.forEach(form => {
      form.addEventListener('submit', function(e) {
          if (!confirm('¿Deseas cancelar esta postulación?')) {
              e.preventDefault();
          }
      });
  });
  
  // Filtrar postulaciones por estado
  const filtroEstado = document.getElementById('filtro-estado');
  if (filtroEstado) {
      filtroEstado.addEventListener('change', function() {
          const estado = this.value;
          const cards = document.querySelectorAll('.postulacion-card');
          
          cards.forEach(card => {
              if (!estado || card.dataset.estado === estado) {
                  card.style.display = '';
              } else {
                  card.style.display = 'none';
              }
          });
      });
  }
});

// Tracking de postulaciones
function trackPostulacion(idOferta, estado) {
  if (typeof gtag !== 'undefined') {
      gtag('event', 'postulacion', {
          'event_category': 'engagement',
          'event_label': `oferta_${idOferta}`,
          'value': estado
      });
  }
}