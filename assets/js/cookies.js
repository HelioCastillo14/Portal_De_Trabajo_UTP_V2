/**
 * COOKIES.JS - Gestión de cookies y consentimiento
 * Portal de Trabajo UTP
 */

// Gestión de cookies
const CookieManager = {
  set: function(name, value, days = 365) {
      const date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      const expires = "expires=" + date.toUTCString();
      document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Strict";
  },
  
  get: function(name) {
      const nameEQ = name + "=";
      const cookies = document.cookie.split(';');
      for(let i = 0; i < cookies.length; i++) {
          let c = cookies[i];
          while (c.charAt(0) === ' ') c = c.substring(1);
          if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
      }
      return null;
  },
  
  delete: function(name) {
      document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";
  },
  
  exists: function(name) {
      return this.get(name) !== null;
  }
};

// Banner de consentimiento de cookies
function mostrarBannerCookies() {
  if (CookieManager.exists('cookies_accepted')) return;
  
  const banner = document.createElement('div');
  banner.id = 'cookie-banner';
  banner.className = 'cookie-banner';
  banner.innerHTML = `
      <div class="cookie-content">
          <div class="cookie-icon">
              <i class="bi bi-shield-check"></i>
          </div>
          <div class="cookie-text">
              <strong>Uso de Cookies</strong>
              <p>Utilizamos cookies para mejorar tu experiencia en el portal. Al continuar navegando, aceptas nuestra política de cookies.</p>
          </div>
          <div class="cookie-actions">
              <button onclick="aceptarCookies()" class="btn btn-success">Aceptar</button>
              <button onclick="rechazarCookies()" class="btn btn-outline-secondary">Rechazar</button>
          </div>
      </div>
  `;
  document.body.appendChild(banner);
  setTimeout(() => banner.classList.add('show'), 100);
}

function aceptarCookies() {
  CookieManager.set('cookies_accepted', 'true', 365);
  ocultarBannerCookies();
}

function rechazarCookies() {
  CookieManager.set('cookies_accepted', 'false', 30);
  ocultarBannerCookies();
}

function ocultarBannerCookies() {
  const banner = document.getElementById('cookie-banner');
  if (banner) {
      banner.classList.remove('show');
      setTimeout(() => banner.remove(), 300);
  }
}

// Recordar filtros de búsqueda
const FiltrosManager = {
  guardar: function(filtros) {
      if (CookieManager.get('cookies_accepted') === 'true') {
          localStorage.setItem('filtros_busqueda', JSON.stringify(filtros));
      }
  },
  
  cargar: function() {
      if (CookieManager.get('cookies_accepted') === 'true') {
          const filtros = localStorage.getItem('filtros_busqueda');
          return filtros ? JSON.parse(filtros) : null;
      }
      return null;
  },
  
  limpiar: function() {
      localStorage.removeItem('filtros_busqueda');
  }
};

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(mostrarBannerCookies, 1000);
});

// Exportar para uso global
window.CookieManager = CookieManager;
window.FiltrosManager = FiltrosManager;