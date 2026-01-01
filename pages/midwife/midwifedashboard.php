<?php include __DIR__ . '/auth/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Schedule Overview</title>

  <!-- GLOBAL ADMIN CSS -->
  <link rel="stylesheet" href="../../../src/midwife/midwife.css">

</head>

<body>

<div class="admin-layout">
  <div class="sidebar-container">
    <?php include __DIR__ . '/components/midwife_sidebar.php'; ?>
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
      <button class="btn-edit" onclick="editSchedule()">Edit</button>
      <button class="btn-delete" onclick="deleteSchedule()">Delete</button>
    </div>

  </div>
</div>

<script>
/* ===============================
   VACCINATION CALENDAR SCRIPT
================================ */

const VAX_API_URL = "http://localhost:8080/schedule/vaccination/scheduled/month";

let vaxCurrentDate = new Date();

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

/* ===============================
   MODAL FUNCTIONS
================================ */

function openVaxModal(schedule) {
  document.getElementById("modalScheduleId").value = schedule.schedule_id;

  document.getElementById("modalInfantName").textContent =
    [schedule.firstname, schedule.middlename, schedule.lastname, schedule.suffix]
      .filter(Boolean).join(" ");

  document.getElementById("modalScheduledDate").textContent = schedule.scheduled_on;
  document.getElementById("modalVaccineName").textContent = schedule.vaccine_name;
  document.getElementById("modalDoseType").textContent = schedule.dose_type_label;
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

/* ===============================
   ACTION BUTTONS
================================ */

function editSchedule() {
  const scheduleId = document.getElementById("modalScheduleId").value;
  alert("Edit Schedule ID: " + scheduleId);
  // 👉 Redirect or open edit modal
}

function deleteSchedule() {
  const scheduleId = document.getElementById("modalScheduleId").value;

  if (!confirm("Are you sure you want to delete this schedule?")) return;

  alert("Delete Schedule ID: " + scheduleId);
  // 👉 Call DELETE API here
}

window.onclick = function(e) {
  const modal = document.getElementById("vaxDetailModal");
  if (e.target === modal) closeVaxModal();
};

renderVaxCalendar();
</script>

</body>
</html>
