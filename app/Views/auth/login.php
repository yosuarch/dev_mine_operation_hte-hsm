<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lang('Auth.login') ?> — MINE-OPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Design tokens (logo-derived green palette) ─────── */
        :root {
            --bg           : #091a11;
            --panel-bg     : #0d2016;
            --brand-bg     : #102519;
            --green        : #38b549;
            --green-mid    : #28a035;
            --green-dark   : #16873d;
            --teal         : #1ab5a4;
            --lime         : #7dc242;
            --green-glow   : rgba(56,181,73,.16);
            --border       : rgba(255,255,255,.07);
            --border-green : rgba(56,181,73,.15);
            --text         : #e8f5ea;
            --muted        : #4d7a57;
            --input-bg     : rgba(255,255,255,.04);
            --input-bdr    : rgba(255,255,255,.10);
            --danger       : #ef4444;
            --danger-glow  : rgba(239,68,68,.12);
            --success      : #22c55e;
            --success-glow : rgba(34,197,94,.12);
            --radius-card  : 14px;
            --radius-el    : 8px;
            --shadow       : 0 32px 80px rgba(0,0,0,.55), 0 0 0 1px var(--border);
        }

        /* ── Base ──────────────────────────────────────────── */
        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ── Animated background orbs ──────────────────────── */
        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(110px);
            pointer-events: none;
            z-index: 0;
            animation: orbDrift ease-in-out infinite;
        }
        /* Large forest-green orb — top-left */
        .orb-1 {
            width: 720px; height: 720px;
            background: radial-gradient(circle at center, #15803d 0%, transparent 70%);
            top: -260px; left: -220px; opacity: .28;
            animation-duration: 28s;
        }
        /* Teal orb — bottom-right */
        .orb-2 {
            width: 520px; height: 520px;
            background: radial-gradient(circle at center, #0d9488 0%, transparent 70%);
            bottom: -160px; right: -160px; opacity: .20;
            animation-duration: 34s; animation-direction: reverse; animation-delay: -11s;
        }
        /* Lime orb — center */
        .orb-3 {
            width: 340px; height: 340px;
            background: radial-gradient(circle at center, #3f6212 0%, transparent 70%);
            top: 42%; left: 52%; opacity: .18;
            animation-duration: 21s; animation-delay: -6s;
        }
        @keyframes orbDrift {
            0%, 100% { transform: translate(0, 0)  scale(1); }
            33%       { transform: translate(35px, -35px) scale(1.06); }
            66%       { transform: translate(-25px, 20px) scale(.96); }
        }

        /* Particle canvas */
        #particleCanvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        /* ── Auth shell ────────────────────────────────────── */
        .auth-shell {
            position: relative;
            z-index: 1;
            display: flex;
            width: 900px;
            max-width: calc(100vw - 32px);
            border-radius: var(--radius-card);
            overflow: hidden;
            box-shadow: var(--shadow);
            animation: shellIn .55s cubic-bezier(.22,.68,0,1.2) both;
        }
        @keyframes shellIn {
            from { opacity: 0; transform: translateY(28px) scale(.96); }
            to   { opacity: 1; transform: translateY(0)    scale(1); }
        }

        /* ── Brand panel ───────────────────────────────────── */
        .auth-brand {
            flex: 1;
            background: var(--brand-bg);
            padding: 56px 44px;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        /* Dot-grid overlay */
        .auth-brand::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle, rgba(56,181,73,.07) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        /* Accent edge line */
        .auth-brand::before {
            content: '';
            position: absolute;
            top: 15%; bottom: 15%; right: -1px;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(56,181,73,.45), transparent);
        }

        .brand-logo {
            width: 60px; height: 60px;
            border-radius: 14px;
            background: var(--green-glow);
            border: 1px solid rgba(56,181,73,.28);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px;
            font-size: 1.6rem;
            color: var(--green);
            box-shadow: 0 0 30px rgba(56,181,73,.2);
        }
        .brand-name {
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: .05em;
            color: #fff;
            margin-bottom: 6px;
        }
        .brand-tagline {
            font-size: .875rem;
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 40px;
            max-width: 280px;
        }

        .brand-features { display: flex; flex-direction: column; gap: 14px; }
        .feature-item   { display: flex; align-items: center; gap: 14px; }
        .fi-icon {
            width: 36px; height: 36px; flex-shrink: 0;
            border-radius: 8px;
            background: var(--green-glow);
            border: 1px solid rgba(56,181,73,.22);
            display: flex; align-items: center; justify-content: center;
            color: var(--green);
            font-size: .8rem;
        }
        .fi-icon.teal  { background: rgba(26,181,164,.12); border-color: rgba(26,181,164,.22); color: var(--teal); }
        .fi-icon.lime  { background: rgba(125,194,66,.12);  border-color: rgba(125,194,66,.22);  color: var(--lime); }
        .feature-item span { font-size: .83rem; color: #6ea87a; line-height: 1.4; }

        .brand-version {
            margin-top: 40px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: rgba(56,181,73,.1);
            border: 1px solid rgba(56,181,73,.22);
            border-radius: 20px;
            font-size: .72rem;
            color: var(--green);
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        /* ── Form panel ────────────────────────────────────── */
        .auth-form {
            width: 390px;
            flex-shrink: 0;
            background: var(--panel-bg);
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Mobile brand */
        .mobile-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .mobile-brand .m-logo {
            width: 52px; height: 52px;
            border-radius: 12px;
            background: var(--green-glow);
            border: 1px solid rgba(56,181,73,.28);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px;
            font-size: 1.4rem;
            color: var(--green);
            box-shadow: 0 0 22px rgba(56,181,73,.18);
        }
        .mobile-brand .m-name { font-size: 1.3rem; font-weight: 800; letter-spacing: .05em; color: #fff; }
        .mobile-brand .m-sub  { font-size: .78rem; color: var(--muted); margin-top: 2px; }

        /* Form header */
        .form-header { margin-bottom: 28px; }
        .form-header h2 { font-size: 1.3rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
        .form-header p  { font-size: .82rem; color: var(--muted); }

        /* ── Alerts ────────────────────────────────────────── */
        .auth-alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 10px 14px;
            border-radius: var(--radius-el);
            font-size: .83rem;
            margin-bottom: 22px;
            border: 1px solid;
            line-height: 1.5;
            animation: alertIn .3s ease both;
        }
        @keyframes alertIn { from { opacity: 0; transform: translateY(-6px); } }
        .auth-alert.err { background: var(--danger-glow);  border-color: rgba(239,68,68,.28);  color: #fca5a5; }
        .auth-alert.ok  { background: var(--success-glow); border-color: rgba(34,197,94,.28);  color: #86efac; }
        .auth-alert i   { margin-top: 2px; flex-shrink: 0; }

        /* ── Field group ───────────────────────────────────── */
        .field {
            margin-bottom: 18px;
            transition: transform .15s ease;
        }
        .field:focus-within { transform: translateY(-1px); }
        .field label {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 7px;
        }

        .auth-input {
            width: 100%;
            padding: 11px 15px;
            background: var(--input-bg);
            border: 1px solid var(--input-bdr);
            border-radius: var(--radius-el);
            color: var(--text);
            font-size: .9rem;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .auth-input:hover  { border-color: rgba(255,255,255,.18); }
        .auth-input:focus  {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(56,181,73,.16);
            background: rgba(56,181,73,.04);
        }
        .auth-input::placeholder { color: #1a3a22; }
        .auth-input:-webkit-autofill,
        .auth-input:-webkit-autofill:hover,
        .auth-input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 40px var(--panel-bg) inset !important;
            -webkit-text-fill-color: var(--text) !important;
            transition: background-color 9999s ease;
        }

        /* Password wrapper */
        .pw-wrap { position: relative; }
        .pw-wrap .auth-input { padding-right: 46px; }
        .pw-eye {
            position: absolute; right: 13px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--muted); cursor: pointer;
            font-size: .9rem; padding: 4px; line-height: 1;
            transition: color .2s;
        }
        .pw-eye:hover { color: var(--green); }

        /* ── Remember row ──────────────────────────────────── */
        .remember-row {
            display: flex; align-items: center; gap: 8px;
            margin: 4px 0 26px;
        }
        .remember-row input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: var(--green); cursor: pointer; flex-shrink: 0;
        }
        .remember-row label { font-size: .83rem; color: var(--muted); cursor: pointer; user-select: none; }

        /* ── Submit button ─────────────────────────────────── */
        .btn-signin {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
            color: #fff;
            border: none;
            border-radius: var(--radius-el);
            font-size: .9rem; font-weight: 600; letter-spacing: .03em;
            cursor: pointer; position: relative; overflow: hidden;
            transition: opacity .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 18px rgba(56,181,73,.32);
        }
        .btn-signin:hover:not(:disabled) {
            opacity: .92;
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(56,181,73,.42);
        }
        .btn-signin:active:not(:disabled) { transform: translateY(0); }
        .btn-signin:disabled { cursor: not-allowed; }

        .btn-spinner {
            display: none;
            width: 17px; height: 17px;
            border: 2px solid rgba(255,255,255,.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .65s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-signin.is-loading .btn-label   { display: none; }
        .btn-signin.is-loading .btn-spinner { display: block; }

        .ripple-el {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,.22);
            transform: scale(0);
            animation: ripple .5s linear;
            pointer-events: none;
        }
        @keyframes ripple { to { transform: scale(4); opacity: 0; } }

        /* ── Footer links ──────────────────────────────────── */
        .form-footer { margin-top: 20px; text-align: center; font-size: .82rem; color: var(--muted); }
        .form-footer a { color: var(--green); text-decoration: none; transition: opacity .2s; }
        .form-footer a:hover { opacity: .8; text-decoration: underline; }

        /* ── Responsive ────────────────────────────────────── */
        @media (max-width: 991px) {
            body { align-items: flex-start; padding: 24px 0; min-height: 100svh; }
            .auth-shell { width: 100%; max-width: 440px; margin: auto; }
            .auth-form  { width: 100%; padding: 40px 32px; }
        }
        @media (max-width: 400px) {
            .auth-form { padding: 36px 22px; }
        }
    </style>
</head>

<body>

<!-- ── Ambient background ──────────────────────────────────── -->
<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>
<div class="bg-orb orb-3"></div>
<canvas id="particleCanvas"></canvas>

<!-- ── Auth card ──────────────────────────────────────────── -->
<div class="auth-shell">

    <!-- Left: branding (desktop only) -->
    <div class="auth-brand d-none d-lg-flex">
        <div class="brand-logo"><i class="fas fa-hard-hat"></i></div>
        <div class="brand-name">MINE-OPS</div>
        <p class="brand-tagline">
            Mining Operations Management System.<br>
            Operational intelligence built for safety-first environments.
        </p>

        <div class="brand-features">
            <div class="feature-item">
                <div class="fi-icon"><i class="fas fa-clipboard-check"></i></div>
                <span>Pre-Start Inspection (P2H) Management</span>
            </div>
            <div class="feature-item">
                <div class="fi-icon teal"><i class="fas fa-users"></i></div>
                <span>Manpower &amp; Operator Coordination</span>
            </div>
            <div class="feature-item">
                <div class="fi-icon lime"><i class="fas fa-chart-bar"></i></div>
                <span>Real-Time Operational Reporting</span>
            </div>
            <div class="feature-item">
                <div class="fi-icon teal"><i class="fas fa-triangle-exclamation"></i></div>
                <span>Hazard Tracking &amp; Auto Notification</span>
            </div>
        </div>

        <div class="brand-version">
            <i class="fas fa-circle" style="font-size:.4rem;"></i>
            Beta Release — v1
        </div>
    </div>

    <!-- Right: login form -->
    <div class="auth-form">

        <div class="mobile-brand d-lg-none">
            <div class="m-logo"><i class="fas fa-hard-hat"></i></div>
            <div class="m-name">MINE-OPS</div>
            <div class="m-sub">Mining Operations Management</div>
        </div>

        <div class="form-header">
            <h2>Welcome back</h2>
            <p>Sign in to your account to continue</p>
        </div>

        <!-- Session alerts -->
        <?php if (session('error') !== null) : ?>
            <div class="auth-alert err" role="alert">
                <i class="fas fa-circle-exclamation"></i>
                <div><?= esc(session('error')) ?></div>
            </div>
        <?php elseif (session('errors') !== null) : ?>
            <div class="auth-alert err" role="alert">
                <i class="fas fa-circle-exclamation"></i>
                <div>
                    <?php if (is_array(session('errors'))) : ?>
                        <?php foreach (session('errors') as $error) : ?><?= esc($error) ?><br><?php endforeach ?>
                    <?php else : ?>
                        <?= esc(session('errors')) ?>
                    <?php endif ?>
                </div>
            </div>
        <?php endif ?>

        <?php if (session('message') !== null) : ?>
            <div class="auth-alert ok" role="alert">
                <i class="fas fa-circle-check"></i>
                <div><?= esc(session('message')) ?></div>
            </div>
        <?php endif ?>

        <!-- Login form -->
        <form action="<?= url_to('login') ?>" method="post" id="loginForm" novalidate>
            <?= csrf_field() ?>

            <div class="field">
                <label for="emailInput">Email address</label>
                <input type="email"
                       class="auth-input"
                       id="emailInput"
                       name="email"
                       inputmode="email"
                       autocomplete="email"
                       placeholder="you@company.com"
                       value="<?= old('email') ?>"
                       required>
            </div>

            <div class="field">
                <label for="passwordInput">Password</label>
                <div class="pw-wrap">
                    <input type="password"
                           class="auth-input"
                           id="passwordInput"
                           name="password"
                           autocomplete="current-password"
                           placeholder="••••••••"
                           required>
                    <button type="button" class="pw-eye" id="pwToggle" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>

            <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
                <div class="remember-row">
                    <input type="checkbox"
                           name="remember"
                           id="rememberMe"
                           <?php if (old('remember')): ?>checked<?php endif ?>>
                    <label for="rememberMe"><?= lang('Auth.rememberMe') ?></label>
                </div>
            <?php else: ?>
                <div style="margin-bottom:26px;"></div>
            <?php endif; ?>

            <button type="submit" class="btn-signin" id="submitBtn">
                <span class="btn-label">Sign In &nbsp;<i class="fas fa-arrow-right" style="font-size:.8em;"></i></span>
                <div class="btn-spinner"></div>
            </button>

            <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
                <div class="form-footer">
                    <?= lang('Auth.forgotPassword') ?>
                    <a href="<?= url_to('magic-link') ?>"><?= lang('Auth.useMagicLink') ?></a>
                </div>
            <?php endif ?>

            <?php if (setting('Auth.allowRegistration')) : ?>
                <div class="form-footer" style="margin-top:10px;">
                    <?= lang('Auth.needAccount') ?>
                    <a href="<?= url_to('register') ?>"><?= lang('Auth.register') ?></a>
                </div>
            <?php endif ?>

        </form>
    </div>
</div>

<script>
(function () {
    'use strict';

    /* ── Particle canvas (green-tinted) ────────────────────── */
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

    const TOTAL = 50;
    const pts   = [];

    function mkPt(scattered) {
        return {
            x     : rand(0, W),
            y     : scattered ? rand(0, H) : rand(H + 10, H + 80),
            r     : rand(1.0, 2.6),
            vy    : rand(-.22, -.75),
            vx    : rand(-.15, .15),
            alpha : rand(.10, .38),
            decay : rand(.0002, .0006)
        };
    }

    for (let i = 0; i < TOTAL; i++) pts.push(mkPt(true));

    function draw() {
        ctx.clearRect(0, 0, W, H);
        for (let i = 0; i < pts.length; i++) {
            const p = pts[i];
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            /* Alternate green / teal / lime particles for variety */
            const hue = [145, 168, 95][i % 3];
            ctx.fillStyle = 'hsla(' + hue + ',70%,60%,' + p.alpha.toFixed(3) + ')';
            ctx.fill();

            p.x += p.vx;
            p.y += p.vy;
            p.alpha -= p.decay;

            if (p.alpha <= 0 || p.y < -10) pts[i] = mkPt(false);
        }
        requestAnimationFrame(draw);
    }
    draw();

    /* ── Password toggle ────────────────────────────────────── */
    const pwInput  = document.getElementById('passwordInput');
    const pwToggle = document.getElementById('pwToggle');
    const pwIcon   = document.getElementById('pwIcon');

    pwToggle.addEventListener('click', function () {
        const show   = pwInput.type === 'password';
        pwInput.type = show ? 'text' : 'password';
        pwIcon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        pwInput.focus();
    });

    /* ── Submit: ripple + loading ───────────────────────────── */
    const form      = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');

    submitBtn.addEventListener('pointerdown', function (e) {
        const rect   = submitBtn.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height);
        const ripple = document.createElement('span');
        ripple.className     = 'ripple-el';
        ripple.style.cssText =
            'width:'  + size + 'px;' +
            'height:' + size + 'px;' +
            'left:'   + (e.clientX - rect.left - size / 2) + 'px;' +
            'top:'    + (e.clientY - rect.top  - size / 2) + 'px;';
        submitBtn.appendChild(ripple);
        setTimeout(function () { ripple.remove(); }, 550);
    });

    form.addEventListener('submit', function () {
        submitBtn.classList.add('is-loading');
        submitBtn.disabled = true;
    });

    /* ── Input lift micro-animation ─────────────────────────── */
    document.querySelectorAll('.auth-input').forEach(function (el) {
        const wrap = el.closest('.field');
        if (!wrap) return;
        el.addEventListener('focus', function () { wrap.style.transform = 'translateY(-2px)'; });
        el.addEventListener('blur',  function () { wrap.style.transform = ''; });
    });

})();
</script>

</body>
</html>
