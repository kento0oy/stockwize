/* ── Show/hide inline message ── */
function showMsg(id, type, text) {
  const el = document.getElementById(id);
  if (!el) return;
  const icon = type === 'error'
    ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`
    : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>`;
  el.className = `msg ${type}`;
  el.innerHTML = icon + `<span>${text}</span>`;
}