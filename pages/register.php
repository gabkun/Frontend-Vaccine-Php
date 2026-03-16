<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // API endpoint (Express backend)
    $url = "http://localhost:8080/auth/register";

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

        if (isset($response["message"]) && $response["message"] === "User registered successfully") {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='/login';</script>";
        } else {
            echo "<script>alert('".$response["message"]."');</script>";
        }
    }
}
?>
<!-- Background --> 
<div class="login-background"></div>

<!-- Register Box -->
<div class="login-container">
  <div class="login-header">
    <img src="assets/img/logo.png" alt="Barangay Logo" class="logo">
    <h2>Barangay Canlandog Vaccination<br>System for Infants - Register</h2>
  </div>

  <form action="register" method="POST">
    <div class="center-text-button">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>

      <p class="register-text">Have Account? <a href="/login">Login Here</a></p>
      <button type="submit" class="login-btn">Register</button>
    </div>
  </form>
</div>
