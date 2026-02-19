<?php
include __DIR__ . '/../auth/auth.php';

// =======================
// Handle Create Infant Form
// =======================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_infant"])) {
    // Base upload directory
    $baseUploadDir = __DIR__ . '/../../../uploads/';
    if (!is_dir($baseUploadDir)) mkdir($baseUploadDir, 0777, true);

    // Subdirectories
    $docDir = $baseUploadDir . 'documents/';
    $photoDir = $baseUploadDir . 'photos/';

    if (!is_dir($docDir)) mkdir($docDir, 0777, true);
    if (!is_dir($photoDir)) mkdir($photoDir, 0777, true);

    $birthDocPath = null;
    $profilePicPath = null;

    // Birth document upload
    if (!empty($_FILES["birth_document"]["name"])) {
        $birthDocName = basename($_FILES["birth_document"]["name"]);
        $birthDocTarget = $docDir . $birthDocName;
        if (move_uploaded_file($_FILES["birth_document"]["tmp_name"], $birthDocTarget)) {
            $birthDocPath = 'uploads/documents/' . $birthDocName; // Relative path for API/frontend
        }
    }

    // Profile picture upload
    if (!empty($_FILES["profile_pic"]["name"])) {
        $profilePicName = basename($_FILES["profile_pic"]["name"]);
        $profilePicTarget = $photoDir . $profilePicName;
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $profilePicTarget)) {
            $profilePicPath = 'uploads/photos/' . $profilePicName; // Relative path for API/frontend
        }
    }

    // Prepare data
    $data = [
        "firstname" => $_POST["firstname"],
        "middlename" => $_POST["middlename"],
        "lastname" => $_POST["lastname"],
        "suffix" => $_POST["suffix"],
        "sex" => $_POST["sex"],
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

    // Send to backend API
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

// =======================
// Fetch Puroks
// =======================
$purokApiUrl = "http://localhost:8080/purok/purok";
$purokData = @file_get_contents($purokApiUrl);
$puroks = $purokData ? json_decode($purokData, true) : [];
$purokMap = [];
foreach ($puroks as $p) {
    $purokMap[$p['id']] = $p['purok_name'];
}

// =======================
// Fetch Infants
// =======================
$apiUrl = "http://localhost:8080/infant/get";
$infantData = @file_get_contents($apiUrl);
$infants = $infantData ? json_decode($infantData, true) : [];

$staticAvatar = "uploads/baby-avatar.png"; // default relative path
?>

<link rel="stylesheet" href="../../../src/admin/admin.css">

<div class="admin-layout">
    <div class="sidebar-container">
        <?php include __DIR__ . '/../components/midwife_sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="infant-content">
            <div class="infant-header">
                <h1>Infant Database</h1>
                <button class="add-btn" id="openInfantModal">Add</button>
            </div>

         <div class="infant-table-wrapper">
    <?php if (!empty($infants)): ?>
        <table class="infant-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Full Name</th>
                    <th>Purok</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($infants as $infant):
                    $name = htmlspecialchars($infant["firstname"] . " " . $infant["lastname"]);
                    $purok_id = $infant["purok_id"];
                    $purok_name = isset($purokMap[$purok_id]) ? htmlspecialchars($purokMap[$purok_id]) : "Unknown";
                    $photo = !empty($infant["profile_pic"]) ? "../../../" . $infant["profile_pic"] : $staticAvatar;
                    $document = !empty($infant["birth_document"]) ? "../../../" . $infant["birth_document"] : $staticAvatar;
                ?>
                <tr>
                    <td>
                        <img src="<?= $photo ?>" alt="<?= $name ?>" class="infant-avatar">
                    </td>
                    <td><?= $name ?></td>
                    <td><?= $purok_name ?></td>
                    <td>
<button class="view-btn"
    onclick="openVaccinationModal(
        <?= $infant['id'] ?>,
        '<?= htmlspecialchars($photo, ENT_QUOTES) ?>',
        '<?= htmlspecialchars($document, ENT_QUOTES) ?>'
    )">
    View
