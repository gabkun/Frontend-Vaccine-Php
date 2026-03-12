<?php
$current = $_SERVER['REQUEST_URI'];
?>

<div class="sidebar">
  <div class="sidebar-top">
    <div class="sidebar-brand">
      <img src="/assets/img/logo.png" alt="Logo" class="sidebar-logo">
      <div class="sidebar-brand-text">
        <h2>Midwife Dashboard</h2>
        <p>Barangay Canlandog</p>
      </div>
    </div>
  </div>

  <div class="sidebar-nav">
<a href="/midwife" class="nav-btn <?= $current == '/midwife' ? 'active' : '' ?>">
  <img src="../../../src/img/analytics.png" class="nav-icon">
  <span>Vaccination Schedule</span>
</a>

<a href="/midwife/infants" class="nav-btn <?= strpos($current, '/midwife/infants') !== false ? 'active' : '' ?>">
  <img src="../../../src/img/infant.png" class="nav-icon">
  <span>Infant Database</span>
</a>

<a href="/midwife/vaccines" class="nav-btn <?= strpos($current, '/midwife/vaccines') !== false ? 'active' : '' ?>">
  <img src="../../../src/img/vaccine.png" class="nav-icon">
  <span>Vaccine Database</span>
</a>


<a href="/midwife/schedules" class="nav-btn <?= strpos($current, '/midwife/schedules') !== false ? 'active' : '' ?>">
  <img src="../../../src/img/schedule.png" class="nav-icon">
  <span>Create Vaccination</span>
</a>


  </div>

  <div class="sidebar-bottom">
    <form method="POST" action="/logout">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </div>
</div>

