<!-- components/admin_sidebar.php -->
<div class="sidebar">
  <img src="/assets/img/logo.png" alt="Logo">
  <h2>Midwife Dashboard</h2>


  <a href="/admin/infants" class="nav-btn">
    <img src="../../../src/img/infant.png" alt="Infant Database" class="nav-icon"> Infant Database
  </a>
  <a href="/admin/vaccines" class="nav-btn">
    <img src="../../../src/img/vaccine.png" alt="Vaccine Database" class="nav-icon"> Vaccine Database
  </a>
  <a href="/admin/schedules" class="nav-btn">
    <img src="../../../src/img/schedule.png" alt="Vaccination Schedules" class="nav-icon"> Vaccination Schedules
  </a>

  <form method="POST" action="/logout">
    <button type="submit" class="logout-btn">Logout</button>
  </form>
</div>
