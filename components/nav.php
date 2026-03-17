<?php
$currentPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<nav class="navbar">
  <div class="navbar-container">
    <a href="/home" class="navbar-left">
      <img src="assets/img/logo.png" alt="Barangay Canlandog Logo">
      <div class="brand-text">
        <span class="brand-title">Vaccination System</span>
        <small class="brand-subtitle">Barangay Canlandog</small>
      </div>
    </a>

    <div class="navbar-right">
      <a href="/home" class="nav-link <?= ($currentPage === '/home' || $currentPage === '/') ? 'active' : '' ?>">Home</a>
      <a href="/about" class="nav-link <?= ($currentPage === '/about') ? 'active' : '' ?>">About</a>
      <a href="/contact" class="nav-link <?= ($currentPage === '/contact') ? 'active' : '' ?>">Contact Us</a>
      <a href="/login" class="btn-login">Login Here</a>
    </div>
  </div>
</nav>