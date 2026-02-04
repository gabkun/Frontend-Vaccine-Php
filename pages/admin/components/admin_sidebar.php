<!-- components/admin_sidebar.php -->
<div class="sidebar">
  <img src="/assets/img/logo.png" alt="Logo">
  <h2>Admin Dashboard</h2>

  <a href="/admin" class="nav-btn">
    <img src="../../../src/img/analytics.png" alt="Analytics" class="nav-icon"> Vaccination Schedule
  </a>
  <a href="/admin/infants" class="nav-btn">
    <img src="../../../src/img/infant.png" alt="Infant Database" class="nav-icon"> Infant Database
  </a>
  <a href="/admin/vaccines" class="nav-btn">
    <img src="../../../src/img/vaccine.png" alt="Vaccine Database" class="nav-icon"> Vaccine Database
  </a>
  <a href="/admin/puroks" class="nav-btn">
    <img src="../../../src/img/purok.png" alt="Purok Database" class="nav-icon"> Purok Database
  </a>
  <a href="/admin/schedules" class="nav-btn">
    <img src="../../../src/img/schedule.png" alt="Vaccination Schedules" class="nav-icon"> Create Vaccination
  </a>
  <a href="/admin/midwives" class="nav-btn">
    <img src="../../../src/img/midwife.png" alt="Midwife Database" class="nav-icon"> Midwife Database
  </a>

  <form method="POST" action="/logout">
    <button type="submit" class="logout-btn">Logout</button>
  </form>
</div>
