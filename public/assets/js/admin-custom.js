document.addEventListener('DOMContentLoaded', function () {
    // ── SISTEMA DE ALERTA PREMIUM (Ultra Estético y Seguro) ──
    const showPremiumAlert = (targetUrl, type = 'exit', isForm = false, targetForm = null) => {
        let overlay = document.getElementById('premium-global-alert');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'premium-global-alert';
            overlay.className = 'premium-alert-overlay shadow-lg';
            overlay.innerHTML = `
                <div class="premium-alert-card">
                    <span class="premium-alert-icon" id="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                    <h5 class="premium-alert-title" id="alert-title">¡Atención!</h5>
                    <p class="premium-alert-text" id="alert-text">Mensaje dinámico...</p>
                    <div class="premium-alert-actions mt-4">
                        <button class="btn-premium-cancel" id="alert-cancel">Cancelar</button>
                        <button class="btn-premium-confirm" id="alert-confirm">Sí, continuar</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
            overlay.querySelector('#alert-cancel').addEventListener('click', () => overlay.classList.remove('show'));
            overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.classList.remove('show'); });
        }

        const iconEl = overlay.querySelector('#alert-icon');
        const titleEl = overlay.querySelector('#alert-title');
        const textEl = overlay.querySelector('#alert-text');
        const confirmBtn = overlay.querySelector('#alert-confirm');

        // El mensaje EXACTO pedido por el usuario para salida del formulario
        iconEl.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #f59e0b; text-shadow: 0 0 15px rgba(245, 158, 11, 0.4);"></i>';
        titleEl.innerText = 'Cambios sin guardar';
        textEl.innerText = 'no has guardado, si avanzas sin guardar perderas tus cambios';
        confirmBtn.innerText = 'Si, avanzar ahora';
        confirmBtn.style.background = '#6366f1';

        confirmBtn.onclick = () => {
            if (isForm && targetForm) targetForm.submit();
            else window.location.href = targetUrl;
            overlay.classList.remove('show');
        };

        setTimeout(() => overlay.classList.add('show'), 10);
    };

    // Escucha de clicks para interceptar acciones críticas a nivel GLOBAL
    document.addEventListener('click', (e) => {
        // Ignorar si es el botón de añadir items de EA para no romper la colección
        if (e.target.closest('.field-collection-add-button')) return;

        // 1. Intercepción de BOTÓN DE SALIDA (Categorías) - Ej: Volver al listado sin guardar
        const exitBtn = e.target.closest('.btn-confirm-exit');
        if (exitBtn) {
            e.preventDefault();
            showPremiumAlert(exitBtn.href, 'exit');
            return;
        }
    }, true);

    // Add logic to close mobile sidebar when clicking outside
    document.addEventListener('click', function (event) {
        if (window.innerWidth <= 991) {
            const body = document.querySelector('body.ea');

            // If the sidebar is visible
            if (body && body.classList.contains('ea-mobile-sidebar-visible')) {
                const sidebar = document.querySelector('.main-sidebar') || document.querySelector('.sidebar-wrapper');
                const toggler = document.querySelector('#navigation-toggler');

                // If the click is outside the sidebar and not on the toggler itself
                if (sidebar && !sidebar.contains(event.target) && toggler && !toggler.contains(event.target)) {
                    // Close the sidebar
                    body.classList.remove('ea-mobile-sidebar-visible');
                    // We also need to remove the no-scroll class from body if it's there
                    document.documentElement.style.overflow = '';
                    document.body.style.overflow = '';
                }
            }
        }
    });

    // Handle touch events as well
    document.addEventListener('touchstart', function (event) {
        if (window.innerWidth <= 991) {
            const body = document.querySelector('body.ea');

            if (body && body.classList.contains('ea-mobile-sidebar-visible')) {
                const sidebar = document.querySelector('.main-sidebar') || document.querySelector('.sidebar-wrapper');
                const toggler = document.querySelector('#navigation-toggler');

                if (sidebar && !sidebar.contains(event.target) && toggler && !toggler.contains(event.target)) {
                    body.classList.remove('ea-mobile-sidebar-visible');
                    document.documentElement.style.overflow = '';
                    document.body.style.overflow = '';
                }
            }
        }
    }, { passive: true });

    // --- Password Toggle Visibility Logic ---
    const initPasswordToggle = () => {
        const passwordFields = document.querySelectorAll('input[type="password"]');

        passwordFields.forEach(field => {
            if (field.dataset.pwToggled === 'true') return;
            field.dataset.pwToggled = 'true';

            const parent = field.parentElement;
            if (parent) {
                parent.style.position = 'relative';

                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.classList.add('btn-password-toggle');
                toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';

                Object.assign(toggleBtn.style, {
                    position: 'absolute',
                    right: '12px',
                    top: '50%',
                    transform: 'translateY(-50%)',
                    background: 'none',
                    border: 'none',
                    cursor: 'pointer',
                    zIndex: '5',
                    color: '#9ca3af',
                    padding: '8px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    outline: 'none'
                });

                parent.appendChild(toggleBtn);
                field.style.paddingRight = '45px';

                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const isPassword = field.getAttribute('type') === 'password';
                    field.setAttribute('type', isPassword ? 'text' : 'password');

                    const icon = toggleBtn.querySelector('i');
                    if (isPassword) {
                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                        toggleBtn.style.color = '#6366f1';
                    } else {
                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                        toggleBtn.style.color = '#9ca3af';
                    }
                });
            }
        });
    };

    initPasswordToggle();

    const observer = new MutationObserver((mutations) => {
        let shouldInit = false;
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length > 0) shouldInit = true;
        });
        if (shouldInit) initPasswordToggle();
    });

    const contentArea = document.querySelector('.content-wrapper') || document.querySelector('.main-content') || document.body;
    if (contentArea) {
        observer.observe(contentArea, { childList: true, subtree: true });
    }

    // --- FORM VALIDATION ESTRICTA (Interceptación Global de Clic) ---
    // Esta estrategia intercepta el clic en el botón ANTES de que EasyAdmin comience su proceso de guardado (evita que el framework se salte el submit)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('button[type="submit"]');
        if (!btn) return;

        let form = btn.hasAttribute('form')
            ? document.getElementById(btn.getAttribute('form'))
            : btn.closest('form');

        if (!form) return;

        const formId = form.getAttribute('id') || '';
        if (form.classList.contains('form-action-search') || formId.includes('delete')) return;

        const requiredFields = Array.from(form.querySelectorAll('input[required], textarea[required], select[required]'))
            .filter(field => {
                // Ignore hidden fields (unless it is a Trix hidden input) or fields that are not displayed
                const style = window.getComputedStyle(field);
                if (style.display === 'none' && !field.classList.contains('trix-editor') && field.type !== 'hidden') return false;
                if (field.offsetParent === null && field.type !== 'hidden') return false; // Not visible
                return true;
            });

        if (requiredFields.length === 0) return;

        form.setAttribute('novalidate', 'novalidate');

        let isValid = true;
        let firstInvalidField = null;
        let missingFieldsNames = [];

        form.querySelectorAll('.manual-invalid').forEach(el => {
            el.classList.remove('manual-invalid');
            el.classList.remove('is-invalid');
        });

        requiredFields.forEach(field => {
            const val = field.value ? field.value.trim() : '';
            if (!val || val === '') {
                isValid = false;
                field.classList.add('is-invalid', 'manual-invalid');

                // Try to find a human-readable name for the field
                const label = form.querySelector(`label[for="${field.id}"]`) || field.closest('.form-group')?.querySelector('label');
                const fieldName = label ? label.textContent.trim().replace('*', '') : field.getAttribute('name') || 'campo desconocido';
                missingFieldsNames.push(fieldName);

                if (!field.dataset.origBorder) {
                    field.dataset.origBorder = field.style.borderColor || '';
                }
                field.style.setProperty('border', '2px solid #ef4444', 'important');

                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
            } else {
                if (field.classList.contains('manual-invalid')) {
                    field.classList.remove('is-invalid', 'manual-invalid');
                    field.style.border = field.dataset.origBorder || '';
                }
            }
        });

        if (!isValid) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const list = missingFieldsNames.filter((v, i, a) => a.indexOf(v) === i).join('\n• ');
            alert(`⚠️ EL FORMULARIO ESTÁ INCOMPLETO ⚠️\n\nFalta completar:\n• ${list}\n\nPor favor, rellena estos campos obligatorios para continuar.`);

            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalidField.focus();
            }
        }
    }, true); // true = Use Capture Phase (Prioridad máxima)

    // Borrado automático del marco rojo al escribir o corregir
    document.addEventListener('input', function (e) {
        if (e.target.hasAttribute('required') && e.target.classList.contains('manual-invalid')) {
            if (e.target.value && e.target.value.trim() !== '') {
                e.target.classList.remove('is-invalid', 'manual-invalid');
                e.target.style.border = e.target.dataset.origBorder || '';
            }
        }
    });

});
