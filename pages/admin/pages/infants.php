<?php
include __DIR__ . '/../auth/auth.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_infant"])) {
    // ✅ Define upload directory
    $uploadDir = __DIR__ . '/../../../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $birthDocPath = null;
    $profilePicPath = null;

    // ✅ Handle uploaded birth document
    if (!empty($_FILES["birth_document"]["name"])) {
        $birthDocPath = 'uploads/documents/' . basename($_FILES["birth_document"]["name"]);
        move_uploaded_file($_FILES["birth_document"]["tmp_name"], __DIR__ . '/../../../' . $birthDocPath);
    }

    // ✅ Handle uploaded profile picture
    if (!empty($_FILES["profile_pic"]["name"])) {
        $profilePicPath = 'uploads/photos/' . basename($_FILES["profile_pic"]["name"]);
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], __DIR__ . '/../../../' . $profilePicPath);
    }

    // ✅ Prepare data to send to API
    $data = [
        "firstname" => $_POST["firstname"],
        "middlename" => $_POST["middlename"],
        "lastname" => $_POST["lastname"],
        "dob" => $_POST["dob"],
        "age_year" => $_POST["age_year"],
        "age_month" => $_POST["age_month"],
        "purok_id" => $_POST["purok_id"],
        "home_add" => $_POST["home_add"],
        "f_firstname" => $_POST["f_firstname"],
        "f_middlename" => $_POST["f_middlename"],
        "f_lastname" => $_POST["f_lastname"],
        "m_firstname" => $_POST["m_firstname"],
        "m_middlename" => $_POST["m_middlename"],
        "m_lastname" => $_POST["m_lastname"],
        "f_contact" => $_POST["f_contact"],
        "m_contact" => $_POST["m_contact"],
        "birth_document" => $birthDocPath,
        "profile_pic" => $profilePicPath
    ];

    // ✅ Send to backend API
    $url = "http://localhost:8080/infant/add";

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
        $msg = isset($response["message"]) ? $response["message"] : "Infant added successfully!";
        echo "<script>alert('".$msg."'); window.location.href=window.location.href;</script>";
    }
}
?>

<link rel="stylesheet" href="../../../src/admin/admin.css">

<div class="admin-layout">
  <div class="sidebar-container">
    <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>
  </div>

  <div class="main-content">
    <div class="infant-content">
      <div class="infant-header">
        <h1>Infant Database</h1>
        <button class="add-btn" id="openInfantModal">Add</button>
      </div>

      <div class="infant-list">
        <?php
        // ✅ Fetch infants from backend API
        $apiUrl = "http://localhost:8080/infant/get";
        $infantData = @file_get_contents($apiUrl);
        $infants = $infantData ? json_decode($infantData, true) : [];

        if (!empty($infants)):
          foreach ($infants as $infant):
            $name = htmlspecialchars($infant["firstname"] . " " . $infant["lastname"]);
            $purok = htmlspecialchars($infant["purok_id"]);
            $photo = !empty($infant["profile_pic"]) ? "../../../" . $infant["profile_pic"] : "https://via.placeholder.com/150";
        ?>
          <div class="infant-card">
            <img src="<?= $photo ?>" alt="<?= $name ?>">
            <div class="infant-info">
              <h3><?= $name ?></h3>
              <p>Purok: <?= $purok ?></p>
              <button class="view-btn">View</button>
            </div>
          </div>
        <?php
          endforeach;
        else:
          echo "<p>No infant records found.</p>";
        endif;
        ?>
      </div>
    </div>
  </div>
</div>

<!-- ==============================
     ADD INFANT MODAL
================================= -->
<div class="modal" id="addInfantModal">
  <div class="modal-content">
    <h2>Add New Infant Record</h2>
    <form method="POST" enctype="multipart/form-data" class="modal-form">

      <!-- Infant Information -->
      <div class="modal-row">
        <input type="text" name="firstname" placeholder="First Name" required>
        <input type="text" name="middlename" placeholder="Middle Name">
        <input type="text" name="lastname" placeholder="Last Name" required>
      </div>

      <div class="modal-row">
        <input type="date" name="dob" required>
        <input type="number" name="age_year" placeholder="Age (Years)">
        <input type="number" name="age_month" placeholder="Age (Months)">
      </div>

      <div class="modal-row">
        <input type="text" name="purok_id" placeholder="Purok ID" required>
        <input type="text" name="home_add" placeholder="Home Address">
      </div>

      <!-- Father Information -->
      <h3>Father Information</h3>
      <div class="modal-row">
        <input type="text" name="f_firstname" placeholder="Father First Name">
        <input type="text" name="f_middlename" placeholder="Father Middle Name">
        <input type="text" name="f_lastname" placeholder="Father Last Name">
        <input type="text" name="f_contact" placeholder="Father Contact Number">
      </div>

      <!-- Mother Information -->
      <h3>Mother Information</h3>
      <div class="modal-row">
        <input type="text" name="m_firstname" placeholder="Mother First Name">
        <input type="text" name="m_middlename" placeholder="Mother Middle Name">
        <input type="text" name="m_lastname" placeholder="Mother Last Name">
        <input type="text" name="m_contact" placeholder="Mother Contact Number">
      </div>

      <!-- Uploads -->
      <div class="modal-row">
        <label>Upload Birth Document:</label>
        <input type="file" name="birth_document" accept=".pdf,.jpg,.png" required>
      </div>

      <div class="modal-row">
        <label>Upload Infant Photo:</label>
        <input type="file" name="profile_pic" accept="image/*" required>
      </div>

      <!-- Actions -->
      <div class="modal-actions">
        <button type="submit" name="create_infant" class="add-submit">Add</button>
        <button type="button" id="closeInfantModal" class="cancel-btn">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- ==============================
     MODAL SCRIPT
================================= -->
<script>
const infantModal = document.getElementById("addInfantModal");
const openInfantBtn = document.getElementById("openInfantModal");
const closeInfantBtn = document.getElementById("closeInfantModal");

openInfantBtn.addEventListener("click", () => {
  infantModal.style.display = "flex";
});
closeInfantBtn.addEventListener("click", () => {
  infantModal.style.display = "none";
});
window.addEventListener("click", (e) => {
  if (e.target === infantModal) infantModal.style.display = "none";
});
</script>