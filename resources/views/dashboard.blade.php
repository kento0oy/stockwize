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
        <div class="rc" onclick="doReport('Stock Summary')"><div class="ri" style="background:rgba(232,255,71,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#e8ff47" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div><div><div class="rn">Stock Summary</div><div class="rd">Full inventory snapshot with quantities and values</div></div></div>
        <div class="rc" onclick="doReport('Low Stock Report')"><div class="ri" style="background:rgba(255,204,0,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#ffcc00" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><div><div class="rn">Low Stock Report</div><div class="rd">Items at or below reorder threshold</div></div></div>
        <div class="rc" onclick="exportCSV()"><div class="ri" style="background:rgba(77,184,255,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#4db8ff" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></div><div><div class="rn">Export to CSV</div><div class="rd">Download full inventory as a spreadsheet</div></div></div>
        <div class="rc" onclick="doReport('Valuation Report')"><div class="ri" style="background:rgba(61,255,160,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#3dffa0" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="rn">Valuation Report</div><div class="rd">Total inventory worth by category and supplier</div></div></div>
        <div class="rc" onclick="doReport('Movement Report')"><div class="ri" style="background:rgba(255,107,53,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div><div><div class="rn">Stock Movement</div><div class="rd">Items added and consumed over time</div></div></div>
        <div class="rc" onclick="doReport('Category Breakdown')"><div class="ri" style="background:rgba(255,77,109,.1)"><svg viewBox="0 0 24 24" fill="none" stroke="#ff4d6d" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></div><div><div class="rn">Category Breakdown</div><div class="rd">Distribution and performance per category</div></div></div>
      </div>
      <div class="card">
        <div class="card-h">
          <div><div class="card-t">Inventory Overview</div><div class="card-s">Current snapshot — all products</div></div>
          <button class="btn btn-primary" onclick="exportCSV()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Export CSV</button>
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
        <div class="fg"><label>Category</label><select id="pCa"><option>Laptop</option><option>Monitor</option><option>CPU</option><option>GPU</option><option>Mother Board</option><option>RAM</option><option>HDD</option><option>SSD</option><option>Keyboard</option><option>Mouse</option><option>Headset</option></select></div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}" defer></script>
</body>
</html>