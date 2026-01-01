<?php include __DIR__ . '/auth/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard Overview</title>

  <!-- GLOBAL ADMIN CSS -->
  <link rel="stylesheet" href="../../../src/admin/admin.css">

</head>

<body>

<div class="admin-layout">
  <div class="sidebar-container">
    <?php include __DIR__ . '/components/admin_sidebar.php'; ?>
  </div>

  <div class="main-content">
    <div class="infant-content">
      <div class="infant-header">
        <h1>Admin Dashboard Overview</h1>
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

        <div class="vax-calendar-grid" id="vaxCalendarGrid">
          <!-- Calendar generated here -->
        </div>

      </div>

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
    vaxCalendarGrid.innerHTML += `
      <div class="vax-calendar-weekday">${day}</div>
    `;
  });

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  for (let i = 0; i < firstDay; i++) {
    vaxCalendarGrid.innerHTML += `<div class="vax-empty-cell"></div>`;
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const dateKey = `${year}-${String(month + 1).padStart(2,"0")}-${String(day).padStart(2,"0")}`;

    const dailySchedules = schedules.filter(s => {
      return new Date(s.scheduled_on).toISOString().split("T")[0] === dateKey;
    });

    let itemsHTML = "";
    dailySchedules.forEach(s => {
      const fullName = [
        s.firstname,
        s.middlename,
        s.lastname,
        s.suffix
      ].filter(Boolean).join(" ");

      itemsHTML += `<div class="vax-schedule-item">${fullName}</div>`;
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

renderVaxCalendar();
</script>

</body>
</html>
