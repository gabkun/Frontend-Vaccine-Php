<!-- components/admin_sidebar.php -->
<div class="sidebar">
  <img src="/assets/img/logo.png" alt="Logo">
  <h2>Admin Dashboard</h2>

  <a href="/admin" class="nav-btn">Analytics</a>
  <a href="/admin/infants" class="nav-btn">Infant Database</a>
  <a href="/admin/vaccines" class="nav-btn">Vaccine Database</a>
  <a href="/admin/puroks" class="nav-btn">Purok Database</a>
  <a href="/admin/schedules" class="nav-btn">Vaccination Schedules</a>
  <a href="/admin/midwives" class="nav-btn">Midwife Database</a>

  <form method="POST" action="/logout">
    <button type="submit" class="logout-btn">Logout</button>
  </form>
</div>
