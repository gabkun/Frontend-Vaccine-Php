<?php
// index.php

// detect if the URL contains "/admin"
$isAdminRoute = strpos($_SERVER['REQUEST_URI'], '/admin') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vaccination System</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <?php if ($isAdminRoute): ?>
    <!-- Admin Only CSS -->
    <link rel="stylesheet" href="src/admin/admin.css">
  <?php else: ?>
    <!-- Global + Page CSS -->
    <link rel="stylesheet" href="components/navstyles.css">
    <link rel="stylesheet" href="src/styles.css">
    <link rel="stylesheet" href="src/home.css">
    <link rel="stylesheet" href="src/login.css">
  <?php endif; ?>
</head>
<body>

  <!-- Include Navbar only if NOT on admin route -->
  <?php if (!$isAdminRoute): ?>
    <?php include 'components/nav.php'; ?>
  <?php endif; ?>

  <!-- Page Content -->
  <div class="main-content">
    <?php include 'router.php'; ?>
  </div>

</body>
</html>
