<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // API endpoint (Express backend)
    $url = "http://localhost:8080/auth/login";

    // Data to send
    $data = array(
        "username" => $username,
        "password" => $password
    );

    $options = array(
        "http" => array(
            "header"  => "Content-Type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data)
        )
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo "<script>alert('Error connecting to API');</script>";
    } else {
        $response = json_decode($result, true);

        if (isset($response["token"])) {
            // Save user data and JWT in PHP session
            $_SESSION["token"] = $response["token"];
            $_SESSION["user"] = $response["user"];

            // Redirect to /admin
            header("Location: /admin");
            exit;
        } else {
            echo "<script>alert('".$response["message"]."');</script>";
        }
    }
}
?>
<!-- Background --> 
<div class="login-background"></div>

<!-- Login Box -->
<div class="login-container">
  <div class="login-header">
    <img src="assets/img/logo.png" alt="Barangay Logo" class="logo">
    <h2>Barangay Canlandog Vaccination<br>System for Infants - Login</h2>
  </div>

  <form action="login" method="POST">
    <div class="center-text-button">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>

      <p class="register-text">No Account? <a href="/register">Register Here</a></p>
      <button type="submit" class="login-btn">Login</button>
    </div>
  </form>
</div>
