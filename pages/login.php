<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Express API endpoint
    $url = "http://localhost:8080/auth/login";

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

            // Save session
            $_SESSION["token"] = $response["token"];
            $_SESSION["user"]  = $response["user"];

            $role = $response["user"]["role"];

            // 🔀 ROLE-BASED REDIRECT
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
  // 🔒 Prevent going back after logout
  window.history.pushState(null, "", window.location.href);
  window.onpopstate = function () {
    window.history.go(1);
  };
</script>

<!-- Background -->
<div class="login-background"></div>

<!-- Login Box -->
<div class="login-container">
  <div class="login-header">
    <img src="assets/img/logo.png" alt="Barangay Logo" class="logo">
    <h2>
      Barangay Canlandog Vaccination<br>
      System for Infants - Login
    </h2>
  </div>

  <form action="login" method="POST">
    <div class="center-text-button">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>

      <p class="register-text">
        No Account? <a href="/register">Register Here</a>
      </p>

      <button type="submit" class="login-btn">Login</button>
    </div>
  </form>
</div>