<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $url = "http://localhost:8080/auth/login";

    $data = [
        "username" => $username,
        "password" => $password
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        die("cURL Error: " . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response = json_decode($result, true);

    if ($httpCode === 200 && isset($response["token"])) {
        $_SESSION["token"] = $response["token"];
        $_SESSION["user"]  = $response["user"];

        $role = $response["user"]["role"];

        if ($role == 1) {
            header("Location: /admin");
            exit;
        } elseif ($role == 2) {
            header("Location: /midwife");
            exit;
        } else {
            echo "<script>alert('Unauthorized role');</script>";
        }
    } else {
        $msg = $response["message"] ?? "Login failed";
        echo "<script>alert('".$msg."');</script>";
    }
}
?>
<script>
  window.history.pushState(null, "", window.location.href);
  window.onpopstate = function () {
    window.history.go(1);
  };
</script>

<div class="login-page">
  <div class="login-background-overlay"></div>

  <div class="login-wrapper">
    <div class="login-left">
      <div class="login-brand-badge">Barangay Canlandog • Murcia</div>
      <h1>Welcome Back</h1>
      <p>
        Log in to manage infant vaccination schedules, monitor appointments,
        and access barangay health records securely.
      </p>
    </div>

    <div class="login-container">
      <div class="login-header">
        <img src="assets/img/sys-logo.png" alt="Barangay Logo" class="logo">
        <div class="login-header-text">
          <h2>Vaccination System</h2>
          <p>Barangay Canlandog for Infants</p>
        </div>
      </div>

      <form action="login" method="POST" class="login-form">
        <div class="input-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Enter your username" required>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="login-row">

        </div>

        <button type="submit" class="login-btn">Login</button>
      </form>
    </div>
  </div>
</div>