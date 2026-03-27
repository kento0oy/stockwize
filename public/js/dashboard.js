/* ═══ CONSTANTS & DATA ═══ */
const ICONS = { Laptop: '💻', Monitor: '🖥️', CPU: '🔲', GPU: '🎮', 'Mother Board': '🖨️', RAM: '🧩', HDD: '💾', SSD: '⚡', Keyboard: '⌨️', Mouse: '🖱️', Headset: '🎧' };
const IBG   = { Laptop: '#0d1e2e', Monitor: '#1e1e0d', CPU: '#0d1e18', GPU: '#2a1a0d', 'Mother Board': '#2a0d12', RAM: '#1a0d2a', HDD: '#1e1a0d', SSD: '#0d1e14', Keyboard: '#2a0d1a', Mouse: '#0d1222', Headset: '#2a1a12' };
const AVC   = ['#e8ff47', '#4db8ff', '#3dffa0', '#ff6b35', '#ffcc00', '#ff4d6d'];

let prods       = [];
let activityLog = [];
let nextPid = 1, editPid = null;
let pf = 'all', ps = null, psd = 1, pp = 1;
const PG = 8;

/* ═══ UTILS ═══ */
const $ = id => document.getElementById(id);
const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const fmt = n => '₱' + n.toLocaleString('en-PH');

function getStatus(p) {
  return p.quantity === 0 ? 'Out of Stock' : p.quantity <= p.reorder ? 'Low Stock' : 'In Stock';
}

function sBadge(s) {
  const m = { 'In Stock': 'bs', 'Low Stock': 'bw', 'Out of Stock': 'bd' };
  return `<span class="bdg ${m[s]}">${s}</span>`;
}

