<?php 
include __DIR__ . '/../auth/auth.php';

// ===== FETCH INFANTS =====
$infants = [];
$infant_api = "http://localhost:8080/infant/get";
$infant_response = @file_get_contents($infant_api);
if ($infant_response !== FALSE) {
    $infant_data = json_decode($infant_response, true);
    if (is_array($infant_data)) {
        foreach ($infant_data as $infant) {
            $infants[] = [
                'id' => $infant['id'],
                'name' => $infant['firstname'] . ' ' . $infant['middlename'] . ' ' . $infant['lastname']
            ];
        }
    }
}

// ===== FETCH VACCINES =====
$vaccines = [];
$vaccine_api = "http://localhost:8080/vaccine/get";
$vaccine_response = @file_get_contents($vaccine_api);
if ($vaccine_response !== FALSE) {
    $vaccine_data = json_decode($vaccine_response, true);
    if (is_array($vaccine_data)) {
        foreach ($vaccine_data as $vaccine) {
            if ($vaccine['status'] == 1) { // Only active vaccines
                $vaccines[] = [
                    'id' => $vaccine['id'],
                    'name' => $vaccine['vaccine_name']
                ];
            }
        }
    }
}

// ===== FETCH MIDWIVES =====
$midwives = [];
$midwife_api = "http://localhost:8080/midwife/get";
$midwife_response = @file_get_contents($midwife_api);
if ($midwife_response !== FALSE) {
    $midwife_data = json_decode($midwife_response, true);
    if (is_array($midwife_data)) {
        foreach ($midwife_data as $midwife) {
            if ($midwife['status'] == 1) { // Only active midwives
                $midwives[] = [
                    'id' => $midwife['id'],
                    'name' => $midwife['firstname'] . ' ' . $midwife['middlename'] . ' ' . $midwife['lastname']
                ];
            }
        }
    }
}

// ===== HANDLE FORM SUBMISSION =====
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_vaccination"])) {
    $data = [
        "vaccine_id"   => (int)$_POST["vaccine_id"],
        "infant_id"    => (int)$_POST["infant_id"],
        "midwife_id"   => (int)$_POST["midwife_id"],
        "dose_type"    => (string)$_POST["dose_type"],
        "remarks"      => !empty($_POST["remarks"]) ? $_POST["remarks"] : null,
        "scheduled_on" => $_POST["scheduled_on"],
        "status"       => (int)$_POST["status"],
        "completed_at" => !empty($_POST["completed_at"]) ? $_POST["completed_at"] : null
    ];

    $url = "http://localhost:8080/schedule/add";
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
        $msg = $response["message"] ?? "Vaccination schedule created successfully!";
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
<div class="main-content scheduling-content ">
    <h1 class="scheduling-header">Create Vaccination Schedule</h1>

    <form method="POST" class="schedule-form">

        <!-- Infant -->
        <div class="schedule-form-group">
            <label>Infant</label>
            <select name="infant_id" required>
                <option value="">-- Select Infant --</option>
                <?php foreach($infants as $infant): ?>
                    <option value="<?= $infant['id'] ?>"><?= htmlspecialchars($infant['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Vaccine -->
        <div class="schedule-form-group">
            <label>Vaccine</label>
            <select name="vaccine_id" required>
                <option value="">-- Select Vaccine --</option>
                <?php foreach($vaccines as $vaccine): ?>
                    <option value="<?= $vaccine['id'] ?>"><?= htmlspecialchars($vaccine['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Midwife -->
        <div class="schedule-form-group">
            <label>Midwife</label>
            <select name="midwife_id" required>
                <option value="">-- Select Midwife --</option>
                <?php foreach($midwives as $midwife): ?>
                    <option value="<?= $midwife['id'] ?>"><?= htmlspecialchars($midwife['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Dose Type -->
        <div class="schedule-form-group">
            <label>Dose Type</label>
            <select name="dose_type" required>
                <option value="">-- Select Dose --</option>
                <option value="1st Dose">1st Dose</option>
                <option value="2nd Dose">2nd Dose</option>
                <option value="Booster">Booster</option>
            </select>
        </div>

        <!-- Scheduled Date -->
        <div class="schedule-form-group">
            <label>Scheduled Date</label>
            <input type="date" name="scheduled_on" required>
        </div>

        <!-- Status -->
        <div class="schedule-form-group">
            <label>Status</label>
            <select name="status" required>
                <option value="1">Scheduled</option>
                <option value="0">Cancelled</option>
                <option value="2">Completed</option>
            </select>
        </div>

 
        <button type="submit" name="create_vaccination" class="schedule-btn">
            Save Schedule
        </button>

    </form>
</div>
</div>