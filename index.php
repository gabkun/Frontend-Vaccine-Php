<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vaccination System</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <!-- Navbar CSS -->
  <link rel="stylesheet" href="components/navstyles.css">

  <!-- Main CSS -->
  <link rel="stylesheet" href="src/styles.css">

  <link rel="stylesheet" href="src/home.css">
    <link rel="stylesheet" href="src/login.css">
</head>
<body>

  <!-- Include Navbar -->
  <?php include 'components/nav.php'; ?>

  <!-- Page Content -->
  <div class="main-content">
    <?php include 'router.php'; ?>
  </div>

</body>
</html>
