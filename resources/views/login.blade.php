<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>STOCKWIZE — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

<div class="page">

  <div class="left-panel">
    <div class="logo-area">
      <svg viewBox="0 0 26 26" fill="none">
        <polygon points="13,3 20,7 13,11 6,7" fill="#e8ff47"/>
        <polygon points="6,7 6,15 13,19 13,11" fill="#9aaa2e"/>
        <polygon points="20,7 20,15 13,19 13,11" fill="#c8dc38"/>
      </svg>
      <div>
        <div class="brand">STOCKWIZE</div>
        <div class="tagline">Inventory Management System</div>
      </div>
    </div>

    <div class="hero-area">
      <h1 class="hero-title">
        Control your<br/>
        stock with<br/>
        <span>precision.</span>
      </h1>
      <p class="hero-desc">
        Real-time inventory tracking, smart reorder alerts, and full warehouse visibility — all in one place.
      </p>
      <div class="feature-list">
        <div class="feature-item"><div class="feature-dot"></div>Live stock level monitoring</div>
        <div class="feature-item"><div class="feature-dot"></div>Multi-category item management</div>
        <div class="feature-item"><div class="feature-dot"></div>Supplier & purchase order tracking</div>
      </div>
    </div>

    <div class="left-footer">© 2026 STOCKWIZE — All rights reserved.</div>
  </div>

  <!-- ── Right Panel ── -->
  <div class="right-panel">
    <div class="form-container">



      <!-- ── LOGIN FORM ── -->
      <div class="form-panel active" id="loginPanel">
        <div class="form-head">
          <h2>Welcome!</h2>
          <p>Sign in to access your inventory dashboard.</p>
        </div>

        <div id="loginMsg"></div>

        <!-- POST to login.php -->
        <form id="loginForm" action="/login" method="POST">
            @csrf
          <div class="field">
            <label for="loginEmail">Email Address</label>
            <div class="input-wrap">
              <input type="email" id="loginEmail" name="email" placeholder="" autocomplete="off" required/>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </div>
          </div>

          <div class="field">
            <label for="loginPassword">Password</label>
            <div class="input-wrap">
              <input type="password" id="loginPassword" name="password" class="pw-field" placeholder="" autocomplete="new-password" required/>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <button class="eye-btn" type="button" onclick="togglePw('loginPassword', this)" tabindex="-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>

          <div class="field-extras">
            <a href="/forgot-password">Forgot password?</a>
          </div>

          <button class="btn-submit" type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Sign In
          </button>
        </form>

      </div>

    </div>
  </div>
</div>
<script src="{{ asset('js/login.js') }}" defer></script>
</body>
</html>