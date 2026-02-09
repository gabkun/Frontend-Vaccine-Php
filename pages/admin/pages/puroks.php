<?php
include __DIR__ . '/../auth/auth.php';

// ✅ Handle form submission to create new Purok
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_purok"])) {

    // Prepare data for API
    $data = [
        "purok_name" => $_POST["purok_name"],
        "purok_status" => isset($_POST["purok_status"]) ? $_POST["purok_status"] : 1
    ];

    // API endpoint
    $url = "http://localhost:8080/purok/add";

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data),
            "ignore_errors" => true
        ]
    ];

    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo "<script>alert('Error connecting to API');</script>";
    } else {
        $response = json_decode($result, true);
        $msg = isset($response["message"]) ? $response["message"] : "Purok added successfully!";
        echo "<script>alert('".$msg."'); window.location.href=window.location.href;</script>";
    }
}
?>

<link rel="stylesheet" href="../../../src/admin/admin.css">

<div class="admin-layout">

  <!-- SIDEBAR -->
  <div class="sidebar-container">
    <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>
  </div>

  <!-- MAIN -->
  <div class="main-content">
    <div class="infant-content">
    <div class="purok-header">
      <h1>Purok Database</h1>
      <button class="add-btn" onclick="document.getElementById('addPurokModal').style.display='block'">Add</button>
    </div>

    <!-- PUROK FOLDERS -->
    <div class="purok-list">
      <?php
      $apiUrl = "http://localhost:8080/purok/purok";
      $puroks = json_decode(@file_get_contents($apiUrl), true);

      if ($puroks):
        foreach ($puroks as $purok):
      ?>
        <div class="purok-folder"
          onclick="openPurokModal(
            <?= $purok['id'] ?>,
            '<?= htmlspecialchars($purok['purok_name']) ?>'
          )">
          <div class="folder-icon">📁</div>
          <div class="folder-name"><?= htmlspecialchars($purok['purok_name']) ?></div>
          <div class="folder-status">
            <?= $purok['purok_status'] ? 'Active' : 'Inactive' ?>
          </div>
        </div>
      <?php endforeach; else: ?>
        <p>No Puroks Found.</p>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- ===============================
     ADD PUROK MODAL
================================ -->
<div id="addPurokModal" class="purok-modal">
  <div class="purok-modal-content" style="max-width:400px">
    <span class="close" onclick="this.closest('.purok-modal').style.display='none'">&times;</span>
    <h2>Add New Purok</h2>
    <form method="POST">
      <input type="text" name="purok_name" placeholder="Purok Name" required style="width:100%;padding:10px;margin:10px 0">
      <select name="purok_status" style="width:100%;padding:10px;margin-bottom:15px">
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
      <button type="submit" name="create_purok" class="add-btn" style="width:100%">Save</button>
    </form>
  </div>
</div>

<!-- ===============================
     VIEW PUROK MODAL
================================ -->
<div id="purokViewModal" class="purok-modal">
  <div class="purok-modal-content">
    <span class="close" onclick="closePurokModal()">&times;</span>
    <h2 id="modalPurokTitle">Purok</h2>

    <table class="purok-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Sex</th>
          <th>Age</th>
          <th>Date of Birth</th>
        </tr>
      </thead>
      <tbody id="purokTableBody">
        <tr><td colspan="5">Loading...</td></tr>
      </tbody>
    </table>
  </div>
      </div>
</div>

<script>
let currentPurokId = null;

function openPurokModal(purokId, purokName) {
  currentPurokId = purokId;

  // ✅ Reset modal state FIRST
  document.getElementById('modalPurokTitle').innerText = "📁 " + purokName;
  document.getElementById('purokTableBody').innerHTML =
    `<tr><td colspan="5">Loading...</td></tr>`;

  document.getElementById('purokViewModal').style.display = 'block';

  fetch(`http://localhost:8080/infant/by-purok/${purokId}`)
    .then(res => res.json())
    .then(data => {
      // ❌ Ignore stale responses
      if (currentPurokId !== purokId) return;

      let html = '';

      if (!Array.isArray(data) || data.length === 0) {
        html = `<tr><td colspan="5">No records found</td></tr>`;
      } else {
        data.forEach((row, i) => {
          html += `
            <tr>
              <td>${i + 1}</td>
              <td>${row.firstname} ${row.lastname}</td>
              <td>${row.sex}</td>
              <td>${row.age_year ?? 0}y ${row.age_month ?? 0}m</td>
              <td>${row.dob}</td>
            </tr>
          `;
        });
      }

      document.getElementById('purokTableBody').innerHTML = html;
    })
    .catch(() => {
      document.getElementById('purokTableBody').innerHTML =
        `<tr><td colspan="5">Failed to load data</td></tr>`;
    });
}

function closePurokModal() {
  // ✅ FULL RESET when closing
  currentPurokId = null;
  document.getElementById('modalPurokTitle').innerText = "Purok";
  document.getElementById('purokTableBody').innerHTML =
    `<tr><td colspan="5">No data</td></tr>`;
  document.getElementById('purokViewModal').style.display = 'none';
}
</script>