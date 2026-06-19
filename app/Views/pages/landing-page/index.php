<?= $this->extend('layouts/landing_page') ?>

<?= $this->section('link'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?= $this->endSection(); ?>

<?= $this->section('pageStyles'); ?>
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── Design tokens (logo green palette) ────────────────── */
    :root {
        --bg         : #091a11;
        --surface    : rgba(13,32,20,.75);
        --border     : rgba(255,255,255,.07);
        --green      : #38b549;
        --green-d    : #16873d;
        --teal       : #1ab5a4;
        --lime       : #7dc242;
        --sky        : #4fb8e8;
        --text       : #e8f5ea;
        --muted      : #4d7a57;
        --radius     : 14px;
        --transition : .22s ease;
    }

    html, body {
        min-height: 100vh;
        background: var(--bg);
        color: var(--text);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        overflow-x: hidden;
    }

    /* Scrollbar hidden but functional */
    body { scrollbar-width: none; -ms-overflow-style: none; }
    body::-webkit-scrollbar { display: none; }

    /* ── Animated orbs ─────────────────────────────────────── */
    .bg-orb {
        position: fixed; border-radius: 50%;
        filter: blur(110px); pointer-events: none; z-index: 0;
        animation: orbDrift ease-in-out infinite;
    }
    .orb-1 { width:650px; height:650px; background:radial-gradient(circle,#15803d 0%,transparent 70%); top:-200px; left:-180px; opacity:.26; animation-duration:30s; }
    .orb-2 { width:480px; height:480px; background:radial-gradient(circle,#0d9488 0%,transparent 70%); bottom:-140px; right:-140px; opacity:.20; animation-duration:36s; animation-direction:reverse; animation-delay:-12s; }
    .orb-3 { width:300px; height:300px; background:radial-gradient(circle,#3f6212 0%,transparent 70%); top:50%; left:60%; opacity:.16; animation-duration:22s; animation-delay:-7s; }
    @keyframes orbDrift {
        0%,100% { transform: translate(0,0) scale(1); }
        33%      { transform: translate(30px,-30px) scale(1.05); }
        66%      { transform: translate(-20px,18px) scale(.97); }
    }

    #particleCanvas {
        position: fixed; inset: 0; z-index: 0; pointer-events: none;
    }

    /* ── Page shell ────────────────────────────────────────── */
    .lp-shell {
        position: relative; z-index: 1;
        min-height: 100vh;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 48px 20px 40px;
        gap: 40px;
    }

    /* ── Header ────────────────────────────────────────────── */
    .lp-header {
        text-align: center;
        animation: fadeUp .6s cubic-bezier(.22,.68,0,1.2) both;
    }
    .lp-brand-row {
        display: flex; align-items: center; justify-content: center;
        gap: 14px; margin-bottom: 10px;
    }
    .lp-logo-badge {
        width: 52px; height: 52px; border-radius: 13px;
        background: rgba(56,181,73,.14);
        border: 1px solid rgba(56,181,73,.30);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; color: var(--green);
        box-shadow: 0 0 28px rgba(56,181,73,.18);
    }
    .lp-app-name {
        font-size: 2rem; font-weight: 800; letter-spacing: .06em; color: #fff;
    }
    .lp-tagline {
        font-size: .88rem; color: var(--muted); margin-top: 4px; letter-spacing: .02em;
    }
    .lp-prompt {
        margin-top: 18px; font-size: .95rem; color: #6ea87a; font-weight: 500;
    }

    /* ── Role grid ─────────────────────────────────────────── */
    .role-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
        width: 100%; max-width: 780px;
        animation: fadeUp .6s cubic-bezier(.22,.68,0,1.2) .1s both;
    }

    /* ── Role card ─────────────────────────────────────────── */
    .role-card {
        display: flex; flex-direction: column;
        background: var(--surface);
        backdrop-filter: blur(16px);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 28px 26px;
        text-decoration: none; color: var(--text);
        transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
        position: relative; overflow: hidden;
        cursor: pointer;
    }

    /* Per-card accent via CSS custom property */
    .role-card[data-accent="green"] { --accent: #38b549; --accent-glow: rgba(56,181,73,.18); }
    .role-card[data-accent="teal"]  { --accent: #1ab5a4; --accent-glow: rgba(26,181,164,.18); }
    .role-card[data-accent="lime"]  { --accent: #7dc242; --accent-glow: rgba(125,194,66,.18); }
    .role-card[data-accent="sky"]   { --accent: #4fb8e8; --accent-glow: rgba(79,184,232,.18); }

    /* Top accent stripe */
    .role-card::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; height: 2px;
        background: linear-gradient(90deg, transparent, var(--accent), transparent);
        opacity: 0;
        transition: opacity var(--transition);
    }
    /* Corner glow */
    .role-card::after {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(ellipse at top left, var(--accent-glow) 0%, transparent 60%);
        opacity: 0;
        transition: opacity var(--transition);
    }

    .role-card:hover {
        transform: translateY(-5px);
        border-color: rgba(var(--accent), .35);
        border-color: color-mix(in srgb, var(--accent) 35%, transparent);
        box-shadow: 0 20px 50px rgba(0,0,0,.35), 0 0 0 1px var(--accent-glow);
    }
    .role-card:hover::before { opacity: 1; }
    .role-card:hover::after  { opacity: 1; }
    .role-card:hover .rc-icon-wrap { background: var(--accent-glow); border-color: var(--accent); }
    .role-card:hover .rc-icon-wrap i { color: var(--accent); }
    .role-card:hover .rc-cta  { color: var(--accent); }
    .role-card:hover .rc-cta i { transform: translateX(4px); }

    /* Card icon */
    .rc-icon-wrap {
        width: 44px; height: 44px; border-radius: 10px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.10);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; color: #94a3b8;
        margin-bottom: 18px;
        transition: background var(--transition), border-color var(--transition);
    }
    .rc-icon-wrap i { transition: color var(--transition); }

    /* Card tag */
    .rc-tag {
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .08em;
        color: var(--muted); margin-bottom: 6px;
    }

    /* Card title */
    .rc-title {
        font-size: 1rem; font-weight: 700; color: #fff;
        margin-bottom: 8px; line-height: 1.3;
    }

    /* Card description */
    .rc-desc {
        font-size: .82rem; color: #4d7a57;
        line-height: 1.6; flex: 1;
    }

    /* Device notice badge */
    .rc-notice {
        display: inline-flex; align-items: center; gap: 5px;
        margin-top: 10px;
        font-size: .72rem; color: #64748b;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 20px;
        padding: 3px 8px; width: fit-content;
    }

    /* CTA row */
    .rc-cta {
        display: flex; align-items: center; gap: 6px;
        margin-top: 20px;
        font-size: .82rem; font-weight: 600;
        color: #4d7a57;
        transition: color var(--transition);
    }
    .rc-cta i { font-size: .75rem; transition: transform var(--transition); }

    /* ── Page footer ───────────────────────────────────────── */
    .lp-footer {
        font-size: .75rem; color: #2d5538; text-align: center;
        animation: fadeUp .6s cubic-bezier(.22,.68,0,1.2) .2s both;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Responsive ────────────────────────────────────────── */
    @media (max-width: 600px) {
        .role-grid { grid-template-columns: 1fr; max-width: 400px; }
        .lp-shell  { padding: 36px 16px 32px; }
    }
    @media (max-width: 340px) {
        .role-card { padding: 22px 18px; }
    }
</style>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<!-- Ambient background -->
<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>
<div class="bg-orb orb-3"></div>
<canvas id="particleCanvas"></canvas>

<div class="lp-shell">

    <!-- Header -->
    <header class="lp-header">
        <div class="lp-brand-row">
            <div class="lp-logo-badge"><i class="fas fa-hard-hat"></i></div>
            <span class="lp-app-name">MINE-OPS</span>
        </div>
        <div class="lp-tagline">Mining Operations Management System</div>
        <div class="lp-prompt">Select your role to continue</div>
    </header>

    <!-- Role selector grid -->
    <div class="role-grid">

        <!-- Admin -->
        <a class="role-card" href="/admin-dashboard" data-accent="green">
            <div class="rc-icon-wrap"><i class="fas fa-display"></i></div>
            <div class="rc-tag">Admin Panel</div>
            <div class="rc-title">Manage Mine Operations</div>
            <div class="rc-desc">
                Full access to operational data, P2H records, manpower
                management and reporting dashboards.
            </div>
            <div class="rc-notice">
                <i class="fas fa-desktop" style="font-size:.65rem;"></i>
                Desktop recommended
            </div>
            <div class="rc-cta">
                Access Dashboard <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <!-- Group Leader -->
        <a class="role-card" href="/group-leader" data-accent="teal">
            <div class="rc-icon-wrap"><i class="fas fa-chart-line"></i></div>
            <div class="rc-tag">Group Leader</div>
            <div class="rc-title">Monitor Operations &amp; Team</div>
            <div class="rc-desc">
                View daily mining operations, team performance metrics
                and shift reports across all work areas.
            </div>
            <div class="rc-cta">
                View Reports <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <!-- Operator / Driver -->
        <a class="role-card" href="/operator-driver" data-accent="lime">
            <div class="rc-icon-wrap"><i class="fas fa-truck"></i></div>
            <div class="rc-tag">Operation</div>
            <div class="rc-title">Operator &amp; Driver Portal</div>
            <div class="rc-desc">
                Submit daily Pre-Start Inspections (P2H), record task
                assignments and manage operational inputs on the go.
            </div>
            <div class="rc-cta">
                Access Portal <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <!-- PLM -->
        <a class="role-card" href="/plm-record" data-accent="sky">
            <div class="rc-icon-wrap"><i class="fas fa-screwdriver-wrench"></i></div>
            <div class="rc-tag">PLM Panel</div>
            <div class="rc-title">Equipment Status &amp; Records</div>
            <div class="rc-desc">
                Update equipment operational status, report breakdowns
                and modify existing maintenance records.
            </div>
            <div class="rc-cta">
                Update Records <i class="fas fa-arrow-right"></i>
            </div>
        </a>

    </div>

    <!-- Footer -->
    <div class="lp-footer">
        &copy; <?= date('Y') ?> MINE-OPS &mdash; Beta Release v1
    </div>

</div>

<script>
(function () {
    'use strict';

    /* ── Particle canvas ───────────────────────────────────── */
    const canvas = document.getElementById('particleCanvas');
    const ctx    = canvas.getContext('2d');
    let W = 0, H = 0;

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resize, { passive: true });
    resize();

    function rand(a, b) { return a + Math.random() * (b - a); }

    const TOTAL = 45;
    const pts   = [];

    function mkPt(scattered) {
        return {
            x : rand(0, W),
            y : scattered ? rand(0, H) : rand(H + 10, H + 80),
            r : rand(.9, 2.4),
            vy: rand(-.20, -.68),
            vx: rand(-.12, .12),
            alpha: rand(.08, .32),
            decay: rand(.0002, .0005),
            hue: [145, 168, 95, 200][Math.floor(Math.random() * 4)]
        };
    }

    for (let i = 0; i < TOTAL; i++) pts.push(mkPt(true));

    function draw() {
        ctx.clearRect(0, 0, W, H);
        for (let i = 0; i < pts.length; i++) {
            const p = pts[i];
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = 'hsla(' + p.hue + ',65%,60%,' + p.alpha.toFixed(3) + ')';
            ctx.fill();
            p.x += p.vx; p.y += p.vy; p.alpha -= p.decay;
            if (p.alpha <= 0 || p.y < -10) pts[i] = mkPt(false);
        }
        requestAnimationFrame(draw);
    }
    draw();

    /* ── Card entrance stagger ─────────────────────────────── */
    const cards = document.querySelectorAll('.role-card');
    cards.forEach(function (card, i) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(18px)';
        card.style.transition = 'opacity .45s ease ' + (i * .08 + .2) + 's, transform .45s cubic-bezier(.22,.68,0,1.2) ' + (i * .08 + .2) + 's, box-shadow .22s ease, border-color .22s ease';
        /* Allow CSS hover transitions after initial animation */
        setTimeout(function () {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
            setTimeout(function () {
                card.style.transition = 'transform .22s ease, box-shadow .22s ease, border-color .22s ease';
            }, 600);
        }, 20);
    });

})();
</script>

<?= $this->endSection(); ?>
