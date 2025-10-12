<?php include __DIR__ . '/../auth/auth.php'; ?>

<link rel="stylesheet" href="../../../src/admin/admin.css">

<div class="admin-layout">
  <!-- Sidebar -->
  <div class="sidebar-container">
    <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="midwife-content"> 

    <div class="midwife-header">
      <h1>Midwife Database</h1>
      <button class="add-btn">Add</button>
    </div>

    <div class="midwife-list">
      <?php
      $midwives = [
        ["Jane Farrows", "https://via.placeholder.com/80"],
        ["Jane Foster", "https://via.placeholder.com/80"],
        ["Lex Green", "https://via.placeholder.com/80"],
        ["John Blue", "https://via.placeholder.com/80"],
        ["James Ocean", "https://via.placeholder.com/80"]
      ];

      foreach ($midwives as $midwife): ?>
        <div class="midwife-card">
          <img src="<?= $midwife[1] ?>" alt="<?= $midwife[0] ?>">
          <div class="midwife-info">
            <h3><?= $midwife[0] ?></h3>
            <p>Flight from India</p>
            <p class="price">₱15,200</p>
            <p>Direct</p>
          </div>
          <div class="card-actions">
            <button class="edit-btn">Edit</button>
            <button class="delete-btn">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  </div>
</div>