</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No infant records found.</p>
    <?php endif; ?>
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
<form method="POST" enctype="multipart/form-data" class="modal-form" id="infantForm">

            <!-- Infant Info -->
            <div class="modal-row">
                <input type="hidden" name="infant_id" id="infant_id">
                <input type="text" name="firstname" placeholder="First Name" required>
                <input type="text" name="middlename" placeholder="Middle Name">
                <input type="text" name="lastname" placeholder="Last Name" required>

                <!-- Suffix Dropdown -->
                <select name="suffix">
                    <option value="">Suffix</option>
                    <option value="Jr.">Jr.</option>
                    <option value="Sr.">Sr.</option>
                    <option value="II">II</option>
                    <option value="III">III</option>
                    <option value="IV">IV</option>
                </select>
            </div>

            <div class="modal-row">
                <!-- Sex Dropdown -->
                <select name="sex" required>
                    <option value="">Select Sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <input type="date" name="dob" required>
                <input type="number" name="age_year" placeholder="Age (Years)">
                <input type="number" name="age_month" placeholder="Age (Months)">
            </div>

            <div class="modal-row">
                <select name="purok_id" required>
                    <option value="" disabled selected>Select Purok</option>
                    <option value="1">Purok Monarch</option>
                    <option value="2">Purok Mainswagon</option>
                    <option value="3">Purok Maliayon</option>
                    <option value="4">Purok Benedicto</option>
                    <option value="5">Purok Tunay</option>
                    <option value="6">Purok Paho</option>
                    <option value="7">Purok Halandumon</option>
                    <option value="8">Purok Mary</option>
                    <option value="9">Purok Hda. Paz</option>
                    <option value="10">Purok Nato</option>
                    <option value="11">Purok Lopues</option>
                    <option value="12">Purok Antawan</option>
                </select>

                <input type="text" name="home_add" placeholder="Home Address">
            </div>

            <!-- Father Info -->
            <h3>Father Information</h3>
            <div class="modal-row">
                <input type="text" name="f_firstname" placeholder="Father First Name">
                <input type="text" name="f_middlename" placeholder="Father Middle Name">
                <input type="text" name="f_lastname" placeholder="Father Last Name">
                <input type="text" name="f_contact" placeholder="Father Contact Number">
            </div>

            <!-- Mother Info -->
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
     VIEW INFANT + VACCINATION MODAL
================================= -->
<div id="viewVaccinationModal" class="vaccination-modal">
    <div class="vaccination-modal-content">

        <!-- HEADER -->
        <div class="vaccination-modal-header">
            <h2>Infant Vaccination Record</h2>
            <span class="vaccination-close" onclick="closeVaccinationModal()">&times;</span>
        </div>

        <!-- BODY -->
        <div class="vaccination-modal-body">

            <!-- INFANT INFO -->
            <div class="infant-info-box">
                <div class="infant-info-grid">
    <img id="profilePic" src="" alt="" width="150">
    <h2 id="infantName"></h2>
    <p>Sex: <span id="infantSex"></span></p>
    <p>Date of Birth: <span id="infantDob"></span></p>
    <p>Age: <span id="infantAge"></span></p>
    <p>Address: <span id="infantPob"></span></p>
    <p>Mother: <span id="infantMother"></span> | Contact: <span id="infantMotherContact"></span></p>
    <p>Father: <span id="infantFather"></span> | Contact: <span id="infantFatherContact"></span></p>
    <img id="birthDocument" src="" alt="" width="200">
</div>

                <!-- ACTION BUTTONS -->
                <div class="infant-action-buttons">
                    <button class="edit-btn" onclick="editInfant()">Edit</button>
                    <button class="delete-btn" onclick="deleteInfant()">Delete</button>
                    <button class="download-btn" onclick="downloadRecord()">Download Record</button>
                <button id="viewBirthCertBtn" class="birth-btn">
                    View Birth Certificate
                </button>
                </div>
            </div>

            <!-- VACCINATION TABLE -->
            <table class="vaccination-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vaccine</th>
                        <th>Dose</th>
                        <th>Midwife Name</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="vaccinationTableBody">
                    <tr>
                        <td colspan="5" class="loading-row">Loading records...</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>



<script>
let formMode = "create"; // create | edit
let editingInfantId = null;

const infantModal = document.getElementById("addInfantModal");
const openInfantBtn = document.getElementById("openInfantModal");
const closeInfantBtn = document.getElementById("closeInfantModal");

