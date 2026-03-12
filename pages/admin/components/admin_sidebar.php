<?php
$current = $_SERVER['REQUEST_URI'];
?>

<div class="sidebar">
  <div class="sidebar-top">
    <div class="sidebar-brand">
      <img src="/assets/img/logo.png" alt="Logo" class="sidebar-logo">
      <div class="sidebar-brand-text">
        <h2>Admin Dashboard</h2>
        <p>Barangay Canlandog</p>
      </div>
    </div>
  </div>

  <div class="sidebar-nav">
<a href="/admin" class="nav-btn <?= $current == '/admin' ? 'active' : '' ?>">
  <img src="../../../src/img/analytics.png" class="nav-icon">
  <span>Vaccination Schedule</span>
</a>

<a href="/admin/infants" class="nav-btn <?= strpos($current, '/admin/infants') !== false ? 'active' : '' ?>">
  <img src="../../../src/img/infant.png" class="nav-icon">
  <span>Infant Database</span>
</a>

<a href="/admin/vaccines" class="nav-btn <?= strpos($current, '/admin/vaccines') !== false ? 'active' : '' ?>">
  <img src="../../../src/img/vaccine.png" class="nav-icon">
  <span>Vaccine Database</span>
</a>

<a href="/admin/puroks" class="nav-btn <?= strpos($current, '/admin/puroks') !== false ? 'active' : '' ?>">
  <img src="../../../src/img/purok.png" class="nav-icon">
  <span>Purok Database</span>
</a>

<a href="/admin/schedules" class="nav-btn <?= strpos($current, '/admin/schedules') !== false ? 'active' : '' ?>">
  <img src="../../../src/img/schedule.png" class="nav-icon">
  <span>Create Vaccination</span>
</a>

<a href="/admin/midwives" class="nav-btn <?= strpos($current, '/admin/midwives') !== false ? 'active' : '' ?>">
  <img src="../../../src/img/midwife.png" class="nav-icon">
  <span>Midwife Database</span>
</a>
  </div>

  <div class="sidebar-bottom">
    <form method="POST" action="/logout">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </div>
</div>

