<script>
    (function () {
        const KEY    = 'mine_ops_color_mode';
        const MODES  = ['system', 'dark', 'light'];
        const ICONS  = { system: 'fa-circle-half-stroke', dark: 'fa-moon', light: 'fa-sun' };
        const LABELS = { system: 'Follow system', dark: 'Dark mode', light: 'Light mode' };

        function applyTheme(mode) {
            const isDark = mode === 'dark' ||
                (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
        }

        function syncButton(mode) {
            const btn  = document.getElementById('themeToggleBtn');
            const icon = document.getElementById('themeToggleIcon');
            if (!btn || !icon) return;
            btn.title = LABELS[mode];
            btn.setAttribute('aria-label', LABELS[mode]);
            icon.className = 'fas ' + ICONS[mode];
        }

        let current = localStorage.getItem(KEY) || 'system';

        // Apply on load (pre-paint script already ran, but sync button state after DOM ready)
        applyTheme(current);
        syncButton(current);

        document.getElementById('themeToggleBtn')?.addEventListener('click', function () {
            current = MODES[(MODES.indexOf(current) + 1) % MODES.length];
            localStorage.setItem(KEY, current);
            applyTheme(current);
            syncButton(current);
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            if (current === 'system') applyTheme('system');
        });
    })();
</script>
