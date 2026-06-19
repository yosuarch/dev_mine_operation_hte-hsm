# Views — Template System Reference

This document covers the layout architecture, available sections, components,
and conventions used across all MINE-OPS views.

---

## Directory Structure

```
app/Views/
├── layouts/
│   ├── main_layout.php      ← Admin shell (navbar + sidebar + footer)
│   └── landing_page.php     ← Minimal shell (no nav, no sidebar)
│
├── components/
│   ├── navbar.php           ← Top navbar content (injected into main_layout)
│   ├── sidebar.php          ← Desktop aside + mobile offcanvas drawer
│   └── footer.php           ← Page footer
│
├── pages/
│   ├── idx-dashboard.php
│   ├── landing-page/
│   │   └── index.php        ← Role-selector landing page
│   ├── manpower/
│   │   └── idx-manpower.php
│   └── psi/
│       ├── idx-psi.php
│       ├── modal-*.php      ← Modal partials (included via $this->include)
│       ├── tabel-*.php      ← Table HTML partials
│       ├── script-*.php     ← JavaScript partials
│       └── mobile/
│           ├── mobile-operator_driver.php   ← Mobile P2H form
│           └── script/                      ← AJAX scripts for the form
│
└── errors/                  ← CodeIgniter error pages (do not edit)
```

---

## Layouts

### `layouts/main_layout.php` — Admin Layout

Use this for all authenticated / admin pages.

**What it provides globally (no import needed in page):**
| Resource | Version |
|---|---|
| Bootstrap CSS + JS | 5.3.8 |
| Font Awesome | 6.4.0 |
| Navbar component | — |
| Sidebar component | — |
| Footer component | — |
| Sidebar JS controller | — |

**Available sections:**

| Section name | Where it renders | Purpose |
|---|---|---|
| `pageStyles` | `<head>`, after global CSS | Page-specific `<link>` or `<style>` tags |
| `content` | Inside `<main>` | Primary page HTML |
| `modal` | After `</main>`, before Bootstrap JS | Bootstrap modal markup |
| `main-js` | After Bootstrap JS | jQuery, DataTables, other libraries |
| `script` | After `main-js` | Page-specific `<script>` blocks |

**Load order for scripts:**
```
Bootstrap JS  (layout, always)
Sidebar JS    (layout, always)
main-js       (page section)   ← load jQuery + library CDNs here
script        (page section)   ← page logic that depends on main-js
```

**Minimal page skeleton:**
```php
<?= $this->extend('layouts/main_layout') ?>

<?= $this->section('pageStyles'); ?>
<!-- extra CSS for this page only -->
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<h2>Page Title</h2>
<!-- page body -->
<?= $this->endSection(); ?>

<?= $this->section('main-js'); ?>
<script src="...jquery..."></script>
<script src="...datatables..."></script>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script>
  $(document).ready(function () { /* page logic */ });
</script>
<?= $this->endSection(); ?>
```

---

### `layouts/landing_page.php` — Minimal Layout

Use for public-facing or mobile-optimized pages that need no chrome
(no navbar, no sidebar, no footer).

**What it provides globally:**
- Bootstrap CSS + JS (5.3.8)

**Available sections:** `link`, `pageStyles`, `content`, `script`

**Usage:**
```php
<?= $this->extend('layouts/landing_page') ?>

<?= $this->section('content'); ?>
<!-- standalone page content -->
<?= $this->endSection(); ?>
```

---

## Components

Components are **not** extended — they are injected by the layout via
`view('components/name')` or included in a page via `$this->include(...)`.
You never call `$this->extend()` inside a component.

### `components/navbar.php`

Outputs the inner content of the sticky top navbar (`<nav>` wrapper
is provided by `main_layout`). Contains:
- Sidebar toggle button `#sidebarToggleBtn` (FA hamburger icon)
- Brand link
- Right-side "Admin Panel" label (placeholder)

**To add right-side navbar items** (user info, notifications, etc.),
edit the right-side `<div>` in this file.

---

### `components/sidebar.php`

Two separate elements in one file:

| Element | ID | Shown on |
|---|---|---|
| `<aside>` desktop sidebar | `desktopSidebar` | ≥ 992 px (d-none d-lg-flex) |
| `<div>` mobile drawer | `mobileSidebarDrawer` | All widths (Bootstrap offcanvas, fixed-position) |

