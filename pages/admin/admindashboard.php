<?php include __DIR__ . '/auth/auth.php'; ?>
<?php include __DIR__ . '/components/admin_sidebar.php'; ?>

<link rel="stylesheet" href="../../../src/admin/admin.css"> 

<div class="main-content">
  <div class="date-time" id="date-time"></div>
</div>

<script>
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
