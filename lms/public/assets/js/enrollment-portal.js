/**
 * Enrollment Portal — UI interactions
 */
(function () {
    'use strict';

    // Toastr defaults
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000,
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };
    }

    // Sidebar toggle
    const sidebar = document.getElementById('epSidebar');
    const overlay = document.getElementById('epOverlay');
    const toggleBtns = document.querySelectorAll('[data-ep-toggle-sidebar]');

    function toggleSidebar() {
        if (!sidebar) return;
        if (window.innerWidth <= 1024) {
            sidebar.classList.toggle('mobile-open');
            overlay?.classList.toggle('show');
        } else {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('ep-sidebar-collapsed', sidebar.classList.contains('collapsed'));
        }
    }

    toggleBtns.forEach(btn => btn.addEventListener('click', toggleSidebar));
    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('mobile-open');
        overlay.classList.remove('show');
    });

    if (sidebar && localStorage.getItem('ep-sidebar-collapsed') === 'true' && window.innerWidth > 1024) {
        sidebar.classList.add('collapsed');
    }

    // Dark mode toggle
    const DARK_KEY = 'ep-dark-mode';
    const darkToggleBtns = document.querySelectorAll('[data-ep-toggle-dark]');

    function applyDarkMode(isDark) {
        document.body.classList.toggle('ep-dark', isDark);
        document.querySelectorAll('[data-ep-dark-icon]').forEach(icon => {
            icon.classList.toggle('fa-moon', !isDark);
            icon.classList.toggle('fa-sun', isDark);
        });
    }

    applyDarkMode(localStorage.getItem(DARK_KEY) === 'true');

    darkToggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const next = !document.body.classList.contains('ep-dark');
            localStorage.setItem(DARK_KEY, String(next));
            applyDarkMode(next);
        });
    });

    // Tab panels
    document.querySelectorAll('[data-ep-tab]').forEach(tab => {
        tab.addEventListener('click', function () {
            const group = this.closest('[data-ep-tab-group]');
            const target = this.dataset.epTab;
            group?.querySelectorAll('[data-ep-tab]').forEach(t => t.classList.remove('active'));
            group?.querySelectorAll('[data-ep-tab-panel]').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(target)?.classList.add('active');
        });
    });

    // Form autosave (localStorage)
    const form = document.getElementById('enrollmentForm');
    const AUTOSAVE_KEY = 'ep_enrollment_draft';

    if (form) {
        // Restore draft
        try {
            const saved = localStorage.getItem(AUTOSAVE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                Object.keys(data).forEach(key => {
                    const el = form.elements[key];
                    if (!el || el.type === 'file') return;
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        if (el.value === data[key]) el.checked = true;
                    } else {
                        el.value = data[key];
                    }
                });
                if (typeof toastr !== 'undefined') {
                    toastr.info('Draft restored from your last session.', 'Auto-save');
                }
            }
        } catch (e) { /* ignore */ }

        // Save on change
        let saveTimer;
        form.addEventListener('input', function () {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(() => {
                const data = {};
                Array.from(form.elements).forEach(el => {
                    if (!el.name || el.type === 'file' || el.type === 'password') return;
                    if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
                    data[el.name] = el.type === 'checkbox' ? el.value : el.value;
                });
                localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(data));
            }, 800);
        });

        form.addEventListener('submit', () => localStorage.removeItem(AUTOSAVE_KEY));
    }

    // Drag & drop file zones
    document.querySelectorAll('.ep-dropzone').forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        if (!input) return;

        ['dragenter', 'dragover'].forEach(evt => {
            zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.remove('dragover'); });
        });
        zone.addEventListener('drop', e => {
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        zone.addEventListener('click', () => input.click());
        input.addEventListener('change', () => {
            const preview = zone.querySelector('.ep-file-preview-name');
            if (preview && input.files[0]) {
                preview.textContent = input.files[0].name;
                zone.querySelector('.ep-file-preview')?.classList.remove('d-none');
            }
        });
    });

    // Button loading state
    document.querySelectorAll('form').forEach(f => {
        f.addEventListener('submit', function () {
            const btn = this.querySelector('[type="submit"]');
            if (btn && !btn.dataset.noLoading) {
                btn.disabled = true;
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                setTimeout(() => { btn.disabled = false; btn.innerHTML = orig; }, 15000);
            }
        });
    });

    // Animate stat cards on scroll
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.ep-stat-card, .ep-type-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(16px)';
        el.style.transition = 'opacity .5s ease, transform .5s ease';
        observer.observe(el);
    });

    // Flash messages as toasts
    document.querySelectorAll('[data-ep-flash]').forEach(el => {
        const type = el.dataset.epFlash;
        const msg = el.textContent.trim();
        if (typeof toastr !== 'undefined' && msg) toastr[type](msg);
    });
})();
