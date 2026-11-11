(() => {
    'use strict';
    const pending = new WeakSet();
    document.addEventListener('submit', event => {
        const form = event.target;
        if (form.method.toLowerCase() === 'get') return;
        if (pending.has(form)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }
        pending.add(form);
        // Let existing validation and AJAX handlers finish before locking native submits.
        queueMicrotask(() => {
            if (event.defaultPrevented) {
                pending.delete(form);
                return;
            }
            form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])').forEach(button => {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
            });
        });
    }, true);
    window.addEventListener('pageshow', event => {
        if (event.persisted) window.location.reload();
    });
    const sidebar = document.getElementById('accordionSidebar');
    if (sidebar) {
        const closeSidebar = () => {
            if (window.matchMedia('(max-width: 767.98px)').matches) {
                sidebar.classList.remove('toggled');
                document.body.classList.remove('toggled');
            }
        };
        document.addEventListener('click', event => {
            if (!sidebar.contains(event.target) && !event.target.closest('#sidebarToggleTop')) closeSidebar();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeSidebar();
        });
    }
})();
