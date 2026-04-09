/* ── Show/hide password toggle ── */
function togglePw(id, btn) {
  const input = document.getElementById(id);
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.innerHTML = isText
    ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
       </svg>`
    : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
        <line x1="1" y1="1" x2="23" y2="23"/>
       </svg>`;
}

/* ── Client-side validation ── */
document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    const pw  = document.getElementById('new_password').value;
    const cpw = document.getElementById('new_password_confirmation').value;

    if (pw.length < 8) {
      e.preventDefault();
      alert('Password must be at least 8 characters.');
      return;
    }

    if (pw !== cpw) {
      e.preventDefault();
      alert('Passwords do not match.');
    }
  });
});