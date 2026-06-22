<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Mine-Operations Management System">
    <title><?= isset($pageTitle) ? esc($pageTitle) : 'Dashboard' ?> — MINE-OPS</title>

    <!-- Global CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ─── Shell ─────────────────────────────────────────────── */
        :root {
            --navbar-h:       56px;
            --sidebar-w:      250px;
            --sidebar-w-mini: 68px;
            --sidebar-bg:     #1e2d40;
            --sidebar-link:   #94a3b8;
            --sidebar-hover:  rgba(255,255,255,.07);
            --sidebar-active: #3b82f6;
            --sidebar-divider:rgba(255,255,255,.10);
            --transition:     0.24s ease;
            --sidebar-link-hover: #e2e8f0;
            /* Navbar tokens (dark defaults) */
            --nav-bg:         #212529;
            --nav-text:       #ffffff;
            --nav-muted:      rgba(255,255,255,.5);
            --nav-border:     transparent;
            --toggle-color:   rgba(255,255,255,.8);
        }

        /* ─── Light theme overrides ──────────────────────────────── */
        [data-bs-theme="light"] {
            --sidebar-bg:         #f1f5f9;
            --sidebar-link:       #475569;
            --sidebar-link-hover: #1e293b;
            --sidebar-hover:      rgba(0,0,0,.06);
            --sidebar-divider:    rgba(0,0,0,.12);
            --nav-bg:             #ffffff;
            --nav-text:           #1e293b;
            --nav-muted:          #64748b;
            --nav-border:         #e2e8f0;
            --toggle-color:       #475569;
        }

        /* ─── System light fallback (no-JS / before script runs) ─── */
        @media (prefers-color-scheme: light) {
            html:not([data-bs-theme="dark"]) {
                --sidebar-bg:         #f1f5f9;
                --sidebar-link:       #475569;
                --sidebar-link-hover: #1e293b;
                --sidebar-hover:      rgba(0,0,0,.06);
                --sidebar-divider:    rgba(0,0,0,.12);
                --nav-bg:             #ffffff;
                --nav-text:           #1e293b;
                --nav-muted:          #64748b;
                --nav-border:         #e2e8f0;
                --toggle-color:       #475569;
            }
        }

        body { overflow-x: hidden; }

        /* ─── Desktop sidebar ────────────────────────────────────── */
        #desktopSidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background-color: var(--sidebar-bg);
            min-height: calc(100vh - var(--navbar-h));
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: width var(--transition);
        }
        #desktopSidebar.sidebar-minimized { width: var(--sidebar-w-mini); }

        /* ─── Sidebar nav links ──────────────────────────────────── */
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--sidebar-link);
            border-radius: 6px;
            margin: 2px 8px;
            padding: 10px 12px;
            white-space: nowrap;
            text-decoration: none;
            transition: background-color var(--transition), color var(--transition);
        }
        .sidebar-nav .nav-link:hover  { background-color: var(--sidebar-hover); color: var(--sidebar-link-hover); }
        .sidebar-nav .nav-link.active { background-color: var(--sidebar-active); color: #fff; }
        .sidebar-nav .nav-link i      { width: 20px; text-align: center; flex-shrink: 0; }

        /* ─── Sidebar text label ─────────────────────────────────── */
        .sidebar-text {
            font-size: .875rem;
            font-weight: 500;
            white-space: nowrap;
            transition: opacity var(--transition);
        }
        /* Keep inner nav at full width so text clips on minimize */
        .sidebar-nav { min-width: var(--sidebar-w); }

        /* Hide text (not layout) when minimized */
        #desktopSidebar.sidebar-minimized .sidebar-text { opacity: 0; pointer-events: none; }

        /* ─── Sidebar footer area ────────────────────────────────── */
        .sidebar-footer { margin-top: auto; padding-bottom: 8px; }
        .sidebar-divider { border-top: 1px solid var(--sidebar-divider); margin: 8px 16px; }

        /* ─── Sidebar section labels ─────────────────────────────── */
        .sidebar-section-label {
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--sidebar-link);
            opacity: .45;
            padding: 14px 20px 2px;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity var(--transition), height var(--transition), padding var(--transition);
        }
        #desktopSidebar.sidebar-minimized .sidebar-section-label {
            opacity: 0;
            height: 0;
            padding: 0;
            pointer-events: none;
        }

        /* ─── Internal collapse button ───────────────────────────── */
        #sidebarInternalToggle {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            background: none;
            border: none;
            color: #475569;
            border-radius: 6px;
            margin: 2px 8px;
            padding: 10px 12px;
            white-space: nowrap;
            cursor: pointer;
            font-size: .875rem;
            font-weight: 500;
            transition: background-color var(--transition), color var(--transition);
        }
        #sidebarInternalToggle:hover { background-color: var(--sidebar-hover); color: #94a3b8; }
        #sidebarInternalToggle i     { width: 20px; text-align: center; flex-shrink: 0; }

        /* ─── Mobile offcanvas (matches sidebar palette) ─────────── */
        #mobileSidebarDrawer { background-color: var(--sidebar-bg) !important; }
        #mobileSidebarDrawer .offcanvas-header {
            border-bottom: 1px solid var(--sidebar-divider);
        }

        /* ─── Navbar theming via CSS tokens ──────────────────────── */
        #mainNavbar {
            background-color: var(--nav-bg) !important;
            border-bottom: 1px solid var(--nav-border) !important;
            transition: background-color 0.24s ease, border-color 0.24s ease;
        }
        #mainNavbar .navbar-brand { color: var(--nav-text) !important; }
        #sidebarToggleBtn         { color: var(--toggle-color) !important; }
        #themeToggleBtn           { color: var(--toggle-color) !important; }
        .nav-muted-text           { color: var(--nav-muted) !important; transition: color 0.24s ease; }

        /* Light mode: mobile drawer text contrast */
        [data-bs-theme="light"] #mobileSidebarDrawer .offcanvas-header .fw-bold,
        [data-bs-theme="light"] #mobileSidebarDrawer .btn-close { filter: invert(1) grayscale(1); }
    </style>

    <!-- Page-level styles injected here -->
    <?= $this->renderSection('pageStyles'); ?>

    <!--
        State is applied before first paint via an inline script to avoid
        a flash of the expanded sidebar on pages that were left minimized.
    -->
    <script>
        (function () {
            /* Sidebar: prevent flash of expanded state */
            if (localStorage.getItem('mine_ops_sidebar_minimized') === 'true') {
                document.documentElement.classList.add('js-sidebar-pre-minimized');
            }
            /* Color mode: resolve and apply before first paint */
            var m = localStorage.getItem('mine_ops_color_mode') || 'system';
            if (m === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else if (m === 'light') {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            } else {
                document.documentElement.setAttribute(
                    'data-bs-theme',
                    window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
                );
            }
        })();
    </script>
    <style>
        .js-sidebar-pre-minimized #desktopSidebar {
            width: var(--sidebar-w-mini) !important;
        }
    </style>
