<?php
include __DIR__ . '/../auth/auth.php';

// ✅ Handle form submission to create new midwife
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_midwife"])) {
    // Handle uploaded files
    $uploadDir = __DIR__ . '/../../../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $validDocumentPath = null;
    $photoPath = null;

    if (!empty($_FILES["valid_document"]["name"])) {
        $validDocumentPath = 'uploads/documents/' . basename($_FILES["valid_document"]["name"]);
        move_uploaded_file($_FILES["valid_document"]["tmp_name"], __DIR__ . '/../../../' . $validDocumentPath);
    }

    if (!empty($_FILES["photo"]["name"])) {
        $photoPath = 'uploads/photos/' . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], __DIR__ . '/../../../' . $photoPath);
    }

    // Prepare data for API
    $data = [
        "username" => $_POST["username"],
        "password" => $_POST["password"],
        "firstname" => $_POST["firstname"],
        "middlename" => $_POST["middlename"],
        "lastname" => $_POST["lastname"],
        "dob" => $_POST["dob"],
        "gender" => $_POST["gender"],
        "address" => $_POST["address"],
        "email" => $_POST["email"],
        "phone" => $_POST["phone"],
        "valid_document" => $validDocumentPath,
        "photo" => $photoPath
    ];

    // API endpoint
    $url = "http://localhost:8080/midwife/create";

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
        $msg = isset($response["message"]) ? $response["message"] : "Midwife added successfully!";
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
    <div class="midwife-content"> 
      <div class="midwife-header">
        <h1>Midwife Database</h1>
        <button class="add-btn" id="openModal">Add</button>
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

<!-- ==============================
     ADD MIDWIFE MODAL COMPONENT
================================= -->
<div class="modal" id="addMidwifeModal">
  <div class="modal-content">
    <h2>Add New Midwife</h2>
    <form method="POST" enctype="multipart/form-data" class="modal-form">
      <div class="modal-row">
        <input type="text" name="firstname" placeholder="First Name" required>
        <input type="text" name="lastname" placeholder="Last Name" required>
        <input type="text" name="middlename" placeholder="Middle Name">
        <input type="text" name="suffix" placeholder="Suffix">
      </div>

      <div class="modal-row">
        <input type="number" name="age" placeholder="Age">
        <input type="date" name="dob" required>
        <input type="text" name="gender" placeholder="Gender" required>
        <input type="text" name="address" placeholder="Personal Address" required>
      </div>

      <div class="modal-row">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="file" name="valid_document" accept=".pdf,.jpg,.png" required>
      </div>

      <div class="modal-row">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="file" name="photo" accept="image/*" required>
      </div>

      <div class="modal-actions">
        <button type="submit" name="create_midwife" class="add-submit">Add</button>
        <button type="button" id="closeModal" class="cancel-btn">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- ==============================
     MODAL SCRIPT
================================= -->
<script>
const modal = document.getElementById("addMidwifeModal");
const openBtn = document.getElementById("openModal");
const closeBtn = document.getElementById("closeModal");

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