/* ═══ NAVIGATION ═══ */
function go(page, el) {
  document.querySelectorAll('.n-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  $('pg-' + page).classList.add('active');
  $('tbTitle').textContent = { dashboard: 'Dashboard', products: 'Products', reports: 'Reports' }[page];

  const ta = $('tbAct');
  ta.innerHTML = '';
  if (page === 'dashboard') ta.innerHTML = `<button class="btn btn-primary" onclick="exportCSV()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Export CSV</button>`;
  if (page === 'products')  ta.innerHTML = `<button class="btn btn-primary" onclick="openProd()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>Add Product</button>`;

  if (page === 'products') renderProds();
  if (page === 'reports')  renderRpt();
}

/* ═══ KPI CARDS ═══ */
function updateKPIs() {
  const total = prods.length;
  const v     = prods.reduce((s, p) => s + p.price * p.quantity, 0);
  const low   = prods.filter(p => getStatus(p) === 'Low Stock').length;
  const out   = prods.filter(p => getStatus(p) === 'Out of Stock').length;

  $('kTotal').textContent = total;
  $('kValue').textContent = v >= 1000000 ? '₱' + (v / 1000000).toFixed(1) + 'M' : v >= 1000 ? '₱' + (v / 1000).toFixed(0) + 'K' : '₱' + v;
  $('kLow').textContent   = low;
  $('kOut').textContent   = out;

  $('kTotalMeta').innerHTML = total === 0 ? `<span style="color:var(--muted)">No products yet</span>`       : `<span style="color:var(--muted2)">${total} item${total !== 1 ? 's' : ''} tracked</span>`;
  $('kValueMeta').innerHTML = v === 0     ? `<span style="color:var(--muted)">Add products to track</span>` : `<span style="color:var(--muted2)">Across ${total} product${total !== 1 ? 's' : ''}</span>`;
  $('kLowMeta').innerHTML   = low === 0   ? `<span style="color:var(--muted)">No issues</span>`             : `<span class="dn">${low} need${low === 1 ? 's' : ''} reordering</span>`;
  $('kOutMeta').innerHTML   = out === 0   ? `<span style="color:var(--muted)">All clear</span>`             : `<span class="dn">${out} item${out !== 1 ? 's' : ''} need restocking</span>`;
}

/* ═══ CHARTS ═══ */
let chartInstances = {};

function initCharts() {
  Chart.defaults.color       = '#4a5060';
  Chart.defaults.font.family = "'Plus Jakarta Sans',sans-serif";
  Chart.defaults.font.size   = 11;

  const tt = { backgroundColor: '#1c1f23', borderColor: '#262a30', borderWidth: 1, titleColor: '#f0f2f5', bodyColor: '#7a8090', padding: 10, cornerRadius: 6 };
  const xygrid = {
    x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#4a5060' } },
    y: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#4a5060' }, beginAtZero: true }
  };

  const cats    = ['Laptop', 'Monitor', 'CPU', 'GPU', 'Mother Board', 'RAM', 'HDD', 'SSD', 'Keyboard', 'Mouse', 'Headset'];
  const ccols   = ['#4db8ff', '#e8ff47', '#3dffa0', '#ff6b35', '#ff4d6d', '#b388ff', '#ffcc00', '#69f0ae', '#f06292', '#82b1ff', '#ff8a65'];
  const ccounts = cats.map(c => prods.filter(p => p.category === c).length);
  const cvals   = cats.map(c => prods.filter(p => p.category === c).reduce((s, p) => s + p.price * p.quantity, 0) / 1000);
  const tot     = ccounts.reduce((a, b) => a + b, 0);
  const ins     = prods.filter(p => getStatus(p) === 'In Stock').length;
  const low     = prods.filter(p => getStatus(p) === 'Low Stock').length;
  const out     = prods.filter(p => getStatus(p) === 'Out of Stock').length;

  Object.values(chartInstances).forEach(c => c.destroy());
  chartInstances = {};

  chartInstances.stock = new Chart($('cStock'), {
    type: 'bar',
    data: { labels: cats, datasets: [
      { label: 'Item Count', data: ccounts, backgroundColor: 'rgba(232,255,71,.85)', borderRadius: 4, borderSkipped: false, barPercentage: .5, categoryPercentage: .7 },
      { label: 'Value (₱K)', data: cvals,   backgroundColor: 'rgba(77,184,255,.7)',  borderRadius: 4, borderSkipped: false, barPercentage: .5, categoryPercentage: .7 }
    ]},
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { top: 0, bottom: 0, left: 0, right: 0 } },
      plugins: { legend: { display: false }, tooltip: tt },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#4a5060' } },
        y: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#4a5060' }, beginAtZero: true, grace: 0 }
      }
    }
  });

  chartInstances.donut = new Chart($('cDonut'), {
    type: 'doughnut',
    data: { labels: cats, datasets: [{ data: ccounts, backgroundColor: ccols, borderColor: '#141618', borderWidth: 3 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false }, tooltip: tt } }
  });
  const leg = $('dLegend');
  leg.innerHTML = '';
  cats.forEach((c, i) => {
    const p = tot ? Math.round(ccounts[i] / tot * 100) : 0;
    leg.innerHTML += `<div class="li"><span class="ld" style="background:${ccols[i]}"></span><span class="ll">${c}</span><span class="lv">${ccounts[i]}</span><span class="lp">${p}%</span></div>`;
  });

  chartInstances.rev = new Chart($('cRev'), {
    type: 'bar',
    data: { labels: cats, datasets: [{ label: 'Value (₱K)', data: cvals, backgroundColor: ccols, borderRadius: 4, borderSkipped: false, barPercentage: .6 }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      layout: { padding: 0 },
      plugins: { legend: { display: false }, tooltip: { ...tt, callbacks: { label: c => `₱${c.parsed.y.toFixed(1)}K` } } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#4a5060' } },
        y: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#4a5060' }, beginAtZero: true, grace: 0 }
      }
    }
  });

  chartInstances.cat = new Chart($('cCat'), {
    type: 'bar',
    data: { labels: cats, datasets: [{ data: cvals, backgroundColor: ccols, borderRadius: 4, borderSkipped: false, barPercentage: .6 }] },
    options: {
      responsive: true, maintainAspectRatio: false, indexAxis: 'y',
      layout: { padding: 0 },
      plugins: { legend: { display: false }, tooltip: { ...tt, callbacks: { label: c => `₱${c.parsed.x.toFixed(0)}K` } } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#4a5060' }, grace: 0 },
        y: { grid: { display: false }, ticks: { color: '#7a8090' } }
      }
    }
  });

  chartInstances.health = new Chart($('cHealth'), {
    type: 'doughnut',
    data: { labels: ['In Stock', 'Low Stock', 'Out of Stock'], datasets: [{ data: [ins, low, out], backgroundColor: ['#3dffa0', '#ffcc00', '#ff4d6d'], borderColor: '#141618', borderWidth: 3 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { color: '#7a8090', padding: 12, boxWidth: 10, font: { size: 11 } } }, tooltip: tt } }
  });
}

