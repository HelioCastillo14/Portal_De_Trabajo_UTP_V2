/**
 * Portal de Trabajo UTP - JavaScript Principal
 * Funciones globales y utilidades
 */

// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    
  // Auto-cerrar alerts después de 5 segundos
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
      setTimeout(() => {
          const bsAlert = new bootstrap.Alert(alert);
          bsAlert.close();
      }, 5000);
  });
  
  // Prevenir doble submit en formularios
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
      form.addEventListener('submit', function() {
          const submitBtn = form.querySelector('button[type="submit"]');
          if (submitBtn) {
              submitBtn.disabled = true;
              submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
          }
      });
  });
  
  // Confirmar acciones destructivas
  const deleteButtons = document.querySelectorAll('[data-confirm]');
  deleteButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
          const message = this.getAttribute('data-confirm') || '¿Estás seguro?';
          if (!confirm(message)) {
              e.preventDefault();
              return false;
          }
      });
  });
  
  // Tooltip de Bootstrap
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

/**
* Formatear fecha relativa (hace X horas/días)
*/
function formatearTiempoRelativo(fecha) {
  const ahora = new Date();
  const fechaOferta = new Date(fecha);
  const diferencia = ahora - fechaOferta;
  
  const segundos = Math.floor(diferencia / 1000);
  const minutos = Math.floor(segundos / 60);
  const horas = Math.floor(minutos / 60);
  const dias = Math.floor(horas / 24);
  
  if (dias > 30) {
      return fechaOferta.toLocaleDateString('es-PA');
  } else if (dias > 0) {
      return `Hace ${dias} ${dias === 1 ? 'día' : 'días'}`;
  } else if (horas > 0) {
      return `Hace ${horas} ${horas === 1 ? 'hora' : 'horas'}`;
  } else if (minutos > 0) {
      return `Hace ${minutos} ${minutos === 1 ? 'minuto' : 'minutos'}`;
  } else {
      return 'Hace un momento';
  }
}

/**
* Mostrar notificación toast
*/
function mostrarToast(mensaje, tipo = 'success') {
  const colores = {
      'success': 'var(--verde-exito)',
      'error': '#dc3545',
      'info': '#0dcaf0',
      'warning': '#ffc107'
  };
  
  const iconos = {
      'success': 'bi-check-circle',
      'error': 'bi-exclamation-triangle',
      'info': 'bi-info-circle',
      'warning': 'bi-exclamation-circle'
  };
  
  const toast = document.createElement('div');
  toast.className = 'position-fixed bottom-0 end-0 p-3';
  toast.style.zIndex = '9999';
  
  toast.innerHTML = `
      <div class="toast show" role="alert">
          <div class="toast-header" style="background-color: ${colores[tipo]}; color: white;">
              <i class="bi ${iconos[tipo]} me-2"></i>
              <strong class="me-auto">${tipo.charAt(0).toUpperCase() + tipo.slice(1)}</strong>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
          </div>
          <div class="toast-body">
              ${mensaje}
          </div>
      </div>
  `;
  
  document.body.appendChild(toast);
  
  setTimeout(() => {
      toast.remove();
  }, 5000);
}

/**
* Validar email institucional UTP
*/
function validarEmailUTP(email) {
  const regex = /^[a-zA-Z0-9._%+-]+@utp\.ac\.pa$/;
  return regex.test(email);
}

/**
* Validar formulario de contacto
*/
function validarFormularioContacto(form) {
  const nombre = form.querySelector('#nombre').value.trim();
  const email = form.querySelector('#correo').value.trim();
  const mensaje = form.querySelector('#mensaje').value.trim();
  
  if (nombre.length < 3) {
      mostrarToast('El nombre debe tener al menos 3 caracteres', 'error');
      return false;
  }
  
  if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
      mostrarToast('Correo electrónico inválido', 'error');
      return false;
  }
  
  if (mensaje.length < 10) {
      mostrarToast('El mensaje debe tener al menos 10 caracteres', 'error');
      return false;
  }
  
  return true;
}

/**
* Copiar al portapapeles
*/
function copiarAlPortapapeles(texto) {
  navigator.clipboard.writeText(texto).then(() => {
      mostrarToast('Copiado al portapapeles', 'success');
  }).catch(err => {
      mostrarToast('Error al copiar', 'error');
  });
}

/**
* Scroll suave a elemento
*/
function scrollSuave(elementId) {
  const elemento = document.getElementById(elementId);
  if (elemento) {
      elemento.scrollIntoView({ 
          behavior: 'smooth',
          block: 'start'
      });
  }
}