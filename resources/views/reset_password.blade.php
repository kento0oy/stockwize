<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>STOCKWIZE — Reset Password</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('css/reset_password.css') }}">
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
        Set a new<br/>
        <span>password.</span>
      </h1>
      <p class="hero-desc">
        Choose a strong password that is at least 8 characters long. This link expires in 60 minutes.
      </p>
      <div class="feature-list">
        <div class="feature-item"><div class="feature-dot"></div>Minimum 8 characters</div>
        <div class="feature-item"><div class="feature-dot"></div>Stored securely with bcrypt</div>
        <div class="feature-item"><div class="feature-dot"></div>Old password is replaced immediately</div>
      </div>
    </div>

    <div class="left-footer">© 2026 STOCKWIZE — All rights reserved.</div>
  </div>

  <!-- ── Right Panel ── -->
  <div class="right-panel">
    <div class="form-container">
      <div class="form-panel active">

        <div class="form-head">
          <h2>New Password</h2>
          <p>Enter and confirm your new password below.</p>
        </div>

        {{-- Error messages --}}
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

        <form action="/reset-password" method="POST">
          @csrf

          {{-- Hidden fields --}}
          <input type="hidden" name="token" value="{{ $token }}"/>
          <input type="hidden" name="email" value="{{ $email }}"/>

          <div class="field">
            <label for="new_password">New Password</label>
            <div class="input-wrap">
              <input type="password" id="new_password" name="new_password"
                     placeholder="" autocomplete="new-password" required/>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <button class="eye-btn" type="button" onclick="togglePw('new_password', this)" tabindex="-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="field">
            <label for="new_password_confirmation">Confirm New Password</label>
            <div class="input-wrap">
              <input type="password" id="new_password_confirmation"
                     name="new_password_confirmation"
                     placeholder="" autocomplete="new-password" required/>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <button class="eye-btn" type="button" onclick="togglePw('new_password_confirmation', this)" tabindex="-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <button class="btn-submit" type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            Reset Password
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

<script src="{{ asset('js/reset_password.js') }}" defer></script>
</body>
</html>