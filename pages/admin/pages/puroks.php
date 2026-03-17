<?php 
include __DIR__ . '/../auth/auth.php';

// ✅ Handle form submission to create new Purok
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_purok"])) {

    $data = [
        "purok_name" => $_POST["purok_name"],
        "purok_status" => isset($_POST["purok_status"]) ? (int)$_POST["purok_status"] : 1
    ];

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

      </div>

      <!-- PUROK FOLDERS -->
      <div class="purok-list">
        <?php
        $apiUrl = "http://localhost:8080/purok/purok";
        $puroks = json_decode(@file_get_contents($apiUrl), true);

        if ($puroks && is_array($puroks)):
          foreach ($puroks as $purok):
        ?>
          <div class="purok-folder"
            onclick="openPurokModal(
              <?= (int)$purok['id'] ?>,
              '<?= htmlspecialchars(addslashes($purok['purok_name'])) ?>',
              <?= isset($purok['purok_status']) ? (int)$purok['purok_status'] : 1 ?>
            )">
            <div class="folder-icon">📁</div>
            <div class="folder-name"><?= htmlspecialchars($purok['purok_name']) ?></div>
            <div class="folder-status">
              <?= !empty($purok['purok_status']) ? 'Active' : 'Inactive' ?>
            </div>
          </div>
        <?php 
          endforeach; 
        else: 
        ?>
          <p>No Puroks Found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ===============================
     ADD PUROK MODAL
================================ -->
<div id="addPurokModal" class="purok-modal">
  <div class="purok-modal-content" style="max-width:400px">
    <span class="close" onclick="document.getElementById('addPurokModal').style.display='none'">&times;</span>
    <h2>Add New Purok</h2>

    <form method="POST">
      <input 
        type="text" 
        name="purok_name" 
        placeholder="Purok Name" 
        required 
        style="width:100%;padding:10px;margin:10px 0"
      >

      <select name="purok_status" style="width:100%;padding:10px;margin-bottom:15px">
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>

      <button type="submit" name="create_purok" class="add-btn" style="width:100%">
        Save
      </button>
    </form>
  </div>
</div>

<!-- ===============================
     VIEW / EDIT PUROK MODAL
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

    <div style="margin-top:15px; text-align:right;">
      <button type="button" onclick="showEditPurok()" class="add-btn">
        Edit Purok
      </button>
    </div>

    <!-- EDIT FORM -->
    <div id="editPurokForm" style="display:none; margin-top:15px;">
      <input
        type="text"
        id="editPurokName"
        placeholder="Purok Name"
        style="width:100%;padding:10px;margin-bottom:10px"
      >

      <select
        id="editPurokStatus"
        style="width:100%;padding:10px;margin-bottom:10px"
      >
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>

      <button
        type="button"
        onclick="updatePurok()"
        class="add-btn"
        style="width:100%"
      >
        Save Changes
      </button>
    </div>
  </div>
</div>

<script>
let currentPurokId = null;
let currentPurokName = "";
let currentPurokStatus = 1;

function openPurokModal(purokId, purokName, purokStatus) {
  currentPurokId = purokId;
  currentPurokName = purokName;
  currentPurokStatus = String(purokStatus);

  document.getElementById('modalPurokTitle').innerText = "📁 " + purokName;
  document.getElementById('purokTableBody').innerHTML =
    `<tr><td colspan="5">Loading...</td></tr>`;

  // reset edit form every open
  document.getElementById("editPurokForm").style.display = "none";
  document.getElementById("editPurokName").value = purokName;
  document.getElementById("editPurokStatus").value = String(purokStatus);

  document.getElementById('purokViewModal').style.display = 'block';

  fetch(`http://localhost:8080/infant/by-purok/${purokId}`)
    .then(res => res.json())
    .then(data => {
      if (currentPurokId !== purokId) return;

      let html = '';

      if (!Array.isArray(data) || data.length === 0) {
        html = `<tr><td colspan="5">No records found</td></tr>`;
      } else {
        data.forEach((row, i) => {
          html += `
            <tr>
              <td>${i + 1}</td>
              <td>${row.firstname ?? ''} ${row.lastname ?? ''}</td>
              <td>${row.sex ?? ''}</td>
              <td>${row.age_year ?? 0}y ${row.age_month ?? 0}m</td>
              <td>${formatDate(row.dob)}</td>
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

function showEditPurok() {
  document.getElementById("editPurokForm").style.display = "block";
  document.getElementById("editPurokName").value = currentPurokName;
  document.getElementById("editPurokStatus").value = currentPurokStatus;
}

function updatePurok() {
  const purokName = document.getElementById("editPurokName").value.trim();
  const purokStatus = document.getElementById("editPurokStatus").value;

  if (!purokName) {
    alert("Purok name is required");
    return;
  }

  fetch(`http://localhost:8080/purok/purok/${currentPurokId}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      purok_name: purokName,
      purok_status: parseInt(purokStatus)
    })
  })
  .then(async (res) => {
    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
      alert(data.message || "Failed to update purok");
      return;
    }

    alert(data.message || "Purok updated successfully");
    window.location.reload();
  })
  .catch((err) => {
    console.error(err);
    alert("Error connecting to API");
  });
}

function closePurokModal() {
  currentPurokId = null;
  currentPurokName = "";
  currentPurokStatus = 1;

  document.getElementById('modalPurokTitle').innerText = "Purok";
  document.getElementById('purokTableBody').innerHTML =
    `<tr><td colspan="5">No data</td></tr>`;

  document.getElementById("editPurokForm").style.display = "none";
  document.getElementById("editPurokName").value = "";
  document.getElementById("editPurokStatus").value = "1";

  document.getElementById('purokViewModal').style.display = 'none';
}

function formatDate(dateString) {
  if (!dateString) return '';

  const date = new Date(dateString);
  if (isNaN(date)) return dateString;

  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

// close modals when clicking outside
window.onclick = function(event) {
  const addModal = document.getElementById('addPurokModal');
  const viewModal = document.getElementById('purokViewModal');

  if (event.target === addModal) {
    addModal.style.display = "none";
  }

  if (event.target === viewModal) {
    closePurokModal();
  }
}
</script>