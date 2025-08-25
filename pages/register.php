<?php 

?>
<!-- Background --> 
<div class="login-background"></div>

<!-- Login Box -->
<div class="login-container">
  <div class="login-header">
    <img src="assets/img/logo.png" alt="Barangay Logo" class="logo">
    <h2>Barangay Canlandog Vaccination<br>System for Infants - Register</h2>
  </div>

  <form action="authenticate.php" method="POST">
    <div class="center-text-button">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>

    <p class="register-text">Have Account? <a href="/login">Login Here</a></p>
    <button type="submit" class="login-btn">Register</button>
</div>
  </form>
</div>
