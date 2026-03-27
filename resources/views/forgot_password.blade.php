<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>STOCKWIZE — Forgot Password</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('css/forgot_password.css') }}">
</head>
<body>

<div class="page">

  <!-- ── Left Panel ── -->
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
        Forgot your<br/>
        <span>password?</span>
      </h1>
      <p class="hero-desc">
        Enter your registered email and we'll send you a secure link to reset your password.
      </p>
      <div class="feature-list">
        <div class="feature-item"><div class="feature-dot"></div>Secure reset link via email</div>
        <div class="feature-item"><div class="feature-dot"></div>Link expires in 60 minutes</div>
        <div class="feature-item"><div class="feature-dot"></div>Password is hashed on reset</div>
      </div>
    </div>

    <div class="left-footer">© 2026 STOCKWIZE — All rights reserved.</div>
  </div>

  <!-- ── Right Panel ── -->
  <div class="right-panel">
    <div class="form-container">
      <div class="form-panel active">

        <div class="form-head">
          <h2>Reset Password</h2>
          <p>We'll email you a secure link to reset your password.</p>
        </div>

        {{-- Success message --}}
        @if(session('success'))
          <div class="msg success" style="display:flex">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('success') }}</span>
          </div>
        @endif

        {{-- Error message --}}
        @if(session('error'))
          <div class="msg error" style="display:flex">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
          </div>
        @endif

        @if($errors->any())
          <div class="msg error" style="display:flex">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        <form action="/forgot-password" method="POST">
          @csrf
          <div class="field">
            <label for="email">Email Address</label>
            <div class="input-wrap">
              <input type="email" id="email" name="email"
                     value="{{ old('email') }}"
                     placeholder="" autocomplete="off" required/>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
            </div>
          </div>

          <button class="btn-submit" type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
              <path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4 20-7z"/>
            </svg>
            Send Reset Link
          </button>
        </form>

        <div style="text-align:center; font-size:13px; color:var(--muted2); margin-top:20px;">
          Remember your password?
          <a href="/" style="color:var(--accent); text-decoration:none; font-weight:600; margin-left:4px;">Sign in →</a>
        </div>

      </div>
    </div>
  </div>

</div>
<script src="{{ asset('js/forgot_password.js') }}" defer></script>
</body>
</html>