<?php
include __DIR__ . '/../auth/auth.php';

/* =========================================================
   ✅ HELPERS
========================================================= */
function ensureDir($dir) {
    if (!is_dir($dir)) mkdir($dir, 0777, true);
}

function uploadFile($fileKey, $destDirAbs, $destDirRel, $allowedExt = []) {
    if (empty($_FILES[$fileKey]["name"])) return [null, null];

    if (!isset($_FILES[$fileKey]["error"]) || $_FILES[$fileKey]["error"] !== UPLOAD_ERR_OK) {
        $err = $_FILES[$fileKey]["error"] ?? "unknown";
        return [null, "Upload error ($fileKey): " . $err];
    }

    $original = basename($_FILES[$fileKey]["name"]);
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

    if (!empty($allowedExt) && !in_array($ext, $allowedExt, true)) {
        return [null, "Invalid file type for $fileKey. Allowed: " . implode(", ", $allowedExt)];
    }

    $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($original, PATHINFO_FILENAME));
    $newName = $safeBase . "_" . date("Ymd_His") . "_" . bin2hex(random_bytes(3)) . "." . $ext;

    ensureDir($destDirAbs);

    $targetAbs = rtrim($destDirAbs, "/") . "/" . $newName;
    if (!move_uploaded_file($_FILES[$fileKey]["tmp_name"], $targetAbs)) {
        return [null, "Failed to move uploaded file for $fileKey."];
    }

    return [rtrim($destDirRel, "/") . "/" . $newName, null];
}

function getHttpStatusFromHeaders($headers) {
    if (!is_array($headers)) return 0;
    foreach ($headers as $h) {
        if (preg_match('#HTTP/\d\.\d\s+(\d+)#', $h, $m)) return (int)$m[1];
    }
    return 0;
}

/* =========================================================
   ✅ UPLOAD DIRS
========================================================= */
$baseUploadDir = __DIR__ . '/../../../uploads/';
ensureDir($baseUploadDir);

$docDirAbs   = $baseUploadDir . 'documents/';
$photoDirAbs = $baseUploadDir . 'photos/';

$docDirRel   = 'uploads/documents';
$photoDirRel = 'uploads/photos';