/* ═══ ACTIVITY LOG ═══ */
function logActivity(color, msg) {
  const now = new Date();
  const ts  = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  activityLog.unshift({ c: color, t: msg, ts });
  if (activityLog.length > 20) activityLog.pop();
  renderActivity();
}

function renderActivity() {
  const el = $('actFeed');
  if (!activityLog.length) {
    el.innerHTML = `<div style="text-align:center;padding:30px;color:var(--muted);font-size:12px">No activity yet</div>`;
    return;
  }
  el.innerHTML = activityLog.map(a =>
    `<div style="display:flex;align-items:flex-start;gap:12px;padding:11px 18px;border-bottom:1px solid rgba(255,255,255,.03)">
      <div style="width:7px;height:7px;border-radius:50%;background:${a.c};margin-top:5px;flex-shrink:0"></div>
      <div style="flex:1;font-size:12px;color:var(--muted2);line-height:1.5">${a.t}</div>
      <div style="font-size:10px;color:var(--muted);white-space:nowrap;margin-top:2px">${a.ts}</div>
    </div>`
  ).join('');
}

/* ═══ PRODUCTS ═══ */
function setPF(f, btn) {
  pf = f; pp = 1;
  document.querySelectorAll('#pg-products .fbtn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderProds();
}

function sortP(k) {
  ps === k ? psd *= -1 : (ps = k, psd = 1);
  renderProds();
}

function filteredProds() {
  let a = [...prods];
  const q = ($('pSearch')?.value || '').toLowerCase();
  if (pf !== 'all') a = a.filter(p => getStatus(p) === pf);
  if (q) a = a.filter(p => [p.name, p.sku, p.category, p.supplier].join(' ').toLowerCase().includes(q));
  if (ps) a.sort((a, b) => {
    let va = a[ps], vb = b[ps];
    if (ps === 'status') { va = getStatus(a); vb = getStatus(b); }
    return (typeof va === 'string' ? va.localeCompare(vb) : va - vb) * psd;
  });
  return a;
}

function renderProds() {
  const fp = filteredProds(), tot = fp.length, pgs = Math.max(1, Math.ceil(tot / PG));
  if (pp > pgs) pp = pgs;
  const sl = fp.slice((pp - 1) * PG, pp * PG);

  $('pBody').innerHTML = sl.map(p => {
    const s   = getStatus(p);
    const pct = Math.min(Math.round(p.quantity / Math.max(p.quantity, p.reorder * 3, 1) * 100), 100);
    const bc  = p.quantity === 0 ? 'var(--danger)' : p.quantity <= p.reorder ? 'var(--warning)' : 'var(--success)';
    const thumb = p.image
      ? `<img src="${p.image}" style="width:34px;height:34px;object-fit:cover;border-radius:var(--r);flex-shrink:0;" />`
      : `<div class="ie" style="background:${IBG[p.category]}">${ICONS[p.category] || '📦'}</div>`;

    return `<tr>
      <td><div class="ir">${thumb}<div><div class="in">${esc(p.name)}</div><div class="is">${esc(p.sku)}</div></div></div></td>
      <td><span class="bdg bi">${esc(p.category)}</span></td>
      <td><div class="mb"><span style="font-weight:600;min-width:22px;color:var(--text)">${p.quantity}</span><div class="mbt"><div class="mbf" style="width:${pct}%;background:${bc}"></div></div></div></td>
      <td style="color:var(--text);font-weight:600">${fmt(p.price)}</td>
      <td style="color:var(--muted2)">${fmt(p.price * p.quantity)}</td>
      <td>${sBadge(s)}</td>
      <td style="color:var(--muted2);font-size:11px">${esc(p.supplier)}</td>
      <td><div class="abtns">
        <button class="abtn" onclick="openProd(${p.id})" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="abtn del" onclick="confirmDel('prod',${p.id})" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg></button>
      </div></td>
    </tr>`;
  }).join('') || `<tr><td colspan="8"><div class="empty"><p>No products found</p></div></td></tr>`;

  $('pPgInfo').textContent = `Showing ${tot === 0 ? 0 : (pp - 1) * PG + 1}–${Math.min(pp * PG, tot)} of ${tot}`;
  renderPg('pPgBtns', pp, pgs, p => { pp = p; renderProds(); });
}

function openProd(id = null) {
  editPid = id;
  $('moProdT').textContent = id ? 'Edit Product' : 'Add Product';
  const p = id ? prods.find(x => x.id === id) : null;
  $('pN').value  = p?.name     || '';
  $('pSk').value = p?.sku      || '';
  $('pCa').value = p?.category || 'Laptop';
  $('pQ').value  = p?.quantity  ?? '';
  $('pPr').value = p?.price     ?? '';
  $('pRe').value = p?.reorder   ?? '';
  $('pSu').value = p?.supplier || '';
  $('pNo').value = p?.notes    || '';

  if ($('pImgInput'))  $('pImgInput').value = '';
  if ($('imgDrop'))    $('imgDrop').style.display = 'block';
  if ($('imgPreview')) $('imgPreview').style.display = 'none';

  if (p?.image && $('imgPreviewImg') && $('imgDrop') && $('imgPreview')) {
    $('imgPreviewImg').src = p.image;
    $('imgDrop').style.display = 'none';
    $('imgPreview').style.display = 'block';
  }

  $('moProd').classList.add('open');
}

function saveProd() {
  const name = $('pN').value.trim(), sku = $('pSk').value.trim();
  if (!name || !sku) { toast('Name and SKU are required', 'err'); return; }

  const form = new FormData();
  form.append('name',     name);
  form.append('sku',      sku);
  form.append('category', $('pCa').value);
  form.append('quantity', $('pQ').value  || 0);
  form.append('price',    $('pPr').value || 0);
  form.append('reorder',  $('pRe').value || 5);
  form.append('supplier', $('pSu').value.trim());
  form.append('notes',    $('pNo').value.trim());
  form.append('_token',   document.querySelector('meta[name="csrf-token"]').content);

  const imgFile = $('pImgInput') ? $('pImgInput').files[0] : null;
  if (imgFile) form.append('image', imgFile);
  if (editPid) form.append('_method', 'PUT');

  const url = editPid ? `/products/${editPid}` : '/products';

  fetch(url, { method: 'POST', body: form })
    .then(r => r.json())
    .then(data => {
      if (!data.success) { toast('Something went wrong', 'err'); return; }
      if (editPid) {
        prods = prods.map(p => p.id === editPid
          ? { ...p, name, sku, category: $('pCa').value, quantity: parseInt($('pQ').value)||0, price: parseFloat($('pPr').value)||0, reorder: parseInt($('pRe').value)||5, supplier: $('pSu').value.trim(), notes: $('pNo').value.trim(), image: data.image || p.image }
          : p
        );
        toast('Product updated', 'ok');
        logActivity('var(--info)', `<strong>${esc(name)}</strong> — product updated`);
      } else {
        prods.push({ id: data.id, name, sku, category: $('pCa').value, quantity: parseInt($('pQ').value)||0, price: parseFloat($('pPr').value)||0, reorder: parseInt($('pRe').value)||5, supplier: $('pSu').value.trim(), notes: $('pNo').value.trim(), image: data.image || null });
        toast('Product added', 'ok');
        logActivity('var(--success)', `<strong>${esc(name)}</strong> — new product added`);
      }
      closeMo('moProd'); renderProds(); updateKPIs(); renderRpt(); initCharts();
    })
    .catch(() => toast('Upload failed', 'err'));
}

/* ═══ IMAGE UPLOAD HELPERS ═══ */
function triggerImgUpload() { $('pImgInput').click(); }

function onImgSelected(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    $('imgPreviewImg').src = e.target.result;
    $('imgDrop').style.display = 'none';
    $('imgPreview').style.display = 'block';
  };
  reader.readAsDataURL(input.files[0]);
}

