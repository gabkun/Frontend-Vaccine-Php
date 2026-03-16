<?php include __DIR__ . '/auth/auth.php'; ?>

<head>
  <meta charset="UTF-8">
  <title>Schedule Overview</title>

  <!-- GLOBAL ADMIN CSS -->
  <link rel="stylesheet" href="../../../src/admin/admin.css">

  <!-- FONT AWESOME ICONS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<div class="admin-layout">
  <div class="sidebar-container">
    <?php include __DIR__ . '/components/admin_sidebar.php'; ?>
  </div>

  <div class="main-content">
    <div class="infant-content">
      <div class="infant-header">
        <h1>Schedule Overview</h1>
      </div>

      <!-- ===============================
           SUMMARY DASHBOARD
      ================================ -->
      <div class="summary-dashboard">
        <div class="summary-card infants">
          <div class="summary-glow"></div>
          <div class="summary-icon-wrap">
            <div class="summary-icon">
              <i class="fa-solid fa-baby"></i>
            </div>
          </div>
          <div class="summary-info">
            <span class="summary-label">Total Infants</span>
            <h2 id="totalInfants">--</h2>
            <p>Registered infant records</p>
          </div>
        </div>

        <div class="summary-card midwives">
          <div class="summary-glow"></div>
          <div class="summary-icon-wrap">
            <div class="summary-icon">
              <i class="fa-solid fa-user-nurse"></i>
            </div>
          </div>
          <div class="summary-info">
            <span class="summary-label">Total Midwives</span>
            <h2 id="totalMidwives">--</h2>
            <p>Active assigned midwives</p>
          </div>
        </div>

        <div class="summary-card vaccines">
          <div class="summary-glow"></div>
          <div class="summary-icon-wrap">
            <div class="summary-icon">
              <i class="fa-solid fa-syringe"></i>
            </div>
          </div>
          <div class="summary-info">
            <span class="summary-label">Total Vaccines</span>
            <h2 id="totalVaccines">--</h2>
            <p>Available vaccine records</p>
          </div>
        </div>

        <div class="summary-card success">
          <div class="summary-glow"></div>
          <div class="summary-icon-wrap">
            <div class="summary-icon">
              <i class="fa-solid fa-circle-check"></i>
            </div>
          </div>
          <div class="summary-info">
            <span class="summary-label">Successful Vaccinations</span>
            <h2 id="totalSuccessful">--</h2>
            <p>Completed vaccination schedules</p>
          </div>
        </div>
      </div>

      <!-- ===============================
           VACCINATION MONTHLY CALENDAR
      ================================ -->
      <div class="vax-monthly-calendar">

        <div class="vax-calendar-header">
          <h2 id="vaxCalendarMonth"></h2>

          <div class="vax-calendar-nav">
            <button id="vaxPrevMonth">&lt; Prev</button>
            <button id="vaxNextMonth">Next &gt;</button>
          </div>
        </div>

        <div class="vax-calendar-grid" id="vaxCalendarGrid"></div>

      </div>

    </div>
  </div>
</div>

<!-- ===============================
     VACCINATION DETAILS MODAL
================================ -->
<div id="vaxDetailModal" class="vax-modal">
  <div class="vax-modal-content">

    <span class="vax-modal-close" onclick="closeVaxModal()">&times;</span>

    <h2>Vaccination Schedule Details</h2>

    <input type="hidden" id="modalScheduleId">

    <div class="vax-modal-body">
      <p><strong>Infant Name:</strong> <span id="modalInfantName"></span></p>
      <p><strong>Scheduled Date:</strong> <span id="modalScheduledDate"></span></p>
      <p><strong>Vaccine:</strong> <span id="modalVaccineName"></span></p>
      <p><strong>Dose Type:</strong> <span id="modalDoseType"></span></p>
      <p><strong>Status:</strong> <span id="modalStatus"></span></p>
      <p><strong>Assigned Midwife:</strong> <span id="modalMidwife"></span></p>
    </div>

    <div class="vax-modal-actions">
      <button class="btn-done" onclick="markAsDone()">Mark as Done</button>
      <button class="btn-edit" onclick="editSchedule()">Edit</button>
      <button class="btn-delete" onclick="deleteSchedule()">Delete</button>
    </div>

  </div>
</div>

<!-- ===============================
     EDIT SCHEDULE MODAL
================================ -->
<div id="editVaxModal" class="vax-modal">
  <div class="vax-modal-content">
    <span class="vax-modal-close" onclick="closeEditModal()">&times;</span>

    <h2>Edit Vaccination Schedule</h2>

    <form id="editScheduleForm">
      <input type="hidden" id="editScheduleId">

      <div class="vax-form-group">
        <label for="editVaccineId">Vaccine ID</label>
        <input type="number" id="editVaccineId" required>
      </div>

      <div class="vax-form-group">
        <label for="editDoseType">Dose Type</label>
        <select id="editDoseType" required>
          <option value="">-- Select Dose --</option>
          <option value="1st Dose">1st Dose</option>
          <option value="2nd Dose">2nd Dose</option>
          <option value="Booster">Booster</option>
        </select>
      </div>

      <div class="vax-form-group">
        <label for="editScheduledOn">Scheduled Date</label>
        <input type="date" id="editScheduledOn" required>
      </div>

      <div class="vax-form-group">
        <label for="editRemarks">Remarks</label>
        <textarea id="editRemarks" rows="3" placeholder="Enter remarks (optional)"></textarea>
      </div>

      <div class="vax-form-actions">
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn-save">Update Schedule</button>
      </div>
    </form>
  </div>