</head>

<body>

    <!-- ── Navbar ──────────────────────────────────────────────── -->
    <nav id="mainNavbar" class="navbar navbar-dark bg-dark sticky-top" style="height:var(--navbar-h);">
        <div class="container-fluid">
            <?= view('components/navbar') ?>
        </div>
    </nav>

    <!-- ── Page shell ──────────────────────────────────────────── -->
    <div class="d-flex p-0 m-0">

        <!-- Sidebar (desktop aside + mobile offcanvas) -->
        <?= view('components/sidebar') ?>

        <!-- Content area -->
        <main class="flex-grow-1 p-4" style="min-height:calc(100vh - var(--navbar-h)); overflow-y:auto;">
            <div class="container-fluid">
                <?= $this->renderSection('content'); ?>
            </div>
        </main>

    </div>

    <!-- ── Footer ──────────────────────────────────────────────── -->
    <footer class="border-top">
        <div class="container-fluid p-0 m-0">
            <?= view('components/footer') ?>
        </div>
    </footer>

    <!-- ── Modals ──────────────────────────────────────────────── -->
    <?= $this->renderSection('modal'); ?>

    <!-- ── Bootstrap JS ────────────────────────────────────────── -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ── Sidebar controller ──────────────────────────────────── -->
    <script>
    (function () {
        const STORAGE_KEY   = 'mine_ops_sidebar_minimized';
        const sidebar       = document.getElementById('desktopSidebar');
        const mobileDrawer  = document.getElementById('mobileSidebarDrawer');
        const navbarBtn     = document.getElementById('sidebarToggleBtn');
        const internalBtn   = document.getElementById('sidebarInternalToggle');
        const chevronIcon   = document.getElementById('sidebarChevronIcon');

        /* ── Restore persisted state ── */
        const isMinimized = localStorage.getItem(STORAGE_KEY) === 'true';
        document.documentElement.classList.remove('js-sidebar-pre-minimized');

        if (isMinimized && sidebar) {
            sidebar.classList.add('sidebar-minimized');
            if (chevronIcon) chevronIcon.className = 'fas fa-chevron-right';
        }

        /* ── Active link highlight ── */
        const path = window.location.pathname;
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(function (link) {
            const href = link.getAttribute('href');
            if (!href) return;
            if (href === path || (href !== '/' && path.startsWith(href))) {
                link.classList.add('active');
            }
        });

        /* ── Tooltip init (enabled only when minimized) ── */
        const tipEls = Array.from(document.querySelectorAll('#desktopSidebar [data-sidebar-tip]'));
        const tips   = tipEls.map(function (el) {
            return new bootstrap.Tooltip(el, {
                placement : 'right',
                trigger   : 'hover',
                title     : el.dataset.sidebarTip
            });
        });

        function syncTips(minimized) {
            tips.forEach(function (t) { minimized ? t.enable() : t.disable(); });
        }
        syncTips(isMinimized);

        /* ── Toggle logic ── */
        function toggleDesktopSidebar() {
            if (!sidebar) return;
            const mini = sidebar.classList.toggle('sidebar-minimized');
            localStorage.setItem(STORAGE_KEY, mini);
            if (chevronIcon) chevronIcon.className = mini ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
            syncTips(mini);
        }

        /* Navbar button: collapse on desktop, open drawer on mobile */
        if (navbarBtn) {
            navbarBtn.addEventListener('click', function () {
                if (window.innerWidth >= 992) {
                    toggleDesktopSidebar();
                } else {
                    bootstrap.Offcanvas.getOrCreateInstance(mobileDrawer).show();
                }
            });
        }

        /* Sidebar's own collapse chevron */
        if (internalBtn) {
            internalBtn.addEventListener('click', toggleDesktopSidebar);
        }
    })();
    </script>

    <!-- ── Color mode controller ──────────────────────────────── -->
    <script>
    (function () {
        var KEY   = 'mine_ops_color_mode';
        var CYCLE = ['system', 'dark', 'light'];
        var ICONS = { system: 'fas fa-circle-half-stroke', dark: 'fas fa-moon', light: 'fas fa-sun' };
        var LABELS = { system: 'Follow system', dark: 'Dark mode', light: 'Light mode' };

        function getMode() { return localStorage.getItem(KEY) || 'system'; }

        function applyTheme(mode) {
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var resolved = mode === 'dark' ? 'dark'
                         : mode === 'light' ? 'light'
                         : (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', resolved);
        }

        function updateBtn(mode) {
            var btn  = document.getElementById('themeToggleBtn');
            var icon = document.getElementById('themeToggleIcon');
            if (!btn || !icon) return;
            icon.className = ICONS[mode];
            btn.title = LABELS[mode];
            btn.setAttribute('aria-label', LABELS[mode]);
        }

        function setMode(mode) {
            localStorage.setItem(KEY, mode);
            applyTheme(mode);
            updateBtn(mode);
        }

        /* Init icon once DOM is ready */
        document.addEventListener('DOMContentLoaded', function () {
            var cur = getMode();
            updateBtn(cur);

            var btn = document.getElementById('themeToggleBtn');
            if (btn) {
                btn.addEventListener('click', function () {
                    var cur  = getMode();
                    var next = CYCLE[(CYCLE.indexOf(cur) + 1) % CYCLE.length];
                    setMode(next);
                });
            }

            /* Re-resolve when OS preference changes (system mode only) */
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                if (getMode() === 'system') applyTheme('system');
            });
        });
    })();
    </script>

    <!-- ── Page-level scripts ───────────────────────────────────── -->
    <?= $this->renderSection('main-js'); ?>
    <?= $this->renderSection('script'); ?>

</body>
</html>
