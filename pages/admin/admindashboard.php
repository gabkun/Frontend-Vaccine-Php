<?php include __DIR__ . '/auth/auth.php'; ?>

<head>
  <meta charset="UTF-8">
  <title>Schedule Overview</title>

  <!-- GLOBAL ADMIN CSS -->
  <link rel="stylesheet" href="../../../src/admin/admin.css">

  <style>
    .vax-form-group {
      margin-bottom: 15px;
    }

    .vax-form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
    }

    .vax-form-group input,
    .vax-form-group select,
    .vax-form-group textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
      font-size: 14px;
    }

    .vax-form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 20px;
    }

    .btn-save {
      background: #1e7e34;
      color: #fff;
      border: none;
      padding: 10px 16px;
      border-radius: 8px;
      cursor: pointer;
    }

    .btn-cancel {
      background: #6c757d;
      color: #fff;
      border: none;
      padding: 10px 16px;
      border-radius: 8px;
      cursor: pointer;
    }

    .btn-save:hover {
      background: #17692b;
    }

    .btn-cancel:hover {
      background: #5a6268;
    }
  </style>
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

    <!-- Hidden schedule id -->
    <input type="hidden" id="modalScheduleId">

    <div class="vax-modal-body">
      <p><strong>Infant Name:</strong> <span id="modalInfantName"></span></p>
      <p><strong>Scheduled Date:</strong> <span id="modalScheduledDate"></span></p>
      <p><strong>Vaccine:</strong> <span id="modalVaccineName"></span></p>
      <p><strong>Dose Type:</strong> <span id="modalDoseType"></span></p>
      <p><strong>Status:</strong> <span id="modalStatus"></span></p>
      <p><strong>Assigned Midwife:</strong> <span id="modalMidwife"></span></p>
    </div>

    <!-- ACTION BUTTONS -->
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
          <option value="">Select Dose Type</option>
          <option value="BCG">BCG</option>
          <option value="Hepa B">Hepa B</option>
          <option value="1st Dose">1st Dose</option>
          <option value="2nd Dose">2nd Dose</option>
          <option value="3rd Dose">3rd Dose</option>
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

      itemsHTML += `
        <div class="vax-schedule-item"
          onclick='openVaxModal(${JSON.stringify(s)})'>
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

  document.getElementById("editScheduleId").value = selectedSchedule.schedule_id;
  document.getElementById("editVaccineId").value = selectedSchedule.vaccine_id || "";
  document.getElementById("editDoseType").value = selectedSchedule.dose_type || "";
  document.getElementById("editScheduledOn").value =
    new Date(selectedSchedule.scheduled_on).toISOString().split("T")[0];
  document.getElementById("editRemarks").value = selectedSchedule.remarks || "";

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
  } catch (error) {
    console.error(error);
    alert("Error connecting to API.");
  }
});

function deleteSchedule() {
  const scheduleId = document.getElementById("modalScheduleId").value;

  if (!confirm("Are you sure you want to delete this schedule?")) return;

  alert("Delete Schedule ID: " + scheduleId);
  // 👉 Call DELETE API here
}

window.onclick = function(e) {
  const detailModal = document.getElementById("vaxDetailModal");
  const editModal = document.getElementById("editVaxModal");

  if (e.target === detailModal) closeVaxModal();
  if (e.target === editModal) closeEditModal();
};

renderVaxCalendar();
</script>

</body>