</div>

<script>
/* ===============================
   SUMMARY DATA
================================ */

async function loadSummary() {
  try {
    const [infantsRes, midwivesRes, vaccinesRes, successfulRes] = await Promise.all([
      fetch("http://localhost:8080/summary/infants/total"),
      fetch("http://localhost:8080/summary/midwives/total"),
      fetch("http://localhost:8080/summary/vaccines/total"),
      fetch("http://localhost:8080/summary/vaccination/successful/total")
    ]);

    const infants = await infantsRes.json();
    const midwives = await midwivesRes.json();
    const vaccines = await vaccinesRes.json();
    const successful = await successfulRes.json();

    document.getElementById("totalInfants").textContent = infants.totalInfants ?? 0;
    document.getElementById("totalMidwives").textContent = midwives.totalMidwives ?? 0;
    document.getElementById("totalVaccines").textContent = vaccines.totalVaccines ?? 0;
    document.getElementById("totalSuccessful").textContent = successful.totalSuccessfulVaccinations ?? 0;

  } catch (err) {
    console.error("Summary error:", err);
    document.getElementById("totalInfants").textContent = "0";
    document.getElementById("totalMidwives").textContent = "0";
    document.getElementById("totalVaccines").textContent = "0";
    document.getElementById("totalSuccessful").textContent = "0";
  }
}

/* ===============================
   VACCINATION CALENDAR SCRIPT
================================ */

const VAX_API_URL = "http://localhost:8080/schedule/vaccination/scheduled/month";

let vaxCurrentDate = new Date();
let selectedSchedule = null;

const vaxMonthLabel = document.getElementById("vaxCalendarMonth");
const vaxCalendarGrid = document.getElementById("vaxCalendarGrid");

document.getElementById("vaxPrevMonth").onclick = () => {
  vaxCurrentDate.setMonth(vaxCurrentDate.getMonth() - 1);
  renderVaxCalendar();
};

document.getElementById("vaxNextMonth").onclick = () => {
  vaxCurrentDate.setMonth(vaxCurrentDate.getMonth() + 1);
  renderVaxCalendar();
};

async function fetchVaxSchedules() {
  const response = await fetch(VAX_API_URL);
  return await response.json();
}

async function renderVaxCalendar() {
  vaxCalendarGrid.innerHTML = "";

  const year = vaxCurrentDate.getFullYear();
  const month = vaxCurrentDate.getMonth();
  const schedules = await fetchVaxSchedules();

  vaxMonthLabel.textContent = vaxCurrentDate.toLocaleString("default", {
    month: "long",
    year: "numeric"
  });

  const weekdays = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
  weekdays.forEach(day => {
    vaxCalendarGrid.innerHTML += `<div class="vax-calendar-weekday">${day}</div>`;
  });

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  for (let i = 0; i < firstDay; i++) {
    vaxCalendarGrid.innerHTML += `<div class="vax-empty-cell"></div>`;
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const dateKey = `${year}-${String(month + 1).padStart(2,"0")}-${String(day).padStart(2,"0")}`;

    const dailySchedules = schedules.filter(s =>
      new Date(s.scheduled_on).toISOString().split("T")[0] === dateKey
    );

    let itemsHTML = "";
    dailySchedules.forEach(s => {
      const fullName = [s.firstname, s.middlename, s.lastname, s.suffix]
        .filter(Boolean).join(" ");

      const safeSchedule = encodeURIComponent(JSON.stringify(s));

      itemsHTML += `
        <div class="vax-schedule-item" onclick="openVaxModalFromEncoded('${safeSchedule}')">
          ${fullName}
        </div>
      `;
    });

    const today = new Date();
    const isToday =
      day === today.getDate() &&
      month === today.getMonth() &&
      year === today.getFullYear();

    vaxCalendarGrid.innerHTML += `
      <div class="vax-calendar-day ${isToday ? "today" : ""}">
        <div class="vax-calendar-day-number">${day}</div>
        ${itemsHTML}
      </div>
    `;
  }
}

function openVaxModalFromEncoded(encodedSchedule) {
  try {
    const schedule = JSON.parse(decodeURIComponent(encodedSchedule));
    openVaxModal(schedule);
  } catch (error) {
    console.error("Failed to parse schedule data:", error);
    alert("Failed to open schedule details.");
  }
}

