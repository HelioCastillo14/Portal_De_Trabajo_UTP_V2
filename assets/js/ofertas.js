/**
 * OFERTAS.JS - Funcionalidad para búsqueda y visualización de ofertas
 * Portal de Trabajo UTP
 */

document.addEventListener('DOMContentLoaded', function() {
  // Guardar/cargar filtros de búsqueda
  const formFiltros = document.querySelector('.filtros form');
  if (formFiltros && typeof FiltrosManager !== 'undefined') {
      // Cargar filtros guardados
      const filtrosGuardados = FiltrosManager.cargar();
      if (filtrosGuardados) {
          Object.keys(filtrosGuardados).forEach(key => {
              const input = formFiltros.querySelector(`[name="${key}"]`);
              if (input) input.value = filtrosGuardados[key];
          });
      }
      
      // Guardar filtros al cambiar
      formFiltros.addEventListener('submit', function() {
          const formData = new FormData(formFiltros);
          const filtros = {};
          formData.forEach((value, key) => filtros[key] = value);
          FiltrosManager.guardar(filtros);
      });
  }
  
  // Smooth scroll en lista de ofertas
  const jobsContainer = document.querySelector('.jobs-list-container');
  if (jobsContainer) {
      jobsContainer.style.scrollBehavior = 'smooth';
  }
  
  // Highlight oferta seleccionada
  const urlParams = new URLSearchParams(window.location.search);
  const ofertaId = urlParams.get('id');
  if (ofertaId) {
      const card = document.querySelector(`.job-list-card[data-id="${ofertaId}"]`);
      if (card) {
          card.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
  }
  
  // Contador de resultados
  const jobCards = document.querySelectorAll('.job-list-card, .job-card');
  const contador = document.getElementById('contador-resultados');
  if (contador) {
      contador.textContent = `${jobCards.length} ofertas encontradas`;
  }
});

// Función para filtrar ofertas en tiempo real (búsqueda local)
function filtrarOfertasLocal(termino) {
  const cards = document.querySelectorAll('.job-list-card, .job-card');
  let visibles = 0;
  
  cards.forEach(card => {
      const texto = card.textContent.toLowerCase();
      if (texto.includes(termino.toLowerCase())) {
          card.style.display = '';
          visibles++;
      } else {
          card.style.display = 'none';
      }
  });
  
  return visibles;
}

// Función para compartir oferta
function compartirOferta(idOferta, titulo) {
  if (navigator.share) {
      navigator.share({
          title: titulo,
          text: `Mira esta oferta de empleo: ${titulo}`,
          url: window.location.origin + `/views/public/detalle_oferta_publica.php?id=${idOferta}`
      }).catch(err => console.log('Error al compartir:', err));
  } else {
      // Fallback: copiar enlace
      const url = window.location.origin + `/views/public/detalle_oferta_publica.php?id=${idOferta}`;
      copiarAlPortapapeles(url);
      mostrarToast('Enlace copiado al portapapeles', 'success');
  }
}

// Guardar oferta como favorita (requiere localStorage)
function guardarFavorito(idOferta) {
  let favoritos = JSON.parse(localStorage.getItem('ofertas_favoritas') || '[]');
  if (!favoritos.includes(idOferta)) {
      favoritos.push(idOferta);
      localStorage.setItem('ofertas_favoritas', JSON.stringify(favoritos));
      mostrarToast('Oferta guardada en favoritos', 'success');
      actualizarIconoFavorito(idOferta, true);
  } else {
      favoritos = favoritos.filter(id => id !== idOferta);
      localStorage.setItem('ofertas_favoritas', JSON.stringify(favoritos));
      mostrarToast('Oferta eliminada de favoritos', 'info');
      actualizarIconoFavorito(idOferta, false);
  }
}

function actualizarIconoFavorito(idOferta, esFavorito) {
  const icono = document.querySelector(`[data-oferta-id="${idOferta}"] .icono-favorito`);
  if (icono) {
      icono.className = esFavorito ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
  }
}

// Cargar más ofertas (paginación infinita)
let paginaActual = 1;
let cargando = false;

function cargarMasOfertas() {
  if (cargando) return;
  
  const container = document.querySelector('.ofertas-grid, .jobs-list-container');
  if (!container) return;
  
  cargando = true;
  paginaActual++;
  
  fetch(`?pagina=${paginaActual}&ajax=1`)
      .then(response => response.text())
      .then(html => {
          container.insertAdjacentHTML('beforeend', html);
          cargando = false;
      })
      .catch(err => {
          console.error('Error al cargar ofertas:', err);
          cargando = false;
      });
}

// Scroll infinito
window.addEventListener('scroll', function() {
  if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
      cargarMasOfertas();
  }
});