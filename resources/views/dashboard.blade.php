<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>STOCKWIZE — Dashboard</title>
@vite('resources/css/app.css')
<link rel="icon" type="image/jpg" href="favicon.ico">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
/* ── Category Picker ── */
.cat-picker{position:relative;width:100%}
.cat-picker-header{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--card,#1c1f23);border:1px solid rgba(255,255,255,.1);border-radius:var(--r,6px);cursor:pointer;font-size:13px;color:var(--muted2,#7a8090);transition:border-color .2s}
.cat-picker-header:hover,.cat-picker-header.open{border-color:var(--accent,#e8ff47);color:var(--text,#f0f2f5)}
.cat-picker-header.open #catPickerArrow{transform:rotate(180deg)}
#catPickerArrow{transition:transform .2s;flex-shrink:0}
.cat-picker-dropdown{display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:200;background:var(--card,#1c1f23);border:1px solid rgba(255,255,255,.1);border-radius:var(--r,6px);padding:10px;box-shadow:0 8px 24px rgba(0,0,0,.4)}
.cat-picker-dropdown.open{display:block}
.cat-tag-list{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px;min-height:28px}
.cat-tag{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;cursor:pointer;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:var(--muted2,#7a8090);transition:all .15s;user-select:none}
.cat-tag:hover{border-color:rgba(255,255,255,.3);color:var(--text,#f0f2f5)}
.cat-tag.selected{background:rgba(232,255,71,.12);border-color:var(--accent,#e8ff47);color:var(--accent,#e8ff47)}
.cat-tag .cat-del{font-size:11px;opacity:.6;margin-left:2px;line-height:1}
.cat-tag .cat-del:hover{opacity:1;color:#ff4d6d}
.cat-add-row{display:flex;gap:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,.06)}
.cat-add-row input{flex:1;font-size:12px}
.cat-empty{font-size:12px;color:var(--muted,#4a5060);padding:4px 0}
</style>
</head>
<body>
<div class="app">

<!-- Sidebar -->
<aside class="sidebar">
  <div class="s-logo">
    <svg viewBox="0 0 26 26" fill="none">
      <polygon points="13,3 20,7 13,11 6,7" fill="#e8ff47"/>
      <polygon points="6,7 6,15 13,19 13,11" fill="#9aaa2e"/>
      <polygon points="20,7 20,15 13,19 13,11" fill="#c8dc38"/>
    </svg>
    <div>
      <div class="s-brand">STOCKWIZE</div>
      <div class="s-tag">Inventory Management System</div>
    </div>
  </div>
  <nav class="s-nav">
    <div class="n-item active" onclick="go('dashboard',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Dashboard
    </div>
    <div class="n-item" onclick="go('products',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
      Products
    </div>
    <div class="n-item" onclick="go('reports',this)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Reports
    </div>
  </nav>
  <div class="s-foot">
    <button class="s-chpw" onclick="openChangePw()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Change Password
    </button>
    <a href="/logout" class="s-logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
    <div class="s-est">Stockwize est. 2026</div>
  </div>
</aside>

<!-- Main -->
<div class="main">
  <header class="topbar">
    <div class="tb-title" id="tbTitle">Dashboard</div>
    <div class="tb-date" id="tbDate"></div>
    <div id="tbAct"></div>
  </header>
  <div class="content">

    <!-- ══════ DASHBOARD ══════ -->
    <div class="page active" id="pg-dashboard">
      <div class="kpi-grid">
        <div class="kpi c1"><div class="kpi-lbl">Total Products</div><div class="kpi-val" id="kTotal">0</div><div class="kpi-meta" id="kTotalMeta" style="color:var(--muted)">No products yet</div></div>
        <div class="kpi c2"><div class="kpi-lbl">Total Value</div><div class="kpi-val" id="kValue">₱0</div><div class="kpi-meta" id="kValueMeta" style="color:var(--muted)">Add products to track value</div></div>
        <div class="kpi c3"><div class="kpi-lbl">Low Stock</div><div class="kpi-val" id="kLow">0</div><div class="kpi-meta" id="kLowMeta" style="color:var(--muted)">No issues</div></div>
        <div class="kpi c4"><div class="kpi-lbl">Out of Stock</div><div class="kpi-val" id="kOut">0</div><div class="kpi-meta" id="kOutMeta" style="color:var(--muted)">All clear</div></div>
      </div>
      <div class="cg2">
        <div class="card">
          <div class="card-h">
            <div><div class="card-t">Stock by Category</div><div class="card-s">Item count &amp; value per category</div></div>
            <div style="display:flex;gap:12px">
              <span style="font-size:10px;color:var(--muted2);display:flex;align-items:center;gap:4px"><span style="width:8px;height:8px;border-radius:50%;background:var(--accent);display:inline-block"></span>Count</span>
              <span style="font-size:10px;color:var(--muted2);display:flex;align-items:center;gap:4px"><span style="width:8px;height:8px;border-radius:50%;background:var(--info);display:inline-block"></span>Value (₱K)</span>
            </div>
          </div>
          <div class="card-b"><div class="ch"><canvas id="cStock"></canvas></div></div>
        </div>
        <div class="card">
          <div class="card-h"><div><div class="card-t">By Category</div><div class="card-s">Item distribution</div></div></div>
          <div class="card-b"><div class="chsm"><canvas id="cDonut"></canvas></div><div class="dl" id="dLegend"></div></div>
        </div>
      </div>
      <div class="cg3">
        <div class="card">
          <div class="card-h"><div><div class="card-t">Value by Category</div><div class="card-s">Inventory worth per category (₱K)</div></div></div>
          <div class="card-b"><div class="chsm"><canvas id="cRev"></canvas></div></div>
        </div>
        <div class="card">
          <div class="card-h"><div><div class="card-t">Value by Category</div><div class="card-s">Total inventory worth (₱K)</div></div></div>
          <div class="card-b"><div class="chsm"><canvas id="cCat"></canvas></div></div>
        </div>
        <div class="card">
          <div class="card-h"><div><div class="card-t">Stock Health</div><div class="card-s">Status breakdown</div></div></div>
          <div class="card-b"><div class="chsm"><canvas id="cHealth"></canvas></div></div>
        </div>
      </div>
      <div class="card">
        <div class="card-h"><div><div class="card-t">Recent Activity</div><div class="card-s">Latest system events</div></div></div>
        <div id="actFeed"></div>
      </div>
    </div>

    <!-- ══════ PRODUCTS ══════ -->
    <div class="page" id="pg-products">
      <div class="card">
        <div class="frow">
          <div class="sw"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg><input type="text" id="pSearch" placeholder="Search name, SKU, category…" oninput="renderProds()"/></div>
          <button class="fbtn active" onclick="setPF('all',this)">All</button>
          <button class="fbtn" onclick="setPF('In Stock',this)">In Stock</button>
          <button class="fbtn" onclick="setPF('Low Stock',this)">Low Stock</button>
          <button class="fbtn" onclick="setPF('Out of Stock',this)">Out of Stock</button>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr>
              <th onclick="sortP('name')">Product ↕</th>
              <th onclick="sortP('category')">Category ↕</th>
              <th onclick="sortP('quantity')">Stock ↕</th>
              <th onclick="sortP('price')">Unit Price ↕</th>
              <th>Total Value</th>
              <th onclick="sortP('status')">Status ↕</th>
              <th>Supplier</th>
              <th>Actions</th>
            </tr></thead>
            <tbody id="pBody"></tbody>
          </table>
        </div>
        <div class="pg"><div class="pgi" id="pPgInfo"></div><div class="pgb" id="pPgBtns"></div></div>
      </div>
    </div>

    <!-- ══════ REPORTS ══════ -->
    <div class="page" id="pg-reports">
      <div class="rg">
        <div class="rc" onclick="openReport('summary')"><div class="ri" style="background:rgba(232,255,71,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#e8ff47" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div><div><div class="rn">Stock Summary</div><div class="rd">Full inventory snapshot with quantities and values</div></div></div>
        <div class="rc" onclick="openReport('lowstock')"><div class="ri" style="background:rgba(255,204,0,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#ffcc00" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><div><div class="rn">Low Stock Report</div><div class="rd">Items at or below reorder threshold</div></div></div>
        <div class="rc" onclick="exportCSV()"><div class="ri" style="background:rgba(77,184,255,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#4db8ff" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></div><div><div class="rn">Export to CSV</div><div class="rd">Download full inventory as a spreadsheet</div></div></div>
        <div class="rc" onclick="openReport('valuation')"><div class="ri" style="background:rgba(61,255,160,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#3dffa0" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="rn">Valuation Report</div><div class="rd">Total inventory worth by category and supplier</div></div></div>
        <div class="rc" onclick="openReport('movement')"><div class="ri" style="background:rgba(255,107,53,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div><div><div class="rn">Stock Movement</div><div class="rd">Items added and consumed over time</div></div></div>
        <div class="rc" onclick="openReport('category')"><div class="ri" style="background:rgba(255,77,109,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#ff4d6d" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div><div><div class="rn">Category Breakdown</div><div class="rd">Distribution and performance per category</div></div></div>
      </div>
      <div class="card">
        <div class="card-h">
          <div><div class="card-t">Inventory Overview</div><div class="card-s">Current snapshot — all products</div></div>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Qty</th><th>Unit Price</th><th>Total Value</th><th>Status</th><th>Supplier</th></tr></thead>
            <tbody id="rBody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══════ USERS ══════ -->
    <div class="page" id="pg-users">
      <div class="card">
        <div class="frow">
          <div class="sw"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg><input type="text" id="uSearch" placeholder="Search name or email…" oninput="renderUsers()"/></div>
          <button class="fbtn active" onclick="setUF('all',this)">All</button>
          <button class="fbtn" onclick="setUF('Active',this)">Active</button>
          <button class="fbtn" onclick="setUF('Inactive',this)">Inactive</button>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
            <tbody id="uBody"></tbody>
          </table>
        </div>
        <div class="pg"><div class="pgi" id="uPgInfo"></div><div class="pgb" id="uPgBtns"></div></div>
      </div>
    </div>

  </div>
</div>
</div>

<!-- Product Modal -->
<div class="mo" id="moProd">
  <div class="md">
    <div class="mh"><div class="mt" id="moProdT">Add Product</div></div>
    <div class="mb2">
      <div class="fgrow">
        <div class="fg" style="grid-column:span 2"><label>Product Name</label><input type="text" id="pN" placeholder="e.g. Wireless Keyboard"/></div>
        <div class="fg"><label>SKU</label><input type="text" id="pSk" placeholder="WK-001"/></div>
        <div class="fg">
          <label>Category</label>
          <!-- Hidden input stores the selected value for saveProd() -->
          <input type="hidden" id="pCa" value=""/>
          <div class="cat-picker" id="catPicker">
            <div class="cat-picker-header" id="catPickerHeader" onclick="toggleCatPicker()">
              <span id="catPickerLabel">Select a category…</span>
              <svg id="catPickerArrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="cat-picker-dropdown" id="catPickerDropdown">
              <div class="cat-tag-list" id="catTagList"></div>
              <div class="cat-add-row">
                <input type="text" id="catNewInput" placeholder="New category…" onkeydown="if(event.key==='Enter'){event.preventDefault();addCategoryInline();}"/>
                <button type="button" class="btn btn-primary" style="padding:6px 12px;font-size:12px;" onclick="addCategoryInline()">+ Add</button>
              </div>
            </div>
          </div>
        </div>
        <div class="fg"><label>Quantity</label><input type="number" id="pQ" placeholder="0" min="0"/></div>
        <div class="fg"><label>Unit Price (₱)</label><input type="number" id="pPr" placeholder="0.00" step="0.01" min="0"/></div>
        <div class="fg"><label>Reorder Level</label><input type="number" id="pRe" placeholder="10" min="0"/></div>
        <div class="fg"><label>Supplier</label><input type="text" id="pSu" placeholder="Supplier name"/></div>
        <div class="fg" style="grid-column:span 2"><label>Notes</label><textarea id="pNo" placeholder="Optional…"></textarea></div>
        <!-- Image Upload -->
        <div class="fg" style="grid-column:span 2">
          <label>Product Image</label>
          <div class="img-upload">
            <input type="file" id="pImgInput" accept="image/*" onchange="onImgSelected(this)"/>
            <div class="img-drop" id="imgDrop" onclick="triggerImgUpload()">
              <p><span>Click to upload</span> or drag & drop<br/>PNG, JPG, WEBP up to 2MB</p>
            </div>
            <div class="img-preview" id="imgPreview">
              <img id="imgPreviewImg" src="" alt="Preview"/>
              <button class="img-preview-remove" type="button" onclick="removeImg()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mf"><button class="btn btn-ghost" onclick="closeMo('moProd')">Cancel</button><button class="btn btn-primary" onclick="saveProd()">Save Product</button></div>
  </div>
</div>

<!-- User Modal -->
<div class="mo" id="moUser">
  <div class="md">
    <div class="mh"><div class="mt" id="moUserT">Add User</div><button class="mc" onclick="closeMo('moUser')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button></div>
    <div class="mb2">
      <div class="fgrow">
        <div class="fg"><label>First Name</label><input type="text" id="uFi" placeholder="Juan"/></div>
        <div class="fg"><label>Last Name</label><input type="text" id="uLa" placeholder="Dela Cruz"/></div>
        <div class="fg" style="grid-column:span 2"><label>Email</label><input type="email" id="uEm" placeholder="user@stockwize.io"/></div>
        <div class="fg"><label>Role</label><select id="uRo"><option>Manager</option><option>Staff</option><option>Viewer</option></select></div>
        <div class="fg"><label>Status</label><select id="uSt"><option>Active</option><option>Inactive</option></select></div>
        <div class="fg" style="grid-column:span 2"><label>Password</label><input type="password" id="uPw" placeholder="Min. 8 characters"/></div>
      </div>
    </div>
    <div class="mf"><button class="btn btn-ghost" onclick="closeMo('moUser')">Cancel</button><button class="btn btn-primary" onclick="saveUser()">Save User</button></div>
  </div>
</div>

<!-- Change Password Modal -->
<div class="mo" id="moChPw">
  <div class="md" style="width:400px">
    <div class="mh"><div class="mt">Change Password</div></div>
    <div class="mb2">
      <div id="chPwMsg"></div>
      <div class="fg">
        <label>Current Password</label>
        <input type="password" id="chPwCurrent" placeholder="Enter current password"/>
      </div>
      <div class="fg">
        <label>New Password</label>
        <div style="position:relative;">
          <input type="password" id="chPwNew" placeholder="Min. 8 characters" style="padding-right:36px;width:100%;"/>
          <button type="button" onclick="toggleChPw('chPwNew', this)"
            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:2px;display:flex;align-items:center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <div class="fg">
        <label>Confirm New Password</label>
        <div style="position:relative;">
          <input type="password" id="chPwConfirm" placeholder="Re-enter new password" style="padding-right:36px;width:100%;"/>
          <button type="button" onclick="toggleChPw('chPwConfirm', this)"
            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;padding:2px;display:flex;align-items:center;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
    </div>
    <div class="mf">
      <button class="btn btn-ghost" onclick="closeMo('moChPw')">Cancel</button>
      <button class="btn btn-primary" onclick="submitChangePw()">Update Password</button>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="mo" id="moDel">
  <div class="md" style="width:360px">
    <div class="mh"><div class="mt">Confirm Delete</div><button class="mc" onclick="closeMo('moDel')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg></button></div>
    <div class="mb2"><p style="font-size:13px;color:var(--muted2);line-height:1.6" id="delMsg"></p></div>
    <div class="mf"><button class="btn btn-ghost" onclick="closeMo('moDel')">Cancel</button><button class="btn btn-danger" id="delOk">Delete</button></div>
  </div>
</div>

<div class="ta" id="ta"></div>

<!-- ══════ REPORT MODAL ══════ -->
<div class="mo" id="moReport" style="z-index:1100">
  <div class="md" style="width:800px;max-width:96vw;max-height:88vh;display:flex;flex-direction:column;overflow:hidden">
    <div class="mh" style="flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:12px">
      <div style="display:flex;align-items:center;gap:12px">
        <div id="rpModalIcon" style="width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0"></div>
        <div>
          <div class="mt" id="rpModalTitle" style="color:var(--accent)"></div>
          <div style="font-size:11px;color:var(--muted2);margin-top:2px" id="rpModalSub"></div>
        </div>
      </div>
      <button class="mc" onclick="closeMo('moReport')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div id="rpModalBody" style="overflow-y:auto;flex:1;padding:20px 24px"></div>
    <div style="flex-shrink:0;padding:14px 24px;border-top:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between;gap:12px">
      <span style="font-size:11px;color:var(--muted2)" id="rpModalMeta"></span>
      <button class="btn btn-primary" id="rpModalCsvBtn" onclick="downloadReportCSV()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Download CSV
      </button>
    </div>
  </div>
</div>

<style>
/* ── Report Modal inner styles (scoped, won't affect existing modals) ── */
#rpModalBody .rp-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px}
#rpModalBody .rp-stat{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:14px;text-align:center}
#rpModalBody .rp-stat-val{font-size:22px;font-weight:700;color:var(--accent);line-height:1}
#rpModalBody .rp-stat-val.warn{color:#f0a030}
#rpModalBody .rp-stat-val.danger{color:#e05a3a}
#rpModalBody .rp-stat-val.success{color:#3acf80}
#rpModalBody .rp-stat-val.info{color:#4db8ff}
#rpModalBody .rp-stat-lbl{font-size:10px;color:var(--muted2);margin-top:5px;letter-spacing:.08em;text-transform:uppercase}
#rpModalBody .rp-section{font-size:10px;color:var(--muted);letter-spacing:.10em;text-transform:uppercase;margin-bottom:10px;margin-top:2px}
#rpModalBody .rp-table{width:100%;border-collapse:collapse;font-size:12px}
#rpModalBody .rp-table th{padding:8px 12px;color:var(--muted2);font-weight:500;font-size:10px;letter-spacing:.07em;text-transform:uppercase;text-align:left;border-bottom:1px solid rgba(255,255,255,.07);white-space:nowrap}
#rpModalBody .rp-table td{padding:9px 12px;border-bottom:1px solid rgba(255,255,255,.04);color:var(--text);vertical-align:middle}
#rpModalBody .rp-table tr:last-child td{border-bottom:none}
#rpModalBody .rp-table tr:hover td{background:rgba(232,255,71,.04)}
#rpModalBody .rp-table .tr{text-align:right}
#rpModalBody .rp-table .tm{color:var(--muted2)}
#rpModalBody .rp-bar-row{display:flex;align-items:center;gap:10px;margin-bottom:8px;font-size:12px}
#rpModalBody .rp-bar-name{width:130px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex-shrink:0}
#rpModalBody .rp-bar-track{flex:1;height:7px;background:rgba(255,255,255,.06);border-radius:4px;overflow:hidden}
#rpModalBody .rp-bar-fill{height:100%;border-radius:4px;background:var(--accent)}
#rpModalBody .rp-bar-val{width:90px;text-align:right;color:var(--muted2);font-size:11px}
#rpModalBody .rp-mv-item{display:flex;align-items:flex-start;gap:12px;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.04)}
#rpModalBody .rp-mv-item:last-child{border-bottom:none}
#rpModalBody .rp-mv-dot{width:8px;height:8px;border-radius:50%;margin-top:5px;flex-shrink:0}
#rpModalBody .rp-mv-dot.add{background:#3acf80}
#rpModalBody .rp-mv-dot.edit{background:#4db8ff}
#rpModalBody .rp-mv-dot.del{background:#e05a3a}
#rpModalBody .rp-mv-name{font-size:13px;color:var(--text);font-weight:500}
#rpModalBody .rp-mv-detail{font-size:11px;color:var(--muted2);margin-top:2px}
#rpModalBody .rp-mv-time{font-size:11px;color:var(--muted);white-space:nowrap;margin-left:auto}
/* status badges */
.bd{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600;letter-spacing:.04em}
.bd-ok{background:rgba(58,207,128,.15);color:#3acf80}
.bd-low{background:rgba(240,160,48,.18);color:#f0a030}
.bd-out{background:rgba(224,90,58,.18);color:#e05a3a}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}" defer></script>

<script>
/* ══════════════════════════════════════════
   REPORT MODAL — reads from window.products
   which dashboard.js already populates
══════════════════════════════════════════ */
(function(){
  const fmt   = n => '₱' + Number(n).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});
  const fmtN  = n => Number(n).toLocaleString();
  const today = () => new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});

  function badge(qty, reorder){
    qty = +qty; reorder = +reorder;
    if(qty === 0)        return '<span class="bd bd-out">OUT OF STOCK</span>';
    if(qty <= reorder)   return '<span class="bd bd-low">LOW STOCK</span>';
    return '<span class="bd bd-ok">IN STOCK</span>';
  }

  /* icon map per report type */
  const ICONS = {
    summary:   {bg:'rgba(232,255,71,.12)',  stroke:'#e8ff47', svg:'<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>'},
    lowstock:  {bg:'rgba(255,204,0,.12)',   stroke:'#ffcc00', svg:'<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'},
    valuation: {bg:'rgba(61,255,160,.12)',  stroke:'#3dffa0', svg:'<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'},
    movement:  {bg:'rgba(255,107,53,.12)',  stroke:'#ff6b35', svg:'<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'},
    category:  {bg:'rgba(255,77,109,.12)', stroke:'#ff4d6d', svg:'<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'},
  };

  const TITLES = {
    summary:   ['STOCK SUMMARY',       'Full inventory snapshot with quantities and values'],
    lowstock:  ['LOW STOCK REPORT',    'Items at or below their reorder level'],
    valuation: ['VALUATION REPORT',    'Total inventory worth grouped by category and supplier'],
    movement:  ['STOCK MOVEMENT',      'Recently added, updated and deleted items'],
    category:  ['CATEGORY BREAKDOWN',  'Item count and total value per category'],
  };

  let _currentType = '';
  let _csvData     = '';

  /* helper: get products from dashboard.js global */
  function getProducts(){
    /* dashboard.js stores products in `products` (global) */
    if(Array.isArray(window.products)) return window.products;
    /* fallback: try allProducts */
    if(Array.isArray(window.allProducts)) return window.allProducts;
    return [];
  }

  window.openReport = function(type){
    _currentType = type;
    const ic = ICONS[type];
    const [title, sub] = TITLES[type];

    document.getElementById('rpModalIcon').style.background = ic.bg;
    document.getElementById('rpModalIcon').innerHTML =
      `<svg viewBox="0 0 24 24" fill="none" stroke="${ic.stroke}" stroke-width="2" width="20" height="20">${ic.svg}</svg>`;
    document.getElementById('rpModalTitle').textContent = title;
    document.getElementById('rpModalSub').textContent   = sub;
    document.getElementById('rpModalBody').innerHTML    = '';
    document.getElementById('rpModalMeta').textContent  = '';

    const prods = getProducts();

    switch(type){
      case 'summary':   buildSummary(prods);   break;
      case 'lowstock':  buildLowstock(prods);  break;
      case 'valuation': buildValuation(prods); break;
      case 'movement':  buildMovement(prods);  break;
      case 'category':  buildCategory(prods);  break;
    }

    /* open using existing closeMo / mo system */
    const mo = document.getElementById('moReport');
    mo.style.display = 'flex';
    setTimeout(()=>mo.classList.add('open'),10);
  };

  window.downloadReportCSV = function(){
    if(!_csvData) return;
    const blob = new Blob([_csvData],{type:'text/csv'});
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'),{
      href:url, download:`stockwize_${_currentType}_${new Date().toISOString().slice(0,10)}.csv`
    });
    a.click(); URL.revokeObjectURL(url);
  };

  /* ── SUMMARY ── */
  function buildSummary(prods){
    const total    = prods.reduce((s,p)=>s+(+p.quantity * +p.price),0);
    const totalQty = prods.reduce((s,p)=>s+ +p.quantity,0);

    const rows = prods.map(p=>`<tr>
      <td>${p.name}</td><td class="tm">${p.sku||'—'}</td><td>${p.category||'—'}</td>
      <td class="tr">${fmtN(p.quantity)}</td><td class="tr">${fmt(p.price)}</td>
      <td class="tr">${fmt(+p.quantity * +p.price)}</td>
      <td>${badge(p.quantity,p.reorder_level??p.reorder??0)}</td>
    </tr>`).join('');

    document.getElementById('rpModalBody').innerHTML = `
      <div class="rp-stats">
        <div class="rp-stat"><div class="rp-stat-val">${prods.length}</div><div class="rp-stat-lbl">Products</div></div>
        <div class="rp-stat"><div class="rp-stat-val">${fmtN(totalQty)}</div><div class="rp-stat-lbl">Total Units</div></div>
        <div class="rp-stat"><div class="rp-stat-val">${fmt(total)}</div><div class="rp-stat-lbl">Total Value</div></div>
      </div>
      <div class="rp-section">All Products</div>
      <div style="overflow-x:auto"><table class="rp-table">
        <thead><tr><th>Product</th><th>SKU</th><th>Category</th><th class="tr">Qty</th><th class="tr">Unit Price</th><th class="tr">Total Value</th><th>Status</th></tr></thead>
        <tbody>${rows}</tbody>
      </table></div>`;

    document.getElementById('rpModalMeta').textContent = `${prods.length} products · Generated ${today()}`;
    _csvData = 'Product,SKU,Category,Qty,Unit Price,Total Value,Status\n' +
      prods.map(p=>`${p.name},${p.sku||''},${p.category||''},${p.quantity},${(+p.price).toFixed(2)},${(+p.quantity * +p.price).toFixed(2)},${+p.quantity===0?'Out of Stock':+p.quantity<=(+(p.reorder_level??p.reorder??0))?'Low Stock':'OK'}`).join('\n');
  }

  /* ── LOW STOCK ── */
  function buildLowstock(prods){
    const low  = prods.filter(p=> +p.quantity <= +(p.reorder_level??p.reorder??0));
    const out  = low.filter(p=> +p.quantity === 0).length;

    const rows = low.map(p=>`<tr>
      <td>${p.name}</td><td>${p.category||'—'}</td>
      <td class="tr" style="color:#e05a3a">${p.quantity}</td>
      <td class="tr tm">${p.reorder_level??p.reorder??0}</td>
      <td>${p.supplier||'—'}</td>
      <td>${badge(p.quantity,p.reorder_level??p.reorder??0)}</td>
    </tr>`).join('') || '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">All stock levels are healthy ✓</td></tr>';

    document.getElementById('rpModalBody').innerHTML = `
      <div class="rp-stats">
        <div class="rp-stat"><div class="rp-stat-val warn">${low.length}</div><div class="rp-stat-lbl">Total Alerts</div></div>
        <div class="rp-stat"><div class="rp-stat-val danger">${out}</div><div class="rp-stat-lbl">Out of Stock</div></div>
        <div class="rp-stat"><div class="rp-stat-val warn">${low.length-out}</div><div class="rp-stat-lbl">Low Stock</div></div>
      </div>
      <div class="rp-section">Items Requiring Attention</div>
      <div style="overflow-x:auto"><table class="rp-table">
        <thead><tr><th>Product</th><th>Category</th><th class="tr">Current Qty</th><th class="tr">Reorder Level</th><th>Supplier</th><th>Status</th></tr></thead>
        <tbody>${rows}</tbody>
      </table></div>`;

    document.getElementById('rpModalMeta').textContent = `${low.length} items need attention · ${today()}`;
    _csvData = 'Product,Category,Current Qty,Reorder Level,Supplier,Status\n' +
      low.map(p=>`${p.name},${p.category||''},${p.quantity},${p.reorder_level??p.reorder??0},${p.supplier||''},${+p.quantity===0?'Out of Stock':'Low Stock'}`).join('\n');
  }

  /* ── VALUATION ── */
  function buildValuation(prods){
    const grand = prods.reduce((s,p)=>s+(+p.quantity * +p.price),0);
    const catMap={}, supMap={};
    prods.forEach(p=>{
      const c=p.category||'Uncategorised', s=p.supplier||'Unknown', v=+p.quantity * +p.price;
      catMap[c]=(catMap[c]||0)+v;
      supMap[s]=(supMap[s]||0)+v;
    });
    const catArr = Object.entries(catMap).sort((a,b)=>b[1]-a[1]);
    const catMax = Math.max(...catArr.map(e=>e[1]),1);

    const bars = catArr.map(([c,v])=>`
      <div class="rp-bar-row">
        <span class="rp-bar-name">${c}</span>
        <div class="rp-bar-track"><div class="rp-bar-fill" style="width:${Math.round(v/catMax*100)}%"></div></div>
        <span class="rp-bar-val">${fmt(v)}</span>
      </div>`).join('');

    const supRows = Object.entries(supMap).sort((a,b)=>b[1]-a[1]).map(([s,v])=>`<tr>
      <td>${s}</td><td class="tr">${fmt(v)}</td>
      <td class="tr tm">${grand>0?(v/grand*100).toFixed(1):0}%</td>
    </tr>`).join('');

    document.getElementById('rpModalBody').innerHTML = `
      <div class="rp-stats">
        <div class="rp-stat"><div class="rp-stat-val">${fmt(grand)}</div><div class="rp-stat-lbl">Grand Total</div></div>
        <div class="rp-stat"><div class="rp-stat-val">${catArr.length}</div><div class="rp-stat-lbl">Categories</div></div>
        <div class="rp-stat"><div class="rp-stat-val">${Object.keys(supMap).length}</div><div class="rp-stat-lbl">Suppliers</div></div>
      </div>
      <div class="rp-section">By Category</div>
      ${bars}
      <div class="rp-section" style="margin-top:18px">By Supplier</div>
      <div style="overflow-x:auto"><table class="rp-table">
        <thead><tr><th>Supplier</th><th class="tr">Total Value</th><th class="tr">% of Inventory</th></tr></thead>
        <tbody>${supRows}</tbody>
      </table></div>`;

    document.getElementById('rpModalMeta').textContent = `Grand total: ${fmt(grand)} · ${today()}`;
    _csvData = 'Category,Total Value\n'+catArr.map(([c,v])=>`${c},${v.toFixed(2)}`).join('\n')+
               '\n\nSupplier,Total Value\n'+Object.entries(supMap).map(([s,v])=>`${s},${v.toFixed(2)}`).join('\n');
  }

  /* ── MOVEMENT ── */
  function buildMovement(prods){
    /* Build movement from products sorted by updated_at / last modified.
       dashboard.js stores activity in window.activityLog if it exists,
       otherwise we derive it from recently-modified products. */
    let acts = [];
    if(Array.isArray(window.activityLog) && window.activityLog.length){
      acts = window.activityLog.slice(0,30).map(a=>({
        type: a.type||'edit', name:a.name||a.product||'—',
        detail:a.detail||a.description||'', time:a.time||a.created_at||''
      }));
    } else {
      /* derive from products — show recently added/modified */
      acts = [...prods]
        .sort((a,b)=>new Date(b.updated_at||0)-new Date(a.updated_at||0))
        .slice(0,15)
        .map(p=>({
          type:'edit', name:p.name,
          detail:`Qty: ${p.quantity} · ${fmt(p.price)}`,
          time: p.updated_at ? new Date(p.updated_at).toLocaleDateString('en-US',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}) : '—'
        }));
    }

    const adds  = acts.filter(a=>a.type==='add').length;
    const edits = acts.filter(a=>a.type==='edit').length;
    const dels  = acts.filter(a=>a.type==='del'||a.type==='delete').length;

    const items = acts.map(a=>`
      <div class="rp-mv-item">
        <div class="rp-mv-dot ${a.type==='add'?'add':a.type==='del'||a.type==='delete'?'del':'edit'}"></div>
        <div style="flex:1">
          <div class="rp-mv-name">${a.name}</div>
          <div class="rp-mv-detail">${a.detail}</div>
        </div>
        <div class="rp-mv-time">${a.time}</div>
      </div>`).join('') || '<div style="text-align:center;color:var(--muted);padding:24px;font-size:13px">No activity log available</div>';

    document.getElementById('rpModalBody').innerHTML = `
      <div class="rp-stats">
        <div class="rp-stat"><div class="rp-stat-val success">${adds}</div><div class="rp-stat-lbl">Added</div></div>
        <div class="rp-stat"><div class="rp-stat-val info">${edits}</div><div class="rp-stat-lbl">Updated</div></div>
        <div class="rp-stat"><div class="rp-stat-val danger">${dels}</div><div class="rp-stat-lbl">Removed</div></div>
      </div>
      <div class="rp-section">Activity Log</div>
      ${items}`;

    document.getElementById('rpModalMeta').textContent = `${acts.length} recent activities · ${today()}`;
    _csvData = 'Type,Product,Detail,Time\n'+acts.map(a=>`${a.type},${a.name},"${a.detail}",${a.time}`).join('\n');
  }

  /* ── CATEGORY BREAKDOWN ── */
  function buildCategory(prods){
    const map = {};
    prods.forEach(p=>{
      const c = p.category||'Uncategorised';
      if(!map[c]) map[c]={count:0,qty:0,value:0,low:0};
      map[c].count++;
      map[c].qty   += +p.quantity;
      map[c].value += +p.quantity * +p.price;
      if(+p.quantity <= +(p.reorder_level??p.reorder??0)) map[c].low++;
    });

    const rows = Object.entries(map).sort((a,b)=>b[1].value-a[1].value).map(([c,d])=>`<tr>
      <td>${c}</td>
      <td class="tr">${d.count}</td>
      <td class="tr">${fmtN(d.qty)}</td>
      <td class="tr">${fmt(d.value)}</td>
      <td class="tr" style="color:${d.low>0?'#f0a030':'#3acf80'}">${d.low}</td>
    </tr>`).join('');

    const combined = prods.reduce((s,p)=>s+(+p.quantity * +p.price),0);

    document.getElementById('rpModalBody').innerHTML = `
      <div class="rp-stats">
        <div class="rp-stat"><div class="rp-stat-val">${Object.keys(map).length}</div><div class="rp-stat-lbl">Categories</div></div>
        <div class="rp-stat"><div class="rp-stat-val">${prods.length}</div><div class="rp-stat-lbl">Total SKUs</div></div>
        <div class="rp-stat"><div class="rp-stat-val">${fmt(combined)}</div><div class="rp-stat-lbl">Combined Value</div></div>
      </div>
      <div class="rp-section">Per Category</div>
      <div style="overflow-x:auto"><table class="rp-table">
        <thead><tr><th>Category</th><th class="tr">SKUs</th><th class="tr">Total Units</th><th class="tr">Total Value</th><th class="tr">Low Stock</th></tr></thead>
        <tbody>${rows}</tbody>
      </table></div>`;

    document.getElementById('rpModalMeta').textContent = `${Object.keys(map).length} categories · ${prods.length} total SKUs · ${today()}`;
    _csvData = 'Category,SKUs,Total Units,Total Value,Low Stock\n'+
      Object.entries(map).map(([c,d])=>`${c},${d.count},${d.qty},${d.value.toFixed(2)},${d.low}`).join('\n');
  }

  /* close on overlay click */
  document.getElementById('moReport').addEventListener('click',function(e){
    if(e.target===this) closeMo('moReport');
  });

  /* ESC key */
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape' && document.getElementById('moReport').classList.contains('open'))
      closeMo('moReport');
  });

})();
</script>
</body>
</html>