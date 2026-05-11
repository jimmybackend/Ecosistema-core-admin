// ========================================
// CONFIGURACIÓN INICIAL
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar primero el selector de temas (define handlers/utilidades)
    initThemeSelector();

    // Cargar tema guardado (compat: 'theme' y el viejo 'preferredTheme')
    loadSavedTheme();

    // Inicializar todos los componentes
    initSidebarToggle();
    initTabs();
    initAccordion();
    initAlerts();
    initModal();
    initDropdowns();
    initSubmenus();
    initSpecialInputs();
});

// ========================================
// THEME HELPERS (unificados)
// ========================================
function applyTheme(theme, opts = {}) {
    const { save = true } = opts;
    const body = document.body;

    // Remover todos los temas
    body.className = '';
    // Agregar el nuevo tema
    body.classList.add(`theme-${theme}`);

    // Guardar preferencia (mantengo ambas keys por compatibilidad)
    if (save) {
        localStorage.setItem('theme', theme);
        localStorage.setItem('preferredTheme', theme);
    }

    // Actualizar botones activos (si existen en esta pantalla)
    updateThemeButtons(theme);
}

function updateThemeButtons(theme) {
    document.querySelectorAll('.theme-option').forEach(option => {
        if (option.dataset.theme === theme) {
            option.classList.add('active');
        } else {
            option.classList.remove('active');
        }
    });
}

function loadSavedTheme() {
    const savedTheme = localStorage.getItem('theme') || localStorage.getItem('preferredTheme') || 'light';
    applyTheme(savedTheme, { save: false });
}

// ========================================
// SIDEBAR TOGGLE
// ========================================
function initSidebarToggle() {
    const sidebarToggle = document.querySelector('.btn-toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            mainContent.classList.toggle('sidebar-open');
        });
    }
}

// ========================================
// THEME SELECTOR
// ========================================
function initThemeSelector() {
    const themeToggle = document.getElementById('themeToggle');
    const themeSelector = document.querySelector('.theme-selector');
    const themeMenu = document.querySelector('.theme-menu');
    const themeBtnSm = document.getElementById('footerThemeToggle');

    // Opciones de tema
    document.querySelectorAll('.theme-option').forEach(option => {
        option.addEventListener('click', () => {
            const theme = option.dataset.theme;
            applyTheme(theme);

            // Cerrar menú si existe
            if (themeMenu) {
                themeMenu.classList.remove('show');
            }
        });
    });

    // Toggle del menú de temas (clic en el botón)
    if (themeSelector && themeToggle && themeMenu) {
        themeSelector.addEventListener('click', (e) => {
            if (e.target === themeToggle || e.target.closest('.theme-btn')) {
                themeMenu.classList.toggle('show');
            }
        });
    }

    // Theme button en footer (alternar light/dark)
    if (themeBtnSm) {
        themeBtnSm.addEventListener('click', () => {
            const isDark = document.body.classList.contains('theme-dark');
            applyTheme(isDark ? 'light' : 'dark');
        });
    }

    // Exponer helpers si los necesitas desde HTML
    window.changeTheme = function(theme) {
        applyTheme(theme);
    };

    window.closeAllMenus = function() {
        document.querySelectorAll('.dropdown-menu, .theme-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    };
}

// ========================================
// TABS

// ========================================
function initTabs() {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.dataset.tab;
            
            // Remover active de todos
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Agregar active al seleccionado
            tab.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
}

// ========================================
// ACCORDION
// ========================================
function initAccordion() {
    const accordionHeaders = document.querySelectorAll('.accordion-header');

    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const isActive = content.classList.contains('show');
            
            // Cerrar todos
            document.querySelectorAll('.accordion-content').forEach(c => {
                c.classList.remove('show');
            });
            document.querySelectorAll('.accordion-header').forEach(h => {
                h.setAttribute('aria-expanded', 'false');
            });
            
            // Abrir el seleccionado si estaba cerrado
            if (!isActive) {
                content.classList.add('show');
                header.setAttribute('aria-expanded', 'true');
            }
        });
    });
}