function removeImg() {
  $('pImgInput').value = '';
  $('imgPreviewImg').src = '';
  $('imgPreview').style.display = 'none';
  $('imgDrop').style.display = 'block';
}

/* ═══ REPORTS ═══ */
function doReport(name) { toast(`Generating ${name}…`, 'ok'); }

function renderRpt() {
  $('rBody').innerHTML = prods.map(p =>
    `<tr>
      <td style="font-weight:600;color:var(--text)">${esc(p.name)}</td>
      <td style="color:var(--muted2);font-size:11px">${esc(p.sku)}</td>
      <td><span class="bdg bi">${esc(p.category)}</span></td>
      <td style="font-weight:600;color:var(--text)">${p.quantity}</td>
      <td>${fmt(p.price)}</td>
      <td style="font-weight:600">${fmt(p.price * p.quantity)}</td>
      <td>${sBadge(getStatus(p))}</td>
      <td style="color:var(--muted2);font-size:11px">${esc(p.supplier)}</td>
    </tr>`
  ).join('');
}

function exportCSV() {
  const h    = ['Name', 'SKU', 'Category', 'Quantity', 'Unit Price', 'Total Value', 'Status', 'Supplier'];
  const rows = prods.map(p => [`"${p.name}"`, p.sku, p.category, p.quantity, p.price, p.price * p.quantity, getStatus(p), `"${p.supplier}"`]);
  const csv  = [h, ...rows].map(r => r.join(',')).join('\n');
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
  a.download = `stockwize_${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  toast('CSV exported successfully', 'ok');
}

/* ═══ DELETE ═══ */
let dType = null, dId = null;

function confirmDel(type, id) {
  dType = type; dId = id;
  const nm = prods.find(p => p.id === id)?.name || 'Product';
  $('delMsg').textContent = `Delete "${nm}"? This cannot be undone.`;
  $('delOk').onclick = execDel;
  $('moDel').classList.add('open');
}

function execDel() {
  if (dType === 'prod') {
    const nm = prods.find(p => p.id === dId)?.name || 'Product';
    fetch(`/products/${dId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json',
      }
    })
    .then(r => r.json())
    .then(() => {
      prods = prods.filter(p => p.id !== dId);
      renderProds(); updateKPIs(); renderRpt(); initCharts();
      toast('Product deleted', 'ok');
      logActivity('var(--danger)', `<strong>${esc(nm)}</strong> — product deleted`);
    });
  }
  closeMo('moDel');
}

