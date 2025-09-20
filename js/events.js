document.addEventListener("DOMContentLoaded", function () {
    var calendarEl = document.getElementById("calendar");
    if (typeof calendarEl != "undefined" && calendarEl != null) {
      var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
          left: "prevYear,prev,next,nextYear today",
          center: "title",
          right: "dayGridMonth,listWeek,dayGridWeek,dayGridDay",
        },
        timeZone: "UTC",
        locale: pworkParams.calendarLocale,
        themeSystem: "bootstrap5",
        eventColor: pworkParams.eventColor,
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        events: pworkCalendarEvents,
      });
      calendar.render();
    }
  });