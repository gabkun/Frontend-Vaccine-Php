<?php
include __DIR__ . '/../auth/auth.php';

/* ============================================================
    👉 CREATE VACCINE (POST)
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

    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo "<script>alert('Error connecting to API');</script>";
    } else {
        $response = json_decode($result, true);
        $msg = $response["message"] ?? "Vaccine added successfully!";
        echo "<script>alert('$msg'); location.reload();</script>";
    }
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

    // FIX: API returns array directly
    if (is_array($decoded)) {
        $vaccineData = $decoded;
    }
}

?>


<!-- ============================================================
                    FRONTEND HTML
============================================================ -->
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

                <?php if (!empty($vaccineData)): ?>
                    <?php foreach ($vaccineData as $v): ?>
                        <div class="vaccine-card">
                            <img src="../../../src/assets/vaccine_sample.png" alt="<?= htmlspecialchars($v['vaccine_name']) ?>">
                            <p><strong><?= htmlspecialchars($v['vaccine_name']) ?></strong></p>
                            <small><?= htmlspecialchars($v['description'] ?? '') ?></small>
                            <p>Status: <?= $v['status'] == 1 ? "Active" : "Inactive" ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No vaccines found.</p>
                <?php endif; ?>

            </div>
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

    openBtn.addEventListener("click", () => {
        modal.style.display = "flex";
    });

    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
    });
</script>