/* ═══ MODALS ═══ */
function closeMo(id) { $(id).classList.remove('open'); }

['moProd', 'moDel', 'moChPw'].forEach(id =>
  $(id).addEventListener('click', e => { if (e.target === e.currentTarget) closeMo(id); })
);

/* ═══ PAGINATION ═══ */
function renderPg(cid, cur, tot, cb) {
  const el = $(cid);
  if (!el) return;
  const pages = [];
  if (tot <= 7) for (let i = 1; i <= tot; i++) pages.push(i);
  else if (cur <= 4)       pages.push(1, 2, 3, 4, 5, '…', tot);
  else if (cur >= tot - 3) pages.push(1, '…', tot - 4, tot - 3, tot - 2, tot - 1, tot);
  else                     pages.push(1, '…', cur - 1, cur, cur + 1, '…', tot);
  const fn = cb.toString();
  el.innerHTML =
    `<button class="pb" onclick="(${fn})(${cur - 1})" ${cur === 1 ? 'disabled style="opacity:.3;cursor:default"' : ''}><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg></button>` +
    pages.map(p => p === '…' ? `<button class="pb" style="cursor:default">…</button>` : `<button class="pb${p === cur ? ' active' : ''}" onclick="(${fn})(${p})">${p}</button>`).join('') +
    `<button class="pb" onclick="(${fn})(${cur + 1})" ${cur === tot ? 'disabled style="opacity:.3;cursor:default"' : ''}><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></button>`;
}

