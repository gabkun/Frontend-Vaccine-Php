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

    // Check upload errors
    if (!isset($_FILES[$fileKey]["error"]) || $_FILES[$fileKey]["error"] !== UPLOAD_ERR_OK) {
        $err = $_FILES[$fileKey]["error"] ?? "unknown";
        return [null, "Upload error ($fileKey): " . $err];
    }

    $original = basename($_FILES[$fileKey]["name"]);
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

    if (!empty($allowedExt) && !in_array($ext, $allowedExt, true)) {
        return [null, "Invalid file type for $fileKey. Allowed: " . implode(", ", $allowedExt)];
    }

    // ✅ Unique filename to avoid overwrite
    $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($original, PATHINFO_FILENAME));
    $newName = $safeBase . "_" . date("Ymd_His") . "_" . bin2hex(random_bytes(3)) . "." . $ext;

    ensureDir($destDirAbs);

    $targetAbs = rtrim($destDirAbs, "/") . "/" . $newName;
    if (!move_uploaded_file($_FILES[$fileKey]["tmp_name"], $targetAbs)) {
        return [null, "Failed to move uploaded file for $fileKey."];
    }

    // Return relative path (for DB/API usage)
    return [rtrim($destDirRel, "/") . "/" . $newName, null];
}

/* =========================================================
   ✅ HANDLE CREATE MIDWIFE
========================================================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_midwife"])) {

    // Base upload directory
    $baseUploadDir = __DIR__ . '/../../../uploads/';
    ensureDir($baseUploadDir);

    // Subdirectories (ABS paths)
    $docDirAbs   = $baseUploadDir . 'documents/';
    $photoDirAbs = $baseUploadDir . 'photos/';

    // Relative dirs (what you store/send to API)
    $docDirRel   = 'uploads/documents';
    $photoDirRel = 'uploads/photos';

    // ✅ Upload valid document (pdf/jpg/jpeg/png)
    [$validDocumentPath, $docErr] = uploadFile(
        "valid_document",
        $docDirAbs,
        $docDirRel,
        ["pdf", "jpg", "jpeg", "png"]
    );

    // ✅ Upload photo (jpg/jpeg/png/webp)
    [$photoPath, $photoErr] = uploadFile(
        "photo",
        $photoDirAbs,
        $photoDirRel,
        ["jpg", "jpeg", "png", "webp"]
    );

    if ($docErr || $photoErr) {
        $msg = trim(($docErr ? $docErr : "") . " " . ($photoErr ? $photoErr : ""));
        echo "<script>alert(" . json_encode($msg) . ");</script>";
    } else {

        // ✅ Prepare data for API
        // NOTE: You have inputs for suffix, but your API payload didn’t include it.
        // If your API needs it, we send it.
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

        // ✅ capture HTTP status
        $httpStatus = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('#HTTP/\d\.\d\s+(\d+)#', $h, $m)) {
                    $httpStatus = (int)$m[1];
                    break;
                }
            }
        }

        if ($result === FALSE) {
            echo "<script>alert('Error connecting to API.');</script>";
        } else {
            $response = json_decode($result, true);

            // ✅ If API returns validation error, show it
            $msg = $response["message"] ?? ("API response: " . $result);

            // ✅ If status is not 2xx, show status too
            if ($httpStatus < 200 || $httpStatus >= 300) {
                $msg = "HTTP $httpStatus - " . $msg;
            }

            echo "<script>alert(" . json_encode($msg) . "); window.location.href=window.location.href;</script>";
        }
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
        <button class="add-btn" id="openModal">Add</button>
      </div>

      <div class="midwife-list">
        <?php
          $apiUrl = "http://localhost:8080/midwife/get";
          $midwives = json_decode(@file_get_contents($apiUrl), true);

          if (!empty($midwives)):
            foreach ($midwives as $midwife):

              // ✅ Fix display: your API stores relative path like "uploads/photos/..."
              // but your current <img> uses it directly, so browser looks at "current-folder/uploads/..."
              // You want "../../../" prefix.
              $photo = !empty($midwife['photo'])
                ? "../../../" . ltrim($midwife['photo'], "/")
                : "https://via.placeholder.com/80";

        ?>
          <div class="midwife-card">
            <img src="<?= htmlspecialchars($photo) ?>"
                 alt="<?= htmlspecialchars(($midwife['firstname'] ?? '') . ' ' . ($midwife['lastname'] ?? '')) ?>">

            <div class="midwife-info">
              <h3><?= htmlspecialchars(trim(($midwife['firstname'] ?? '') . ' ' . ($midwife['middlename'] ?? '') . ' ' . ($midwife['lastname'] ?? ''))) ?></h3>
              <p>Email: <?= htmlspecialchars($midwife['email'] ?? '') ?></p>
              <p>Phone: <?= htmlspecialchars($midwife['phone'] ?? '') ?></p>
              <p>Address: <?= htmlspecialchars($midwife['address'] ?? '') ?></p>
              <p>Gender: <?= htmlspecialchars($midwife['gender'] ?? '') ?></p>
              <p>Age: <?= htmlspecialchars($midwife['age'] ?? '') ?></p>
            </div>

            <div class="card-actions">
              <button class="edit-btn" type="button">Edit</button>
              <button class="delete-btn" type="button">Delete</button>
            </div>
          </div>
        <?php
            endforeach;
          else:
        ?>
          <p>No midwives found.</p>
        <?php endif; ?>
      </div>

      <!-- ADD MIDWIFE MODAL -->
      <div class="modal" id="addMidwifeModal">
        <div class="modal-content">
          <h2>Add New Midwife</h2>

 <form method="POST" enctype="multipart/form-data" class="modal-form">

  <!-- ================= PERSONAL INFO ================= -->
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

  <!-- ================= CONTACT INFO ================= -->
  <div class="modal-row">
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="text" name="phone" placeholder="Phone Number" required>
  </div>

  <!-- ================= FILE UPLOADS ================= -->
  <div class="modal-row file-upload-row">

    <!-- VALID DOCUMENT -->
    <div class="file-group">
      <label for="valid_document">
        Valid ID / License Document
      </label>
      <small>Accepted: PDF, JPG, PNG</small>
      <input 
        type="file" 
        id="valid_document"
        name="valid_document" 
        accept=".pdf,.jpg,.jpeg,.png" 
        required
      >
    </div>

    <!-- PROFILE PICTURE -->
    <div class="file-group">
      <label for="photo">
        Profile Picture
      </label>
      <small>Image Only (JPG, PNG)</small>
      <input 
        type="file" 
        id="photo"
        name="photo" 
        accept="image/*" 
        required
      >
    </div>

  </div>

  <!-- ================= ACCOUNT INFO ================= -->
  <div class="modal-row">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
  </div>

  <!-- ================= ACTIONS ================= -->
  <div class="modal-actions">
    <button type="submit" name="create_midwife" class="add-submit">Add</button>
    <button type="button" id="closeModal" class="cancel-btn">Cancel</button>
  </div>

</form>

        </div>
      </div>

    </div>
  </div>
</div>

<script>
const modal = document.getElementById("addMidwifeModal");
const openBtn = document.getElementById("openModal");
const closeBtn = document.getElementById("closeModal");

openBtn.addEventListener("click", () => modal.style.display = "flex");
closeBtn.addEventListener("click", () => modal.style.display = "none");

window.addEventListener("click", (e) => {
  if (e.target === modal) modal.style.display = "none";
});
</script>