async function markAsDone() {
  const scheduleId = document.getElementById("modalScheduleId").value;
  if (!scheduleId) return alert("Schedule ID not found.");

  const remarks = prompt("Enter remarks (optional):");

  try {
    const response = await fetch(`http://localhost:8080/schedule/schedule/complete/${scheduleId}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ remarks })
    });

    const data = await response.json();

    if (!response.ok) {
      alert(data.message || "Failed to mark as done.");
      return;
    }

    alert(data.message || "Schedule marked as completed!");
    closeVaxModal();
    renderVaxCalendar();
    loadSummary();
  } catch (err) {
    console.error(err);
    alert("Error connecting to API.");
  }
}

/* ===============================
   MODAL FUNCTIONS
================================ */

function openVaxModal(schedule) {
  selectedSchedule = schedule;

  document.getElementById("modalScheduleId").value = schedule.schedule_id;

  document.getElementById("modalInfantName").textContent =
    [schedule.firstname, schedule.middlename, schedule.lastname, schedule.suffix]
      .filter(Boolean).join(" ");

  document.getElementById("modalScheduledDate").textContent = schedule.scheduled_on;
  document.getElementById("modalVaccineName").textContent = schedule.vaccine_name;
  document.getElementById("modalDoseType").textContent = schedule.dose_type_label || schedule.dose_type;
  document.getElementById("modalStatus").textContent =
    schedule.status === 1 ? "Scheduled" : "Completed";

  document.getElementById("modalMidwife").textContent =
    [schedule.midwife_firstname, schedule.midwife_middlename, schedule.midwife_lastname, schedule.midwife_suffix]
      .filter(Boolean).join(" ");

  document.getElementById("vaxDetailModal").style.display = "flex";
}

function closeVaxModal() {
  document.getElementById("vaxDetailModal").style.display = "none";
}

function closeEditModal() {
  document.getElementById("editVaxModal").style.display = "none";
}

/* ===============================
   ACTION BUTTONS
================================ */

function editSchedule() {
  if (!selectedSchedule) {
    alert("No schedule selected.");
    return;
  }

  const editScheduleId = document.getElementById("editScheduleId");
  const editVaccineId = document.getElementById("editVaccineId");
  const editDoseType = document.getElementById("editDoseType");
  const editScheduledOn = document.getElementById("editScheduledOn");
  const editRemarks = document.getElementById("editRemarks");

  if (!editScheduleId || !editVaccineId || !editDoseType || !editScheduledOn || !editRemarks) {
    alert("Edit form fields are missing.");
    return;
  }

  editScheduleId.value = selectedSchedule.schedule_id || "";
  editVaccineId.value = selectedSchedule.vaccine_id || "";
  editDoseType.value = selectedSchedule.dose_type || "";
  editScheduledOn.value = selectedSchedule.scheduled_on
    ? new Date(selectedSchedule.scheduled_on).toISOString().split("T")[0]
    : "";
  editRemarks.value = selectedSchedule.remarks || "";

  closeVaxModal();
  document.getElementById("editVaxModal").style.display = "flex";
}

document.getElementById("editScheduleForm").addEventListener("submit", async function(e) {
  e.preventDefault();

  const scheduleId = document.getElementById("editScheduleId").value;
  const vaccine_id = document.getElementById("editVaccineId").value;
  const dose_type = document.getElementById("editDoseType").value;
  const scheduled_on = document.getElementById("editScheduledOn").value;
  const remarks = document.getElementById("editRemarks").value;

  if (!scheduleId || !vaccine_id || !dose_type || !scheduled_on) {
    alert("Please fill in all required fields.");
    return;
  }

  try {
    const response = await fetch(`http://localhost:8080/schedule/schedule/edit/${scheduleId}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        vaccine_id,
        dose_type,
        scheduled_on,
        remarks
      })
    });

    const data = await response.json();

    if (!response.ok) {
      alert(data.message || "Failed to update schedule.");
      return;
    }

    alert(data.message || "Schedule updated successfully.");
    closeEditModal();
    renderVaxCalendar();
    loadSummary();
  } catch (error) {
    console.error(error);
    alert("Error connecting to API.");
  }
});

function deleteSchedule() {
  const scheduleId = document.getElementById("modalScheduleId").value;

  if (!scheduleId) {
    alert("Schedule ID not found.");
    return;
  }

  if (!confirm("Are you sure you want to cancel this schedule?")) return;

  const remarks = prompt("Enter cancellation remarks (optional):") || "";

  fetch(`http://localhost:8080/schedule/schedule/cancel/${scheduleId}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      remarks: remarks
    })
  })
    .then(async (res) => {
      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        alert(data.message || "Failed to cancel schedule.");
        return;
      }

      alert(data.message || "Vaccination schedule cancelled successfully.");
      
      // close modal if needed
      const modal = document.getElementById("scheduleModal");
      if (modal) {
        modal.style.display = "none";
      }

      // reload page or refresh table
      location.reload();
    })
    .catch((err) => {
      console.error("Cancel schedule error:", err);
      alert("Error connecting to API.");
    });
}

window.onclick = function(e) {
  const detailModal = document.getElementById("vaxDetailModal");
  const editModal = document.getElementById("editVaxModal");

  if (e.target === detailModal) closeVaxModal();
  if (e.target === editModal) closeEditModal();
};

loadSummary();
renderVaxCalendar();
</script>

</body>