/* ═══ TOAST ═══ */
function toast(msg, type = 'ok') {
  const icons = {
    ok:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>`,
    err: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`
  };
  const t = document.createElement('div');
  t.className = `tk ${type}`;
  t.innerHTML = icons[type] + `<span>${msg}</span>`;
  $('ta').appendChild(t);
  setTimeout(() => t.remove(), 3000);
}

/* ═══ LOAD PRODUCTS FROM DATABASE ═══ */
function loadProducts() {
  fetch('/products')
    .then(r => r.json())
    .then(data => {
      prods = data.map(p => ({
        ...p,
        quantity: parseInt(p.quantity)  || 0,
        price:    parseFloat(p.price)   || 0,
        reorder:  parseInt(p.reorder)   || 5,
        image:    p.image ? `/storage/${p.image}` : null,
      }));
      renderProds();
      updateKPIs();
      renderRpt();
      initCharts();
    })
    .catch(() => toast('Failed to load products', 'err'));
}

/* ═══ CHANGE PASSWORD ═══ */
function toggleChPw(id, btn) {
  const input = document.getElementById(id);
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.innerHTML = isText
    ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
    : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
}

function openChangePw() {
  $('chPwCurrent').value = '';
  $('chPwNew').value     = '';
  $('chPwConfirm').value = '';
  $('chPwMsg').innerHTML = '';
  $('moChPw').classList.add('open');
}

function submitChangePw() {
  const current = $('chPwCurrent').value;
  const newPw   = $('chPwNew').value;
  const confirm = $('chPwConfirm').value;

  if (!current || !newPw || !confirm) {
    showChPwMsg('error', 'All fields are required.'); return;
  }
  if (newPw.length < 8) {
    showChPwMsg('error', 'New password must be at least 8 characters.'); return;
  }
  if (newPw !== confirm) {
    showChPwMsg('error', 'New passwords do not match.'); return;
  }

  fetch('/change-password', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ current_password: current, new_password: newPw, new_password_confirmation: confirm }),
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showChPwMsg('success', 'Password updated successfully!');
      setTimeout(() => closeMo('moChPw'), 1500);
      toast('Password changed', 'ok');
    } else {
      showChPwMsg('error', data.message || 'Current password is incorrect.');
    }
  })
  .catch(() => showChPwMsg('error', 'Something went wrong. Please try again.'));
}

function showChPwMsg(type, text) {
  const colors = { error: '#ff4d6d', success: '#3dffa0' };
  const bg     = { error: 'rgba(255,77,109,0.1)', success: 'rgba(61,255,160,0.08)' };
  const border = { error: 'rgba(255,77,109,0.3)', success: 'rgba(61,255,160,0.25)' };
  $('chPwMsg').innerHTML = `<div style="padding:10px 14px;border-radius:4px;font-size:12px;margin-bottom:14px;background:${bg[type]};border:1px solid ${border[type]};color:${colors[type]}">${text}</div>`;
}

/* ═══ INIT ═══ */
$('tbDate').textContent = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
$('tbAct').innerHTML    = `<button class="btn btn-primary" onclick="exportCSV()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Export CSV</button>`;

loadProducts();
renderActivity();
setTimeout(initCharts, 100);