<?php include __DIR__ . '/../auth/auth.php'; ?>
<link rel="stylesheet" href="../../../src/admin/admin.css">

<div class="admin-layout">
  <!-- Sidebar -->
  <div class="sidebar-container">
    <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="vaccine-content">
      <div class="vaccine-header">
        <h1>Vaccine Database</h1>
        <button class="add-btn">Add</button>
      </div>

      <div class="vaccine-list">
        <?php
        $vaccines = [
          "BCG (Bacillus Calmette–Guérin)",
          "Hepatitis B (HepB)",
          "DPT/DTaP (Diphtheria, Pertussis, Tetanus)",
          "Polio (OPV/IPV – Oral or Inactivated Polio Vaccine)",
          "Hib (Haemophilus influenzae type b)",
          "Pneumococcal Conjugate Vaccine (PCV)",
          "Rotavirus Vaccine",
          "Measles-Containing Vaccine"
        ];

        foreach ($vaccines as $vaccine): ?>
          <div class="vaccine-card">
            <img src="../../../src/assets/vaccine_sample.png" alt="<?= htmlspecialchars($vaccine) ?>">
            <p><?= htmlspecialchars($vaccine) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
