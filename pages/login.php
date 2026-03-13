<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Express API endpoint
    $url = "https://backend-vaccine.onrender.com/auth/login";

    $data = [
        "username" => $username,
        "password" => $password
    ];

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data),
            "ignore_errors" => true
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo "<script>alert('Error connecting to server');</script>";
    } else {
        $response = json_decode($result, true);

        if (isset($response["token"])) {

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
            $msg = isset($response["message"]) ? $response["message"] : "Login failed";
            echo "<script>alert('".$msg."');</script>";
        }
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
        <img src="assets/img/logo.png" alt="Barangay Logo" class="logo">
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