**Adding a new nav item** — add to both elements:
```html
<!-- Desktop sidebar -->
<a class="nav-link" href="/your-route" data-sidebar-tip="Label">
    <i class="fas fa-icon-name"></i>
    <span class="sidebar-text">Label</span>
</a>

<!-- Mobile drawer (same, without data-sidebar-tip) -->
<a class="nav-link" href="/your-route">
    <i class="fas fa-icon-name"></i>
    <span class="sidebar-text">Label</span>
</a>
```

**`data-sidebar-tip`** — tooltip text shown when the sidebar is collapsed.
Only add it to desktop sidebar links; the mobile drawer doesn't need it.

---

### `components/footer.php`

Simple dark footer with copyright. Included by `main_layout` only.

---

## Sidebar State

The sidebar minimize/maximize state is stored in `localStorage` under the
key `mine_ops_sidebar_minimized` (`"true"` / `"false"`).

State is applied **before first paint** via an inline `<script>` + `<style>`
in `<head>` to avoid a layout flash on page load.

| Expanded | Minimized |
|---|---|
| 250 px wide | 68 px wide |
| Icon + label visible | Icon only (label opacity 0) |
| Tooltips disabled | Tooltips enabled (right-side, on hover) |
| Chevron points left | Chevron points right |

The toggle can be triggered from two places:
1. **Navbar hamburger** (`#sidebarToggleBtn`) — only triggers collapse on ≥ 992 px; opens the mobile drawer otherwise
2. **Sidebar chevron button** (`#sidebarInternalToggle`) — collapse/expand only

---

## CSS Design Tokens

All sidebar-related values are CSS custom properties on `:root` in
`main_layout.php`. Override per-page via `pageStyles` if needed.

| Variable | Default | Notes |
|---|---|---|
| `--navbar-h` | `56px` | Navbar height used in `min-height` calculations |
| `--sidebar-w` | `250px` | Expanded width |
| `--sidebar-w-mini` | `68px` | Minimized width |
| `--sidebar-bg` | `#1e2d40` | Sidebar background |
| `--sidebar-link` | `#94a3b8` | Default link color |
| `--sidebar-hover` | `rgba(255,255,255,.07)` | Link hover background |
| `--sidebar-active` | `#3b82f6` | Active link background |
| `--sidebar-divider` | `rgba(255,255,255,.10)` | Divider line color |
| `--transition` | `0.24s ease` | Global transition timing |

---

## Partials (sub-views via `$this->include`)

Partials are plain PHP/HTML files with no `extend` or `section` calls.
They are included inside a page's section block.

**Naming conventions used in this project:**

| Prefix | Content type |
|---|---|
| `tabel-*.php` | `<table>` HTML only — no `<script>`, no `<style>` |
| `script-*.php` | `<script>` block only — DataTables init, AJAX, etc. |
| `modal-*.php` | Bootstrap modal markup |
| `session-*.php` | Session flash messages |
| `mobile/script/` | AJAX scripts for mobile P2H form |

**Example — including a table and its script:**
```php
<?= $this->section('content'); ?>
<div class="table-responsive">
    <?= $this->include('pages/psi/tabel-prestart-record'); ?>
</div>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<?= $this->include('pages/psi/script-prestart-recorded-tabel'); ?>
<?= $this->endSection(); ?>
```

---

## Page Purposes Quick Reference

| Page | Layout | Purpose |
|---|---|---|
| `pages/landing-page/index.php` | `landing_page` | Role selector (Admin / Group Leader / Operator / PLM) |
| `pages/idx-dashboard.php` | `main_layout` | Admin home |
| `pages/psi/idx-psi.php` | `main_layout` | P2H data management + reporting |
| `pages/manpower/idx-manpower.php` | `main_layout` | Employee list + CRUD |
| `pages/psi/mobile/mobile-operator_driver.php` | `landing_page` | Mobile P2H form for operators |
| `pages/psi/pdf/pdf-psi-report.php` | *(standalone)* | Server-side PDF output |

---

## CDN Dependencies Loaded by Layouts

### `main_layout.php` (always available to admin pages)
- Bootstrap 5.3.8 CSS + JS
- Font Awesome 6.4.0

### Page-level (loaded in `main-js` / `pageStyles` sections as needed)
- jQuery 3.6.0
- DataTables 1.13.6 + Bootstrap5 theme + Responsive 2.5.0 + Buttons 2.4.1
- Chart.js (PSI page)
- Moment.js + DateRangePicker (PSI page)
