<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Northwind BMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="assets/app.css">
</head>
<body>
  <div id="login" class="login-screen">
    <form class="login-box card shadow-sm" onsubmit="login(event)">
      <div class="card-body p-4">
        <div class="brand-mark mb-3">NW</div>
        <h1 class="h4">เข้าสู่ระบบ Northwind</h1>
        <p class="text-secondary">ระบบจัดการธุรกิจ</p>
        <div id="loginError" class="alert alert-danger d-none" role="alert"></div>
        <label class="form-label" for="loginUsername">ชื่อผู้ใช้</label>
        <input id="loginUsername" class="form-control mb-3" name="username" value="admin" required autocomplete="username">
        <label class="form-label" for="loginPassword">รหัสผ่าน</label>
        <input id="loginPassword" class="form-control mb-3" name="password" type="password" required autocomplete="current-password">
        <button class="btn btn-primary w-100">เข้าสู่ระบบ</button>
        <p class="small text-secondary mt-3 mb-0">ใช้บัญชีผู้ดูแลระบบที่กำหนดไว้บนเซิร์ฟเวอร์</p>
      </div>
    </form>
  </div>

  <div class="layout">
    <aside class="side">
      <a class="brand d-flex align-items-center gap-2" href="#" onclick="return false"><span class="brand-mark">NW</span><span>Northwind BMS</span></a>
      <div class="group-label">ภาพรวม</div>
      <nav class="nav-list" aria-label="เมนูหลัก">
        <button class="nav-link active" data-page="dashboard"><span>▦</span>แดชบอร์ด</button>
        <div class="group-label">จัดการข้อมูล</div>
        <button class="nav-link" data-page="products"><span>▣</span>สินค้า</button>
        <button class="nav-link" data-page="categories"><span>◫</span>หมวดหมู่สินค้า</button>
        <button class="nav-link" data-page="orders"><span>▤</span>คำสั่งซื้อ</button>
        <button class="nav-link" data-page="orderdetails"><span>☷</span>รายการสินค้าในคำสั่งซื้อ</button>
        <button class="nav-link" data-page="customers"><span>♙</span>ลูกค้า</button>
        <button class="nav-link" data-page="suppliers"><span>♧</span>ผู้จัดจำหน่าย</button>
        <button class="nav-link" data-page="employees"><span>♟</span>พนักงาน</button>
        <button class="nav-link" data-page="shippers"><span>➤</span>บริษัทขนส่ง</button>
        <div class="group-label">วิเคราะห์</div>
        <button class="nav-link" data-page="reports"><span>▥</span>รายงานภาพรวม</button>
        <button class="nav-link" data-page="monthly"><span>▤</span>รายงานรายเดือน</button>
        <button class="nav-link" data-page="top-products"><span>★</span>สินค้าขายดี</button>
      </nav>
      <div class="sidebar-foot small text-secondary">Northwind Business Management</div>
    </aside>

    <main class="main">
      <header class="topbar"><span id="crumb">แดชบอร์ดภาพรวม</span><div class="d-flex align-items-center gap-2"><a class="btn btn-sm btn-outline-primary" href="api-docs.html">API Docs</a><span id="who" class="d-flex align-items-center gap-2 text-secondary"></span></div></header>
      <div class="container-fluid content"><div id="app"></div></div>
    </main>
  </div>

  <div id="recordModal" class="record-modal" aria-hidden="true">
    <form id="recordForm" class="record-dialog card shadow" onsubmit="save(event)">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 id="modalTitle" class="h5 mb-0">ข้อมูล</h2>
        <button type="button" class="btn-close" aria-label="ปิด" onclick="closeModal()"></button>
      </div>
      <div class="card-body"><div id="formError" class="alert alert-danger d-none"></div><div id="formFields" class="row g-3"></div></div>
      <div class="card-footer bg-white d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="closeModal()">ยกเลิก</button>
        <button class="btn btn-primary">บันทึกข้อมูล</button>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <script src="assets/app.js" defer></script>
</body>
</html>