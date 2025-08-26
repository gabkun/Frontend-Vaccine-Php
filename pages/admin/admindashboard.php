<?php
session_start();

// Check if token exists in session
if (!isset($_SESSION["token"])) {
    header("Location: /login");
    exit;
}

// Optional: Validate token with backend
$token = $_SESSION["token"];
$url = "http://localhost:8080/auth/profile";

$options = array(
    "http" => array(
        "header"  => "Authorization: Bearer " . $token,
        "method"  => "GET"
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    // Token invalid or expired → force logout
    session_destroy();
    header("Location: /login");
    exit;
}

$response = json_decode($result, true);

// If backend says invalid, logout
if (isset($response["message"]) && ($response["message"] === "No token provided" || $response["message"] === "Invalid or expired token")) {
    session_destroy();
    header("Location: /login");
    exit;
}
?>

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

  <form method="POST" action="logout">
    <button type="submit" class="logout-btn">Logout</button>
  </form>
</div>

<!-- Main Content -->
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
