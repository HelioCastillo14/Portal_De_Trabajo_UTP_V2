/**
* ADMIN.JS - Funcionalidad para panel administrativo
*/

document.addEventListener('DOMContentLoaded', function() {
  // DataTables para tablas admin
  if (typeof $ !== 'undefined' && $.fn.DataTable) {
      $('.admin-table').DataTable({
          language: {
              url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
          },
          pageLength: 25,
          order: [[0, 'desc']]
      });
  }
  
  // Confirmar acciones destructivas
  document.querySelectorAll('[data-confirm]').forEach(el => {
      el.addEventListener('click', function(e) {
          const mensaje = this.dataset.confirm || '¿Estás seguro?';
          if (!confirm(mensaje)) {
              e.preventDefault();
              return false;
          }
      });
  });
  
  // Auto-submit de selects de estado
  document.querySelectorAll('select[onchange*="submit"]').forEach(select => {
      select.addEventListener('change', function() {
          if (confirm('¿Cambiar el estado?')) {
              this.form.submit();
          } else {
              this.value = this.dataset.original;
          }
      });
      select.dataset.original = select.value;
  });
  
  // Exportar tabla a CSV
  window.exportarTablaCSV = function(tableId, filename) {
      const table = document.getElementById(tableId);
      if (!table) return;
      
      let csv = [];
      const rows = table.querySelectorAll('tr');
      
      rows.forEach(row => {
          const cols = row.querySelectorAll('td, th');
          const rowData = Array.from(cols).map(col => {
              return '"' + col.textContent.trim().replace(/"/g, '""') + '"';
          });
          csv.push(rowData.join(','));
      });
      
      const csvContent = csv.join('\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = filename || 'export.csv';
      link.click();
  };
  
  // Estadísticas en tiempo real (ejemplo)
  function actualizarEstadisticas() {
      fetch('?ajax=stats')
          .then(res => res.json())
          .then(data => {
              document.querySelectorAll('[data-stat]').forEach(el => {
                  const stat = el.dataset.stat;
                  if (data[stat]) el.textContent = data[stat];
              });
          });
  }
  
  // Actualizar cada 30 segundos
  if (document.querySelector('[data-stat]')) {
      setInterval(actualizarEstadisticas, 30000);
  }
});

// Búsqueda en tiempo real en tablas
function buscarEnTabla(input, tableId) {
  const filter = input.value.toLowerCase();
  const table = document.getElementById(tableId);
  const rows = table.querySelectorAll('tbody tr');
  
  rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? '' : 'none';
  });
}

// Preview de logo empresa
function previewLogo(input) {
  if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
          const preview = document.getElementById('logo-preview');
          if (preview) preview.src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
  }
}