<?php include __DIR__ . '/../auth/auth.php'; ?>

<link rel="stylesheet" href="../../../src/admin/admin.css">

<div class="admin-layout">
  <!-- Sidebar -->
  <div class="sidebar-container">
    <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="infant-content">
      
      <div class="infant-header">
        <h1>Infant Database</h1>
        <button class="add-btn">Add</button>
      </div>

      <div class="infant-list">
        <?php
        // Static sample infants — you can replace with database data
        $infants = [
          ["John Doe", "Purok Paho", "https://images.pexels.com/photos/415824/pexels-photo-415824.jpeg"],
          ["John Doe", "Purok Paho", "https://images.pexels.com/photos/415824/pexels-photo-415824.jpeg"],
          ["John Doe", "Purok Paho", "https://images.pexels.com/photos/415824/pexels-photo-415824.jpeg"],
          ["John Doe", "Purok Paho", "https://images.pexels.com/photos/415824/pexels-photo-415824.jpeg"],
          ["John Doe", "Purok Paho", "https://images.pexels.com/photos/415824/pexels-photo-415824.jpeg"],
          ["John Doe", "Purok Paho", "https://images.pexels.com/photos/415824/pexels-photo-415824.jpeg"],
          ["John Doe", "Purok Paho", "https://images.pexels.com/photos/415824/pexels-photo-415824.jpeg"],
          ["John Doe", "Purok Paho", "https://images.pexels.com/photos/415824/pexels-photo-415824.jpeg"],
        ];

        foreach ($infants as $infant): ?>
          <div class="infant-card">
            <img src="<?= $infant[2] ?>" alt="<?= $infant[0] ?>">
            <div class="infant-info">
              <h3><?= $infant[0] ?></h3>
              <p><?= $infant[1] ?></p>
              <button class="view-btn">View</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>
</div>
