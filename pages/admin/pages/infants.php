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
        <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="infant-content">
            <div class="infant-header">
                <h1>Infant Database</h1>
                <button class="add-btn" id="openInfantModal">Add</button>
            </div>

            <div class="infant-list">
                <?php if (!empty($infants)):
                    foreach ($infants as $infant):
                        $name = htmlspecialchars($infant["firstname"] . " " . $infant["lastname"]);
                        $purok_id = $infant["purok_id"];
                        $purok_name = isset($purokMap[$purok_id]) ? htmlspecialchars($purokMap[$purok_id]) : "Unknown";
                        $photo = !empty($infant["profile_pic"]) ? "../../../" . $infant["profile_pic"] : $staticAvatar;
                ?>
                    <div class="infant-card">
                        <img src="<?= $photo ?>" alt="<?= $name ?>">
                        <div class="infant-info">
                            <h3><?= $name ?></h3>
                            <p>Purok: <?= $purok_name ?></p>
                            <button class="view-btn" onclick="openVaccinationModal(<?= $infant['id'] ?>)">
                            View
                        </button>
                        </div>
                    </div>
                <?php
                    endforeach;
                else:
                    echo "<p>No infant records found.</p>";
                endif; ?>
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
                <input type="number" name="purok_id" placeholder="Purok ID" required>
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
                    <button class="birth-btn" onclick="viewBirthCert()">View Birth Certificate</button>
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

<!-- Modal Script -->
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
</script>
<script>
let currentInfantId = null;

function openVaccinationModal(infantId) {
    currentInfantId = infantId;
    document.getElementById("viewVaccinationModal").style.display = "flex";
    document.body.style.overflow = "hidden";

    loadInfantInfo(infantId);
    loadVaccinationRecords(infantId);
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

            // PROFILE PICTURE always static
            const profilePicElem = document.getElementById("profilePic");
            profilePicElem.src = "../../../assets/img/logo.png"; // static image
            profilePicElem.alt = "Infant Profile Picture";
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

function downloadRecord() {
    window.open(`/infant/${currentInfantId}/download`, "_blank");
}

function viewBirthCert() {
    window.open(`/infant/${currentInfantId}/birth-certificate`, "_blank");
}

function closeVaccinationModal() {
    document.getElementById("viewVaccinationModal").style.display = "none";
    document.body.style.overflow = "auto";
}
</script>
<script>
function viewBirthCert() {
    if (!birthDocumentPath) {
        alert("No birth certificate uploaded.");
        return;
    }

    window.open(`http://localhost:8080/${birthDocumentPath}`, "_blank");
}
</script>
<script>
function downloadRecord() {
    window.open(
        `http://localhost:8080/infant/${currentInfantId}/download`,
        "_blank"
    );
}
</script>
<script>

function deleteInfant() {
    if (!confirm("Are you sure you want to delete this infant record?")) return;

    fetch(`http://localhost:8080/infant/${currentInfantId}`, {
        method: "DELETE"
    })
        .then(res => res.json())
        .then(() => {
            alert("Infant record deleted successfully");
            location.reload();
        });
}
</script>