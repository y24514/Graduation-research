document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar-area');

  var calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'ja',
    initialView: 'dayGridMonth',
    selectable: true,

    events: eventsFromPHP,

    select: function(info) {
      var title = prompt('イベント名:');
      var memo = prompt('メモ:');
      
      if (title) {

        fetch('../PHP/calendarsave.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            title: title,
            memo: memo,
            startdate: info.startStr,
            enddate: info.endStr
          })
        });

        calendar.addEvent({
          title: title,
          start: info.startStr,
          end: info.endStr
        });
      }

      calendar.unselect();
    }
  });

  calendar.render();
});