// ========================================
// ALERTS
// ========================================
function initAlerts() {
    const alertCloses = document.querySelectorAll('.alert-close');
    
    alertCloses.forEach(btn => {
        btn.addEventListener('click', () => {
            btn.parentElement.remove();
        });
    });
}

// ========================================
// MODAL
// ========================================
function initModal() {
    const modal = document.getElementById('exampleModal');
    const openModalBtn = document.getElementById('openModal');
    const modalCloseBtns = document.querySelectorAll('.modal-close');
    const modalOverlay = document.querySelector('.modal-overlay');

    if (openModalBtn && modal) {
        openModalBtn.addEventListener('click', () => {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    }

    modalCloseBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    });

    if (modalOverlay) {
        modalOverlay.addEventListener('click', () => {
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    }
}

// ========================================
// DROPDOWNS
// ========================================
function initDropdowns() {
    const userDropdown = document.querySelector('.user-dropdown');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (userDropdown && dropdownMenu) {
        document.addEventListener('click', (e) => {
            if (!userDropdown.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });

        userDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
    }
}

// ========================================
// SUBMENUS (NUEVO)
// ========================================
function initSubmenus() {
    const submenuLinks = document.querySelectorAll('.nav-item.has-submenu > .nav-link');
    
    submenuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            parent.classList.toggle('active');
        });
    });
}

// ========================================
// SPECIAL INPUTS (NUEVO)
// ========================================
function initSpecialInputs() {
    // Range Input con valor dinámico
    const rangeInput = document.getElementById('rangeInput');
    const rangeValue = document.getElementById('rangeValue');
    
    if (rangeInput && rangeValue) {
        rangeValue.textContent = rangeInput.value;
        
        rangeInput.addEventListener('input', function() {
            rangeValue.textContent = this.value;
        });
    }

    // Color Input con preview
    const colorInput = document.getElementById('colorInput');
    const colorPreview = document.getElementById('colorPreview');
    
    if (colorInput && colorPreview) {
        colorPreview.style.backgroundColor = colorInput.value;
        
        colorInput.addEventListener('input', function() {
            colorPreview.style.backgroundColor = this.value;
        });
    }

    // File Input - nombre del archivo
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    
    if (fileInput && fileName) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
            } else {
                fileName.textContent = 'Ningún archivo seleccionado';
            }
        });
    }

    // Multiple File Input
    const multipleFileInput = document.getElementById('multipleFileInput');
    const multipleFileName = document.getElementById('multipleFileName');
    
    if (multipleFileInput && multipleFileName) {
        multipleFileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                multipleFileName.textContent = `${this.files.length} archivo(s) seleccionado(s)`;
            } else {
                multipleFileName.textContent = 'Ningún archivo seleccionado';
            }
        });
    }
}

// ========================================
// UTILIDADES
// ========================================

// Función para mostrar mensaje de éxito
window.showSuccess = function(message) {
    const alertsContainer = document.querySelector('.alerts-container');
    if (alertsContainer) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success';
        alert.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <strong>¡Éxito!</strong> ${message}
            <button class="alert-close">&times;</button>
        `;
        alertsContainer.insertBefore(alert, alertsContainer.firstChild);
        
        // Auto cerrar después de 5 segundos
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
        
        // Evento para cerrar manualmente
        alert.querySelector('.alert-close').addEventListener('click', () => {
            alert.remove();
        });
    }
};

// Función para mostrar mensaje de error
window.showError = function(message) {
    const alertsContainer = document.querySelector('.alerts-container');
    if (alertsContainer) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <strong>Error:</strong> ${message}
            <button class="alert-close">&times;</button>
        `;
        alertsContainer.insertBefore(alert, alertsContainer.firstChild);
        
        // Auto cerrar después de 5 segundos
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
        
        // Evento para cerrar manualmente
        alert.querySelector('.alert-close').addEventListener('click', () => {
            alert.remove();
        });
    }
};

// ========================================
// EVENTOS GLOBALES
// ========================================
// Cerrar menús al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-dropdown') && !e.target.closest('.theme-selector')) {
        closeAllMenus();
    }
});

// Tecla ESC para cerrar menús y modales
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllMenus();
        const modal = document.querySelector('.modal.show');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    }
});

console.log('Dashboard script loaded successfully! ✅');