document.getElementById("infantForm").addEventListener("submit", function (e) {
    if (formMode !== "edit") return; // let PHP handle CREATE

    e.preventDefault(); // stop normal POST

    const formData = new FormData(this);

    fetch(`http://localhost:8080/infant/update/${editingInfantId}`, {
        method: "PUT",
        body: formData
    })
        .then(res => res.json())
        .then(response => {
            alert(response.message || "Infant updated successfully");
            location.reload();
        })
        .catch(err => {
            console.error(err);
            alert("Failed to update infant");
        });
});

openInfantBtn.addEventListener("click", () => infantModal.style.display = "flex");
closeInfantBtn.addEventListener("click", () => infantModal.style.display = "none");
window.addEventListener("click", (e) => { if (e.target === infantModal) infantModal.style.display = "none"; });

function viewBirthCert(filePath) {
  if (!filePath || filePath.includes('avatar')) {
    alert('No birth certificate uploaded.');
    return;
  }

  const link = document.createElement('a');
  link.href = filePath;
  link.download = filePath.split('/').pop(); // filename
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>
<script>
let currentInfantId = null;

function openVaccinationModal(infantId, profilePic, birthCert) {
    currentInfantId = infantId;

    // Open modal
    document.getElementById("viewVaccinationModal").style.display = "flex";
    document.body.style.overflow = "hidden";

    // Profile picture
    const profilePicElem = document.getElementById("profilePic");
    profilePicElem.src = profilePic || "../../../assets/img/logo.png";
    profilePicElem.alt = "Infant Profile Picture";

    // Birth certificate download button
    const birthBtn = document.getElementById("viewBirthCertBtn");

    if (birthCert && !birthCert.includes("logo.png")) {
        birthBtn.style.display = "inline-block";
        birthBtn.onclick = () => downloadBirthCert(birthCert);
    } else {
        birthBtn.style.display = "none";
    }

    // Load rest via API
    loadInfantInfo(infantId);
    loadVaccinationRecords(infantId);
}

function downloadBirthCert(filePath) {
    const link = document.createElement("a");
    link.href = filePath;
    link.download = filePath.split("/").pop();
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/* ======================
   INFANT INFORMATION
====================== */
function loadInfantInfo(infantId) {
    fetch(`http://localhost:8080/infant/infant/profile/${infantId}`)
        .then(res => res.json())
        .then(data => {
            // FULL NAME
            document.getElementById("infantName").textContent =
                `${data.firstname} ${data.middlename ?? ""} ${data.lastname} ${data.suffix ?? ""}`.trim();

            // SEX
            document.getElementById("infantSex").textContent = data.sex;

            // DATE OF BIRTH
            document.getElementById("infantDob").textContent =
                new Date(data.dob).toLocaleDateString();

            // AGE
            document.getElementById("infantAge").textContent =
                `${data.age_year} years, ${data.age_month} months`;

            // HOME ADDRESS + PUROK
            document.getElementById("infantPob").textContent =
                `${data.home_add} (${data.purok_name})`;

            // MOTHER
            document.getElementById("infantMother").textContent =
                `${data.m_firstname} ${data.m_middlename ?? ""} ${data.m_lastname}`.trim();
            document.getElementById("infantMotherContact").textContent = data.m_contact ?? "N/A";

            // FATHER
            document.getElementById("infantFather").textContent =
                `${data.f_firstname} ${data.f_middlename ?? ""} ${data.f_lastname}`.trim();
            document.getElementById("infantFatherContact").textContent = data.f_contact ?? "N/A";

            // BIRTH DOCUMENT stays dynamic if you want
            const birthDocElem = document.getElementById("birthDocument");
            if (data.birth_document && data.birth_document !== "") {
                birthDocElem.src = `http://localhost:8080/${data.birth_document}`;
                birthDocElem.alt = "Birth Document";
            } else {
                birthDocElem.src = "../../../assets/img/logo.png"; // fallback
                birthDocElem.alt = "No Birth Document";
            }


        })
        .catch(err => {
            console.error(err);
            alert("Failed to load infant information");
        });
}

/* ======================
   VACCINATION RECORDS
====================== */
function loadVaccinationRecords(infantId) {
    const tbody = document.getElementById("vaccinationTableBody");
    tbody.innerHTML = `<tr><td colspan="5" class="loading-row">Loading records...</td></tr>`;

    fetch(`http://localhost:8080/schedule/vaccination/infant/${infantId}`)
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="empty-row">No records found</td></tr>`;
                return;
            }

            tbody.innerHTML = "";
            data.forEach(row => {
                const date = row.completed_at
                    ? new Date(row.completed_at).toLocaleDateString()
                    : new Date(row.scheduled_on).toLocaleDateString();

                tbody.innerHTML += `
                    <tr>
                        <td>${date}</td>
                        <td>${row.vaccine_name}</td>
                        <td>${row.dose_type}</td>
                        <td>${row.midwife_name}</td>
                        <td>${row.remarks ?? "-"}</td>
                    </tr>
                `;
            });
        });
}

/* ======================
   ACTION BUTTONS
====================== */
function editInfant() {
    closeVaccinationModal();
    formMode = "edit";
    editingInfantId = currentInfantId;

    // Open modal
    const modal = document.getElementById("addInfantModal");
    modal.style.display = "flex";

    // Change modal title & button
    modal.querySelector("h2").textContent = "Edit Infant Record";
    modal.querySelector(".add-submit").textContent = "Update";

    // Fetch infant data
    fetch(`http://localhost:8080/infant/infant/profile/${editingInfantId}`)
        .then(res => res.json())
        .then(data => {
            // BASIC INFO
            document.querySelector('[name="firstname"]').value = data.firstname ?? "";
            document.querySelector('[name="middlename"]').value = data.middlename ?? "";
            document.querySelector('[name="lastname"]').value = data.lastname ?? "";
            document.querySelector('[name="suffix"]').value = data.suffix ?? "";
            document.querySelector('[name="sex"]').value = data.sex ?? "";
document.querySelector('[name="dob"]').value =
    data.dob ? data.dob.split("T")[0] : "";
            document.querySelector('[name="age_year"]').value = data.age_year ?? "";
            document.querySelector('[name="age_month"]').value = data.age_month ?? "";
            document.querySelector('[name="purok_id"]').value = data.purok_id ?? "";
            document.querySelector('[name="home_add"]').value = data.home_add ?? "";

            // FATHER
            document.querySelector('[name="f_firstname"]').value = data.f_firstname ?? "";
            document.querySelector('[name="f_middlename"]').value = data.f_middlename ?? "";
            document.querySelector('[name="f_lastname"]').value = data.f_lastname ?? "";
            document.querySelector('[name="f_contact"]').value = data.f_contact ?? "";

            // MOTHER
            document.querySelector('[name="m_firstname"]').value = data.m_firstname ?? "";
            document.querySelector('[name="m_middlename"]').value = data.m_middlename ?? "";
            document.querySelector('[name="m_lastname"]').value = data.m_lastname ?? "";
            document.querySelector('[name="m_contact"]').value = data.m_contact ?? "";

            // Store ID
            document.getElementById("infant_id").value = editingInfantId;
        })
        .catch(err => {
            console.error(err);
            alert("Failed to load infant data for editing");
        });
}

function deleteInfant() {
    if (!confirm("Are you sure you want to delete this infant record?")) return;

    fetch(`http://localhost:8080/infant/${currentInfantId}`, { method: "DELETE" })
        .then(res => res.json())
        .then(() => {
            alert("Infant deleted successfully");
            location.reload();
        });
}



function viewBirthCert() {
    window.open(`/infant/${currentInfantId}/birth-certificate`, "_blank");
}

function closeVaccinationModal() {
    document.getElementById("viewVaccinationModal").style.display = "none";
    document.body.style.overflow = "auto";
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
async function downloadRecord() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF("p", "mm", "a4");

  // =========================
  // HELPERS
  // =========================
  const pageW = doc.internal.pageSize.getWidth();
  const pageH = doc.internal.pageSize.getHeight();

  const M = 15;                     // margin
  const contentW = pageW - M * 2;

  const COLORS = {
    primary: [22, 101, 52],         // deep green
    light:   [240, 253, 244],       // very light green
    border:  [30, 41, 59],          // slate
    gray:    [71, 85, 105],
    line:    [203, 213, 225],
    headerBg:[220, 252, 231]
  };

  const setColor = (type, rgb) => {
    if (type === "fill") doc.setFillColor(...rgb);
    if (type === "draw") doc.setDrawColor(...rgb);
    if (type === "text") doc.setTextColor(...rgb);
  };

  let y = 18;
  const ensurePageSpace = (needed) => {
    if (y + needed > pageH - 18) {
      doc.addPage();
      y = 18;

      // frame on new page
      doc.setLineWidth(0.6);
      setColor("draw", COLORS.line);
      doc.roundedRect(M - 2, 10, pageW - (M - 2) * 2, pageH - 18, 3, 3);
    }
  };

  const split = (text, maxW) => doc.splitTextToSize(String(text ?? ""), maxW);
  const safeText = (id) => (document.getElementById(id)?.textContent || "").trim();

  async function loadImageAsDataURL(url) {
    const res = await fetch(url);
    const blob = await res.blob();
    return await new Promise((resolve) => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.readAsDataURL(blob);
    });
  }

  // =========================
  // DATA
  // =========================
  const name = safeText("infantName");
  const sex = safeText("infantSex");
  const dob = safeText("infantDob");
  const age = safeText("infantAge");
  const address = safeText("infantPob");
  const mother = safeText("infantMother");
  const motherContact = safeText("infantMotherContact");
  const father = safeText("infantFather");
  const fatherContact = safeText("infantFatherContact");

  // =========================
  // OUTER "BOOKLET" FRAME
  // =========================
  doc.setLineWidth(0.6);
  setColor("draw", COLORS.line);
  doc.roundedRect(M - 2, 10, pageW - (M - 2) * 2, pageH - 18, 3, 3);

  // =========================
  // HEADER
  // =========================
  let logoDataUrl = null;
  try {
    logoDataUrl = await loadImageAsDataURL("../../../assets/img/logo.png");
  } catch (e) {
    logoDataUrl = null;
  }

  setColor("fill", COLORS.headerBg);
  setColor("draw", COLORS.line);
  doc.roundedRect(M, y - 8, contentW, 34, 2.5, 2.5, "FD");

  if (logoDataUrl) {
    doc.addImage(logoDataUrl, "PNG", M + 4, y - 4, 22, 22);
  }

  doc.setFont("helvetica", "bold");
  setColor("text", COLORS.border);
  doc.setFontSize(12);

  const headerX = pageW / 2 + 10;
  doc.text("Republic of the Philippines", headerX, y, { align: "center" });

  doc.setFontSize(11);
  doc.text("Province of Negros Occidental", headerX, y + 6, { align: "center" });
  doc.text("Municipality of Murcia", headerX, y + 12, { align: "center" });
  doc.text("Barangay Canlandog", headerX, y + 18, { align: "center" });

  // Title bar
  y += 30;
  ensurePageSpace(20);

  setColor("fill", COLORS.primary);
  doc.roundedRect(M, y, contentW, 12, 2.5, 2.5, "F");

  doc.setFont("helvetica", "bold");
  doc.setFontSize(14);
  setColor("text", [255, 255, 255]);
  doc.text("INFANT VACCINATION RECORD", pageW / 2, y + 8, { align: "center" });

  y += 18;

  // =========================
  // INFANT DETAILS CARD (FIXED: NO OVERPAINT)
  // =========================
  ensurePageSpace(70);

  const lineH = 5.2;

  const cardTopY = y;
  const leftX = M + 6;
  const rightX = M + contentW / 2 + 5;
  const maxLeft = contentW / 2 - 16;
  const maxRight = contentW / 2 - 16;

  const fullX = leftX;
  const fullMax = contentW - 20;

  // Pre-split full-width lines for height calc + later render
  const addressLines = split(address || "-", fullMax - 28);
  const motherLines = split(`${mother}  |  Contact: ${motherContact}` || "-", fullMax - 28);
  const fatherLines = split(`${father}  |  Contact: ${fatherContact}` || "-", fullMax - 28);

  const calcKVHeight = (value, maxW) => split(value || "-", maxW).length * lineH;

  const twoColHeight = Math.max(
    calcKVHeight(name, maxLeft) + calcKVHeight(dob, maxLeft),
    calcKVHeight(sex, maxRight) + calcKVHeight(age, maxRight)
  );

  const fullRowsHeight =
    (addressLines.length * lineH + 1) +
    (motherLines.length * lineH + 1) +
    (fatherLines.length * lineH + 1);

  const baseHeader = 16; // header title + divider
  const paddingBottom = 8;

  const cardH = Math.max(60, baseHeader + twoColHeight + fullRowsHeight + paddingBottom);

  // Draw the card ONCE
  setColor("fill", COLORS.light);
  setColor("draw", COLORS.line);
  doc.roundedRect(M, cardTopY, contentW, cardH, 2.5, 2.5, "FD");

  // Card title
  doc.setFont("helvetica", "bold");
  doc.setFontSize(11);
  setColor("text", COLORS.border);
  doc.text("INFANT INFORMATION", M + 6, cardTopY + 8);

  // Divider
  doc.setLineWidth(0.3);
  setColor("draw", COLORS.line);
  doc.line(M + 6, cardTopY + 11, M + contentW - 6, cardTopY + 11);

  // Content
  let iy = cardTopY + 18;

  const kv = (label, value, x, maxW, yPos) => {
    doc.setFont("helvetica", "bold");
    setColor("text", COLORS.primary); // label color
    doc.text(label, x, yPos);

    doc.setFont("helvetica", "normal");
    setColor("text", COLORS.border); // <<< make value DARK (visible)
    const lines = split(value || "-", maxW);
    doc.text(lines, x + 22, yPos);
    return yPos + lines.length * lineH;
  };

  // row 1
  let iyL = cardTopY + 18;
  let iyR = cardTopY + 18;
  iyL = kv("Name:", name, leftX, maxLeft, iyL);
  iyR = kv("Sex:", sex, rightX, maxRight, iyR);

  let baseY = Math.max(iyL, iyR) + 1;

  // row 2
  iyL = kv("Birthdate:", dob, leftX, maxLeft, baseY);
  iyR = kv("Age:", age, rightX, maxRight, baseY);

  iy = Math.max(iyL, iyR) + 1;

  const fullLine = (label, linesArr) => {
    doc.setFont("helvetica", "bold");
    setColor("text", COLORS.border);
    doc.text(label, fullX, iy);

    doc.setFont("helvetica", "normal");
    setColor("text", COLORS.border); // <<< make value DARK (visible)
    doc.text(linesArr, fullX + 28, iy);

    iy += linesArr.length * lineH + 1;
  };

  fullLine("Address:", addressLines);
  fullLine("Mother:", motherLines);
  fullLine("Father:", fatherLines);

  // move below card
  y = cardTopY + cardH + 10;

  // =========================
  // TABLE TITLE
  // =========================
  ensurePageSpace(20);

  doc.setFont("helvetica", "bold");
  doc.setFontSize(11);
  setColor("text", COLORS.border);
  doc.text("VACCINATION HISTORY", M, y);
  y += 6;

  // =========================
  // TABLE (DYNAMIC HEIGHT + COLORS)
  // =========================
  const col = {
    date: 22,
    vaccine: 50,
    dose: 18,
    midwife: 35,
    remarks: contentW - (22 + 50 + 18 + 35)
  };

  const colX = {
    date: M,
    vaccine: M + col.date,
    dose: M + col.date + col.vaccine,
    midwife: M + col.date + col.vaccine + col.dose,
    remarks: M + col.date + col.vaccine + col.dose + col.midwife
  };

  const drawTableHeader = () => {
    ensurePageSpace(12);

    setColor("fill", COLORS.primary);
    doc.roundedRect(M, y, contentW, 9, 2, 2, "F");

    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    setColor("text", [255, 255, 255]);

    doc.text("Date", colX.date + 2, y + 6);
    doc.text("Vaccine", colX.vaccine + 2, y + 6);
    doc.text("Dose", colX.dose + 2, y + 6);
    doc.text("Midwife", colX.midwife + 2, y + 6);
    doc.text("Remarks", colX.remarks + 2, y + 6);

    y += 10;
  };

  const drawRow = (cells, index) => {
    doc.setFont("helvetica", "normal");
    doc.setFontSize(9);
    setColor("text", COLORS.border);

    const dateLines = split(cells[0], col.date - 4);
    const vacLines = split(cells[1], col.vaccine - 4);
    const doseLines = split(cells[2], col.dose - 4);
    const midLines = split(cells[3], col.midwife - 4);
    const remLines = split(cells[4], col.remarks - 4);

    const maxLines = Math.max(
      dateLines.length, vacLines.length, doseLines.length, midLines.length, remLines.length
    );

    const rowH = Math.max(8, maxLines * 4.5 + 3);

    // new page with header repeat
    if (y + rowH > pageH - 20) {
      doc.addPage();
      y = 18;

      doc.setLineWidth(0.6);
      setColor("draw", COLORS.line);
      doc.roundedRect(M - 2, 10, pageW - (M - 2) * 2, pageH - 18, 3, 3);

      drawTableHeader();
    }

    // zebra fill
    if (index % 2 === 0) {
      setColor("fill", [248, 250, 252]);
      doc.rect(M, y, contentW, rowH, "F");
    }

    setColor("draw", COLORS.line);
    doc.setLineWidth(0.2);
    doc.rect(M, y, contentW, rowH);

    doc.line(colX.vaccine, y, colX.vaccine, y + rowH);
    doc.line(colX.dose, y, colX.dose, y + rowH);
    doc.line(colX.midwife, y, colX.midwife, y + rowH);
    doc.line(colX.remarks, y, colX.remarks, y + rowH);

    const ty = y + 5;
    doc.text(dateLines, colX.date + 2, ty);
    doc.text(vacLines, colX.vaccine + 2, ty);
    doc.text(doseLines, colX.dose + 2, ty);
    doc.text(midLines, colX.midwife + 2, ty);
    doc.text(remLines, colX.remarks + 2, ty);

    y += rowH;
  };

  drawTableHeader();

  const rows = document.querySelectorAll("#vaccinationTableBody tr");

  if (rows.length === 1 && rows[0].innerText.toLowerCase().includes("no records")) {
    ensurePageSpace(18);
    setColor("fill", [255, 251, 235]);
    setColor("draw", [253, 230, 138]);
    doc.roundedRect(M, y, contentW, 14, 2, 2, "FD");

    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    setColor("text", [146, 64, 14]);
    doc.text("No vaccination records found.", M + 6, y + 9);
    y += 18;
  } else {
    let rowIndex = 0;
    rows.forEach((row) => {
      const cols = row.querySelectorAll("td");
      if (cols.length === 5) {
        drawRow(
          [
            cols[0].innerText,
            cols[1].innerText,
            cols[2].innerText,
            cols[3].innerText,
            cols[4].innerText
          ],
          rowIndex
        );
        rowIndex++;
      }
    });
  }

  // =========================
  // FOOTER
  // =========================
  ensurePageSpace(16);
  y += 10;

  const now = new Date();
  const generatedOn = now.toLocaleString("en-PH", {
  year: "numeric",
  month: "long",
  day: "2-digit",
  hour: "2-digit",
  minute: "2-digit",
  hour12: true
});

  doc.setFont("helvetica", "normal");
  doc.setFontSize(9);
  setColor("text", COLORS.gray);
  doc.text(
    "This document is system-generated by Barangay Canlandog Health Center.",
    pageW / 2,
    y,
    { align: "center" }
  );
    y += 5;
    doc.setFontSize(8);
    doc.text(`Generated On: ${generatedOn}`, pageW / 2, y, { align: "center" });
  const fileName = (name || "Infant").replace(/\s+/g, "_") + "_Vaccination_Record.pdf";
  doc.save(fileName);
}
</script>
<script>

function deleteInfant() {
    if (!currentInfantId) {
        alert("No infant selected.");
        return;
    }

    if (!confirm("Are you sure you want to delete this infant record?")) return;

    fetch(`http://localhost:8080/infant/delete/${currentInfantId}`, {
        method: "DELETE"
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            alert(data.message || "Failed to delete infant record.");
            return;
        }

        alert(data.message || "Infant record deleted successfully");

        // close modal then reload
        closeVaccinationModal();
        location.reload();
    })
    .catch((err) => {
        console.error(err);
        alert("Error connecting to API.");
    });
}
</script>