<?php include __DIR__ . '/auth/auth.php'; ?>


<link rel="stylesheet" href="../../../src/admin/admin.css"> 

<div class="admin-layout">
  <div class="sidebar-container">
  <?php include __DIR__ . '/components/admin_sidebar.php'; ?>
  </div>

  <div class="main-content">
    <div class="infant-content">
      <div class="infant-header">
        <h1>Admin Dashboard Overview</h1>
      </div>

  <div class="date-time" id="date-time"></div>
      <div class="dashboard-cards">
        <?php
          $totalInfants = rand(50, 250);
          $totalMidwives = rand(10, 40);
          $totalVaccines = rand(300, 800);
          $monthlyRegistrations = rand(10, 60);
        ?>

        <div class="stat-card">
          <h3>Total Infants</h3>
          <p><?= $totalInfants ?></p>
        </div>

        <div class="stat-card">
          <h3>Total Midwives</h3>
          <p><?= $totalMidwives ?></p>
        </div>

        <div class="stat-card">
          <h3>Total Vaccines Distributed</h3>
          <p><?= $totalVaccines ?></p>
        </div>

        <div class="stat-card">
          <h3>New Registrations This Month</h3>
          <p><?= $monthlyRegistrations ?></p>
        </div>
      </div>

      <!-- ==============================
          CHARTS (RANDOM ANALYTICS)
      ============================== -->
      <div class="charts-section">
        <div class="chart-card">
          <h3>Monthly Infant Registrations</h3>
          <canvas id="infantChart"></canvas>
        </div>

        <div class="chart-card">
          <h3>Vaccines Distributed by Type</h3>
          <canvas id="vaccineChart"></canvas>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ==============================
     JS SCRIPTS
============================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // ✅ Date & Time Display
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

  // ✅ Chart 1 - Infant Registrations (Random Data)
  const ctx1 = document.getElementById('infantChart').getContext('2d');
  new Chart(ctx1, {
    type: 'line',
    data: {
      labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      datasets: [{
        label: 'Infant Registrations',
        data: Array.from({length: 12}, () => Math.floor(Math.random() * 50) + 10),
        borderColor: '#23408e',
        backgroundColor: 'rgba(35, 64, 142, 0.2)',
        fill: true,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  // ✅ Chart 2 - Vaccines Distributed
  const ctx2 = document.getElementById('vaccineChart').getContext('2d');
  new Chart(ctx2, {
    type: 'doughnut',
    data: {
      labels: ['BCG', 'Hepatitis B', 'Polio', 'Measles', 'Others'],
      datasets: [{
        data: Array.from({length: 5}, () => Math.floor(Math.random() * 200) + 50),
        backgroundColor: ['#23408e', '#f0c419', '#43a047', '#c00', '#6ecb63']
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });
</script>
