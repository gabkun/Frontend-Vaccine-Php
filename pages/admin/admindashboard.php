<!-- Sidebar -->
<div class="sidebar">
  <img src="assets/img/logo.png" alt="Logo">
  <h2>Admin Dashboard</h2>

  <button class="nav-btn">Analytics</button>
  <button class="nav-btn">Infant Database</button>
  <button class="nav-btn">Vaccine Database</button>
  <button class="nav-btn">Purok Database</button>
  <button class="nav-btn">Vaccination Schedules</button>
  <button class="nav-btn">Midwife Database</button>

  <button class="logout-btn">Logout</button>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="date-time" id="date-time"></div>
  <!-- Add your content here -->
</div>

<script>
  // Display date and time
  function updateDateTime() {
    const now = new Date();
    const options = { 
      month: '2-digit', 
      day: '2-digit', 
      year: 'numeric', 
      hour: '2-digit', 
      minute: '2-digit', 
      second: '2-digit' 
    };
    document.getElementById('date-time').textContent = 
      "Date: " + now.toLocaleDateString('en-US', options);
  }
  setInterval(updateDateTime, 1000);
  updateDateTime();
</script>
