<?php
// index.php

$requestUri = $_SERVER['REQUEST_URI'];

// Detect routes
$isAdminRoute   = strpos($requestUri, '/admin') !== false;
$isMidwifeRoute = strpos($requestUri, '/midwife') !== false;

// Detect protected dashboard routes
$isDashboardRoute = $isAdminRoute || $isMidwifeRoute;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vaccination System</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <?php if ($isAdminRoute): ?>

    <!-- 🛡 ADMIN ONLY -->
    <link rel="stylesheet" href="src/admin/admin.css">

  <?php elseif ($isMidwifeRoute): ?>

    <!-- 🛡 MIDWIFE ONLY -->
    <link rel="stylesheet" href="src/midwife/midwife.css">

  <?php else: ?>

    <!-- 🌍 PUBLIC / GLOBAL -->
    <link rel="stylesheet" href="components/navstyles.css">
    <link rel="stylesheet" href="src/styles.css">
    <link rel="stylesheet" href="src/home.css">
    <link rel="stylesheet" href="src/login.css">

  <?php endif; ?>
</head>

<body>

  <!-- 🚫 Hide navbar for Admin & Midwife -->
  <?php if (!$isDashboardRoute): ?>
    <?php include 'components/nav.php'; ?>
  <?php endif; ?>

  <!-- Page Content -->
  <div class="main-content">
    <?php include 'router.php'; ?>
  </div>

</body>
</html>
