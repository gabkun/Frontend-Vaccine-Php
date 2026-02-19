<?php
include __DIR__ . '/../auth/auth.php';

/* ============================================================
    👉 CREATE VACCINE (POST)
    Uses POST-Redirect-GET to stop browser resubmission
============================================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_vaccine"])) {

    $data = [
        "vaccine_name" => $_POST["vaccine_name"],
        "description" => $_POST["description"],
        "status" => $_POST["status"]
    ];

    $url = "http://localhost:8080/vaccine/add";

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json\r\n",
            "method"  => "POST",
            "content" => json_encode($data),
            "ignore_errors" => true
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    // If API connection failed
    if ($result === FALSE) {
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?error=api");
        exit;
    }

    $response = json_decode($result, true);
    $msg = $response["message"] ?? "Vaccine added successfully!";

    // Redirect to stop POST resubmission
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?success=" . urlencode($msg));
    exit;
}


/* ============================================================
    👉 GET ALL VACCINES (LOAD LIST)
============================================================ */
$vaccineData = [];

$getUrl = "http://localhost:8080/vaccine/get";

$getOptions = [
    "http" => [
        "method" => "GET",
        "header" => "Content-Type: application/json\r\n",
        "ignore_errors" => true
    ]
];

$getContext = stream_context_create($getOptions);
$getResult = @file_get_contents($getUrl, false, $getContext);

if ($getResult !== FALSE) {
    $decoded = json_decode($getResult, true);

    if (is_array($decoded)) {
        $vaccineData = $decoded;
    }
}
?>

<!-- ============================================================
    SUCCESS / ERROR ALERTS (AFTER REDIRECT)
============================================================ -->
<?php if (isset($_GET['success'])): ?>
<script>
alert("<?= htmlspecialchars($_GET['success'], ENT_QUOTES) ?>");
</script>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'api'): ?>
<script>
alert("Error connecting to API");
</script>
<?php endif; ?>

<!-- ============================================================
                    FRONTEND HTML
============================================================ -->
<link rel="stylesheet" href="../../../src/midwife/midwife.css">

<div class="admin-layout">
    <!-- Sidebar -->
    <div class="sidebar-container">
        <?php include __DIR__ . '/../components/midwife_sidebar.php'; ?>
    </div>

    <!-- Main Content -->
<div class="main-content">
  <div class="vaccine-content">

    <div class="vaccine-header">
      <h1>Vaccine Database</h1>
      <button class="add-btn">Add Vaccine</button>
    </div>

<div class="vaccine-table-wrapper">
    <?php if (!empty($vaccineData)): ?>
        <table class="vaccine-table">
            <thead>
                <tr>
        
                    <th>Vaccine Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vaccineData as $v): ?>
                <tr>

                    <td><?= htmlspecialchars($v['vaccine_name']) ?></td>
                    <td><?= htmlspecialchars($v['description'] ?? 'No description available') ?></td>
                    <td class="<?= $v['status'] == 1 ? 'active' : 'inactive' ?>">
                        <?= $v['status'] == 1 ? 'Active' : 'Inactive' ?>
                    </td>
                    <td>
                        <button
                        type="button"
                        class="vax-btn vax-btn-edit"
                        onclick="openEditVaccineModal(
                            <?= (int)$v['id'] ?>,
                            '<?= htmlspecialchars($v['vaccine_name'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($v['description'] ?? '', ENT_QUOTES) ?>',
                            <?= (int)$v['status'] ?>
                        )"
                        >
                        Edit
                        </button>
                        <button
                        type="button"
                        class="vax-btn vax-btn-delete"
                        onclick="deleteVaccine(<?= (int)$v['id'] ?>)"
                        >
                        Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No vaccines found.</p>
    <?php endif; ?>
</div>

  </div>
</div>



<!-- ============================================================
                    ADD VACCINE MODAL
============================================================ -->
<div id="addVaccineModal" class="modal-overlay">
    <div class="modal-box">
        <h2>Add Vaccine</h2>

        <form id="addVaccineForm" method="POST">
            <input type="hidden" name="vaccine_id" id="vaccine_id">
            <div class="form-group">
                <label>Vaccine Name</label>
                <input type="text" name="vaccine_name" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="button" class="close-modal">Cancel</button>
                <button type="submit" name="create_vaccine" class="save-btn">Save</button>
            </div>
        </form>
    </div>
</div>


<!-- ============================================================
                    MODAL SCRIPT
============================================================ -->
<script>
  const modal = document.getElementById("addVaccineModal");
  const openBtn = document.querySelector(".add-btn");
  const closeBtn = document.querySelector(".close-modal");
  const form = document.getElementById("addVaccineForm");

  let formMode = "create";     // create | edit
  let editingVaccineId = null; // holds the id

  // OPEN CREATE MODAL (PHP submit)
  openBtn.addEventListener("click", () => {
    formMode = "create";
    editingVaccineId = null;

    form.reset();
    document.getElementById("vaccine_id").value = "";

    modal.querySelector("h2").textContent = "Add Vaccine";
    form.querySelector(".save-btn").textContent = "Save";

    // IMPORTANT: keep PHP create handler working
    form.querySelector(".save-btn").setAttribute("name", "create_vaccine");

    modal.style.display = "flex";
  });

  // CLOSE MODAL
  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
  });

  // OPEN EDIT MODAL (prefill fields)
  window.openEditVaccineModal = (id, vaccineName, description, status) => {
    formMode = "edit";
    editingVaccineId = id;

    document.getElementById("vaccine_id").value = id;

    form.querySelector('[name="vaccine_name"]').value = vaccineName || "";
    form.querySelector('[name="description"]').value = description || "";
    form.querySelector('[name="status"]').value = String(status);

    modal.querySelector("h2").textContent = "Edit Vaccine";
    form.querySelector(".save-btn").textContent = "Update";

    // IMPORTANT: prevent PHP create from running
    form.querySelector(".save-btn").removeAttribute("name");

    modal.style.display = "flex";
  };

  // SUBMIT
  form.addEventListener("submit", function (e) {
    // CREATE MODE -> let PHP handle it
    if (formMode !== "edit") return;

    // EDIT MODE -> use API PUT
    e.preventDefault();

    const payload = {
      vaccine_name: form.querySelector('[name="vaccine_name"]').value,
      description: form.querySelector('[name="description"]').value,
      status: Number(form.querySelector('[name="status"]').value) // send 1/0 as number
    };

    fetch(`http://localhost:8080/vaccine/update/${editingVaccineId}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    })
    .then(async (res) => {
      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        alert(data.message || "Failed to update vaccine.");
        return;
      }

      alert(data.message || "Vaccine updated successfully!");
      location.reload();
    })
    .catch((err) => {
      console.error(err);
      alert("Error connecting to API.");
    });
  });

  function deleteVaccine(vaccineId) {
  if (!confirm("Are you sure you want to delete this vaccine? This will also delete related schedules.")) return;

  fetch(`http://localhost:8080/vaccine/delete/${vaccineId}`, {
    method: "DELETE"
  })
  .then(async (res) => {
    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
      alert(data.message || "Failed to delete vaccine.");
      return;
    }

    alert(data.message || "Vaccine deleted successfully!");
    location.reload();
  })
  .catch((err) => {
    console.error(err);
    alert("Error connecting to API.");
  });
}
</script>