/* =========================================================
   ✅ HANDLE CREATE MIDWIFE (POST)
========================================================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_midwife"])) {

    [$validDocumentPath, $docErr] = uploadFile("valid_document", $docDirAbs, $docDirRel, ["pdf","jpg","jpeg","png"]);
    [$photoPath, $photoErr]       = uploadFile("photo", $photoDirAbs, $photoDirRel, ["jpg","jpeg","png","webp"]);

    if ($docErr || $photoErr) {
        $msg = trim(($docErr ? $docErr : "") . " " . ($photoErr ? $photoErr : ""));
        echo "<script>alert(" . json_encode($msg) . ");</script>";
    } else {

        $data = [
            "username" => $_POST["username"] ?? "",
            "password" => $_POST["password"] ?? "",
            "firstname" => $_POST["firstname"] ?? "",
            "middlename" => $_POST["middlename"] ?? "",
            "lastname" => $_POST["lastname"] ?? "",
            "suffix" => $_POST["suffix"] ?? "",
            "age" => $_POST["age"] ?? "",
            "dob" => $_POST["dob"] ?? "",
            "gender" => $_POST["gender"] ?? "",
            "address" => $_POST["address"] ?? "",
            "email" => $_POST["email"] ?? "",
            "phone" => $_POST["phone"] ?? "",
            "valid_document" => $validDocumentPath,
            "photo" => $photoPath
        ];

        $url = "https://backend-vaccine.onrender.com/midwife/create";
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

        $httpStatus = getHttpStatusFromHeaders($http_response_header ?? []);

        if ($result === FALSE) {
            echo "<script>alert('Error connecting to API.');</script>";
        } else {
            $response = json_decode($result, true);
            $msg = $response["message"] ?? ("API response: " . $result);

            if ($httpStatus < 200 || $httpStatus >= 300) $msg = "HTTP $httpStatus - " . $msg;

            echo "<script>alert(" . json_encode($msg) . "); window.location.href=window.location.href;</script>";
        }
    }
}

/* =========================================================
   ✅ HANDLE UPDATE MIDWIFE (PUT)
========================================================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_midwife"])) {

    $id = $_POST["midwife_id"] ?? "";

    // old paths (keep if user doesn't upload new)
    $oldValid = $_POST["old_valid_document"] ?? null;
    $oldPhoto = $_POST["old_photo"] ?? null;

    // optional new uploads
    [$newValid, $docErr] = uploadFile("edit_valid_document", $docDirAbs, $docDirRel, ["pdf","jpg","jpeg","png"]);
    [$newPhoto, $photoErr] = uploadFile("edit_photo", $photoDirAbs, $photoDirRel, ["jpg","jpeg","png","webp"]);

    if ($docErr || $photoErr) {
        $msg = trim(($docErr ? $docErr : "") . " " . ($photoErr ? $photoErr : ""));
        echo "<script>alert(" . json_encode($msg) . ");</script>";
    } else {

        $validToSend = $newValid ?: $oldValid;
        $photoToSend = $newPhoto ?: $oldPhoto;

        $data = [
            "username" => $_POST["username"] ?? "",
            "firstname" => $_POST["firstname"] ?? "",
            "middlename" => $_POST["middlename"] ?? "",
            "lastname" => $_POST["lastname"] ?? "",
            "dob" => $_POST["dob"] ?? "",
            "age" => $_POST["age"] ?? "",
            "gender" => $_POST["gender"] ?? "",
            "address" => $_POST["address"] ?? "",
            "email" => $_POST["email"] ?? "",
            "phone" => $_POST["phone"] ?? "",
            "valid_document" => $validToSend,
            "photo" => $photoToSend,
        ];

        // ✅ only include password if user typed one
        $pw = trim($_POST["password"] ?? "");
        if ($pw !== "") $data["password"] = $pw;

        $url = "https://backend-vaccine.onrender.com/midwife/update/" . urlencode($id);

        $options = [
            "http" => [
                "header"  => "Content-Type: application/json\r\n",
                "method"  => "PUT",
                "content" => json_encode($data),
                "ignore_errors" => true
            ]
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        $httpStatus = getHttpStatusFromHeaders($http_response_header ?? []);

        if ($result === FALSE) {
            echo "<script>alert('Error connecting to API (update).');</script>";
        } else {
            $response = json_decode($result, true);
            $msg = $response["message"] ?? ("API response: " . $result);

            if ($httpStatus < 200 || $httpStatus >= 300) $msg = "HTTP $httpStatus - " . $msg;

            echo "<script>alert(" . json_encode($msg) . "); window.location.href=window.location.href;</script>";
        }
    }
}

/* =========================================================
   ✅ HANDLE DELETE MIDWIFE (DELETE)
========================================================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_midwife"])) {

    $id = $_POST["midwife_id"] ?? "";
    $url = "https://backend-vaccine.onrender.com/midwife/delete/" . urlencode($id);

    $options = [
        "http" => [
            "method"  => "DELETE",
            "ignore_errors" => true
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    $httpStatus = getHttpStatusFromHeaders($http_response_header ?? []);

    if ($result === FALSE) {
        echo "<script>alert('Error connecting to API (delete).');</script>";
    } else {
        $response = json_decode($result, true);
        $msg = $response["message"] ?? ("API response: " . $result);

        if ($httpStatus < 200 || $httpStatus >= 300) $msg = "HTTP $httpStatus - " . $msg;

        echo "<script>alert(" . json_encode($msg) . "); window.location.href=window.location.href;</script>";
    }
}
?>

<link rel="stylesheet" href="../../../src/admin/admin.css">

<div class="admin-layout">
  <div class="sidebar-container">
    <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>
  </div>

  <div class="main-content">
    <div class="midwife-content">
      <div class="midwife-header">
        <h1>Midwife Database</h1>
        <button class="add-btn" id="openAddModal">Add</button>
      </div>

      <div class="midwife-list">
        <?php
          $apiUrl = "https://backend-vaccine.onrender.com/midwife/get";
          $midwives = json_decode(@file_get_contents($apiUrl), true);

          if (!empty($midwives)):
            foreach ($midwives as $midwife):

              $photoAbs = !empty($midwife['photo'])
                ? "../../../" . ltrim($midwife['photo'], "/")
                : "https://via.placeholder.com/80";

              // for edit modal prefill
              $id = $midwife['id'] ?? "";
              $fullname = trim(($midwife['firstname'] ?? '').' '.($midwife['middlename'] ?? '').' '.($midwife['lastname'] ?? ''));
        ?>
          <div class="midwife-card">

            <img src="<?= htmlspecialchars($photoAbs) ?>"
                 alt="<?= htmlspecialchars($fullname) ?>">

            <div class="midwife-info">
              <h3><?= htmlspecialchars($fullname) ?></h3>
              <p>Email: <?= htmlspecialchars($midwife['email'] ?? '') ?></p>
              <p>Phone: <?= htmlspecialchars($midwife['phone'] ?? '') ?></p>
              <p>Address: <?= htmlspecialchars($midwife['address'] ?? '') ?></p>
              <p>Gender: <?= htmlspecialchars($midwife['gender'] ?? '') ?></p>
              <p>Age: <?= htmlspecialchars($midwife['age'] ?? '') ?></p>
            </div>

            <div class="card-actions">
              <!-- ✅ EDIT BUTTON carries data-* -->
              <button
                class="edit-btn"
                type="button"
                data-id="<?= htmlspecialchars($id) ?>"
                data-username="<?= htmlspecialchars($midwife['username'] ?? '') ?>"
                data-firstname="<?= htmlspecialchars($midwife['firstname'] ?? '') ?>"
                data-middlename="<?= htmlspecialchars($midwife['middlename'] ?? '') ?>"
                data-lastname="<?= htmlspecialchars($midwife['lastname'] ?? '') ?>"
                data-suffix="<?= htmlspecialchars($midwife['suffix'] ?? '') ?>"
                data-age="<?= htmlspecialchars($midwife['age'] ?? '') ?>"
                data-dob="<?= htmlspecialchars($midwife['dob'] ?? '') ?>"
                data-gender="<?= htmlspecialchars($midwife['gender'] ?? '') ?>"
                data-address="<?= htmlspecialchars($midwife['address'] ?? '') ?>"
                data-email="<?= htmlspecialchars($midwife['email'] ?? '') ?>"
                data-phone="<?= htmlspecialchars($midwife['phone'] ?? '') ?>"
                data-valid_document="<?= htmlspecialchars($midwife['valid_document'] ?? '') ?>"
                data-photo="<?= htmlspecialchars($midwife['photo'] ?? '') ?>"
              >Edit</button>

              <!-- ✅ DELETE uses hidden form -->
              <form method="POST" class="deleteForm" style="display:inline;">
                <input type="hidden" name="midwife_id" value="<?= htmlspecialchars($id) ?>">
                <button class="delete-btn" type="submit" name="delete_midwife">Delete</button>
              </form>
            </div>
          </div>
        <?php
            endforeach;
          else:
        ?>
          <p>No midwives found.</p>
        <?php endif; ?>
      </div>

      <!-- =========================
           ADD MIDWIFE MODAL
      ========================== -->
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
            </div>

            <div class="modal-row file-upload-row">
              <div class="file-group">
                <label for="valid_document">Valid ID / License Document</label>
                <small>Accepted: PDF, JPG, PNG</small>
                <input type="file" id="valid_document" name="valid_document" accept=".pdf,.jpg,.jpeg,.png" required>
              </div>

              <div class="file-group">
                <label for="photo">Profile Picture</label>
                <small>Image Only (JPG, PNG)</small>
                <input type="file" id="photo" name="photo" accept="image/*" required>
              </div>
            </div>

            <div class="modal-row">
              <input type="text" name="username" placeholder="Username" required>
              <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="modal-actions">
              <button type="submit" name="create_midwife" class="add-submit">Add</button>
              <button type="button" id="closeAddModal" class="cancel-btn">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <!-- =========================
           EDIT MIDWIFE MODAL (same layout)
      ========================== -->
      <div class="modal" id="editMidwifeModal">
        <div class="modal-content">
          <h2>Edit Midwife</h2>

          <form method="POST" enctype="multipart/form-data" class="modal-form" id="editForm">

            <input type="hidden" name="midwife_id" id="edit_midwife_id">
            <input type="hidden" name="old_valid_document" id="edit_old_valid_document">
            <input type="hidden" name="old_photo" id="edit_old_photo">

            <div class="modal-row">
              <input type="text" name="firstname" id="edit_firstname" placeholder="First Name" required>
              <input type="text" name="lastname" id="edit_lastname" placeholder="Last Name" required>
              <input type="text" name="middlename" id="edit_middlename" placeholder="Middle Name">
              <input type="text" name="suffix" id="edit_suffix" placeholder="Suffix">
            </div>

            <div class="modal-row">
              <input type="number" name="age" id="edit_age" placeholder="Age">
              <input type="date" name="dob" id="edit_dob" required>
              <input type="text" name="gender" id="edit_gender" placeholder="Gender" required>
              <input type="text" name="address" id="edit_address" placeholder="Personal Address" required>
            </div>

            <div class="modal-row">
              <input type="email" name="email" id="edit_email" placeholder="Email Address" required>
              <input type="text" name="phone" id="edit_phone" placeholder="Phone Number" required>
            </div>

            <div class="modal-row file-upload-row">
              <div class="file-group">
                <label for="edit_valid_document">Valid ID / License Document</label>
                <small>Upload only if you want to replace</small>
                <input type="file" id="edit_valid_document" name="edit_valid_document" accept=".pdf,.jpg,.jpeg,.png">
              </div>

              <div class="file-group">
                <label for="edit_photo">Profile Picture</label>
                <small>Upload only if you want to replace</small>
                <input type="file" id="edit_photo" name="edit_photo" accept="image/*">
              </div>
            </div>

            <div class="modal-row">
              <input type="text" name="username" id="edit_username" placeholder="Username" required>
              <input type="password" name="password" id="edit_password" placeholder="New Password (optional)">
            </div>

            <div class="modal-actions">
              <button type="submit" name="update_midwife" class="add-submit">Update</button>
              <button type="button" id="closeEditModal" class="cancel-btn">Cancel</button>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  // =========================
  // ADD MODAL
  // =========================
  const addModal = document.getElementById("addMidwifeModal");
  const openAddBtn = document.getElementById("openAddModal");
  const closeAddBtn = document.getElementById("closeAddModal");

  openAddBtn.addEventListener("click", () => addModal.style.display = "flex");
  closeAddBtn.addEventListener("click", () => addModal.style.display = "none");

  // =========================
  // EDIT MODAL
  // =========================
  const editModal = document.getElementById("editMidwifeModal");
  const closeEditBtn = document.getElementById("closeEditModal");

  function openEditModal(btn) {
    // Fill values
    document.getElementById("edit_midwife_id").value = btn.dataset.id || "";
    document.getElementById("edit_username").value = btn.dataset.username || "";

    document.getElementById("edit_firstname").value = btn.dataset.firstname || "";
    document.getElementById("edit_middlename").value = btn.dataset.middlename || "";
    document.getElementById("edit_lastname").value = btn.dataset.lastname || "";
    document.getElementById("edit_suffix").value = btn.dataset.suffix || "";

    document.getElementById("edit_age").value = btn.dataset.age || "";
    document.getElementById("edit_dob").value = btn.dataset.dob || "";
    document.getElementById("edit_gender").value = btn.dataset.gender || "";
    document.getElementById("edit_address").value = btn.dataset.address || "";

    document.getElementById("edit_email").value = btn.dataset.email || "";
    document.getElementById("edit_phone").value = btn.dataset.phone || "";

    // keep old file paths (so update keeps them if no new upload)
    document.getElementById("edit_old_valid_document").value = btn.dataset.valid_document || "";
    document.getElementById("edit_old_photo").value = btn.dataset.photo || "";

    // clear file inputs + password
    document.getElementById("edit_valid_document").value = "";
    document.getElementById("edit_photo").value = "";
    document.getElementById("edit_password").value = "";

    editModal.style.display = "flex";
  }

  document.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", () => openEditModal(btn));
  });

  closeEditBtn.addEventListener("click", () => editModal.style.display = "none");

  // =========================
  // CLOSE ON OUTSIDE CLICK
  // =========================
  window.addEventListener("click", (e) => {
    if (e.target === addModal) addModal.style.display = "none";
    if (e.target === editModal) editModal.style.display = "none";
  });

  // =========================
  // DELETE CONFIRM
  // =========================
  document.querySelectorAll(".deleteForm").forEach(form => {
    form.addEventListener("submit", (e) => {
      if (!confirm("Are you sure you want to delete this midwife?")) {
        e.preventDefault();
      }
    });
  });
</script>