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
  <!-- Sidebar -->
  <div class="sidebar-container">
    <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="infant-content">
      
      <div class="infant-header">
        <h1>Purok Database</h1>
        <button class="add-btn" onclick="document.getElementById('addPurokModal').style.display='block'">Add</button>
      </div>

<div id="addPurokModal" class="purok-modal">
  <div class="purok-modal-content">
    <span class="close" onclick="document.getElementById('addPurokModal').style.display='none'">&times;</span>
    <h2>Add New Purok</h2>
    <form method="POST">
      <input type="text" name="purok_name" placeholder="Purok Name" required>
      <select name="purok_status">
        <option value="1" selected>Active</option>
        <option value="0">Inactive</option>
      </select>
      <button type="submit" name="create_purok">Add Purok</button>
    </form>
  </div>
</div>


      <div class="infant-list" id="purokList">
        <?php
        // Fetch all Puroks from API
        $apiUrl = "http://localhost:8080/purok/purok";
        $puroks = json_decode(@file_get_contents($apiUrl), true);
        if ($puroks && count($puroks) > 0):
            foreach ($puroks as $purok): ?>
              <div class="infant-card">
                <img src="../../../assets/img/logo.png" alt="<?= htmlspecialchars($purok['purok_name']) ?>">
                <div class="infant-info">
                  <h3><?= htmlspecialchars($purok['purok_name']) ?></h3>
                  <p>Status: <?= $purok['purok_status'] == 1 ? "Active" : "Inactive" ?></p>
                </div>
              </div>
        <?php
            endforeach;
        else: ?>
            <p>No Puroks found.</p>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>