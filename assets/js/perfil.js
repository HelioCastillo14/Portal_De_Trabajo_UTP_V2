/**
 * PERFIL.JS - Funcionalidad para gestión de perfil
 */

document.addEventListener('DOMContentLoaded', function() {
  // Validación de archivo CV
  const inputCV = document.querySelector('input[name="cv"]');
  if (inputCV) {
      inputCV.addEventListener('change', function(e) {
          const file = e.target.files[0];
          if (!file) return;
          
          // Validar tipo
          if (file.type !== 'application/pdf') {
              alert('Solo se permiten archivos PDF');
              this.value = '';
              return;
          }
          
          // Validar tamaño (5MB)
          if (file.size > 5 * 1024 * 1024) {
              alert('El archivo no debe superar 5MB');
              this.value = '';
              return;
          }
          
          // Mostrar preview
          document.getElementById('nombre-archivo').textContent = file.name;
      });
  }
  
  // Contador de caracteres en descripción
  const descripcion = document.querySelector('textarea[name="descripcion_perfil"]');
  if (descripcion) {
      const maxLength = 500;
      const counter = document.createElement('small');
      counter.className = 'text-muted';
      descripcion.parentNode.appendChild(counter);
      
      function actualizarContador() {
          const remaining = maxLength - descripcion.value.length;
          counter.textContent = `${remaining} caracteres restantes`;
          counter.style.color = remaining < 50 ? '#DC2626' : '#6B7280';
      }
      
      descripcion.addEventListener('input', actualizarContador);
      actualizarContador();
  }
  
  // Confirmar eliminación de CV
  const btnEliminarCV = document.querySelector('form[action*="eliminarCV"]');
  if (btnEliminarCV) {
      btnEliminarCV.addEventListener('submit', function(e) {
          if (!confirm('¿Estás seguro de eliminar tu CV? Esta acción no se puede deshacer.')) {
              e.preventDefault();
          }
      });
  }
});

// Vista previa de foto de perfil (futuro)
function previsualizarFoto(input) {
  if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
          document.getElementById('preview-foto').src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
  }
}