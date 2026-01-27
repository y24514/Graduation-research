document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar-area');
  if (!calendarEl) return;

  function sdIsTouchDevice() {
    try {
      return (navigator.maxTouchPoints && navigator.maxTouchPoints > 0) || ('ontouchstart' in window);
    } catch (_) {
      return false;
    }
  }

  function sdAddOneDay(dateStr) {
    var parts = String(dateStr).split('-');
    if (parts.length !== 3) return dateStr;
    var d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    d.setDate(d.getDate() + 1);
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + day;
  }

  var isTouch = sdIsTouchDevice();

  // 2025年の日本の祝日
  var holidays = [
    '2025-01-01', // 元日
    '2025-01-13', // 成人の日
    '2025-02-11', // 建国記念の日
    '2025-02-23', // 天皇誕生日
    '2025-02-24', // 振替休日
    '2025-03-20', // 春分の日
    '2025-04-29', // 昭和の日
    '2025-05-03', // 憲法記念日
    '2025-05-04', // みどりの日
    '2025-05-05', // こどもの日
    '2025-05-06', // 振替休日
    '2025-07-21', // 海の日
    '2025-08-11', // 山の日
    '2025-09-15', // 敬老の日
    '2025-09-23', // 秋分の日
    '2025-10-13', // スポーツの日
    '2025-11-03', // 文化の日
    '2025-11-23', // 勤労感謝の日
    '2025-11-24', // 振替休日
    '2026-01-01', // 元日
    '2026-01-12', // 成人の日
    '2026-02-11', // 建国記念の日
    '2026-02-23', // 天皇誕生日
    '2026-03-20', // 春分の日
    '2026-04-29', // 昭和の日
    '2026-05-03', // 憲法記念日
    '2026-05-04', // みどりの日
    '2026-05-05', // こどもの日
    '2026-05-06', // 振替休日
    '2026-07-20', // 海の日
    '2026-08-11', // 山の日
    '2026-09-21', // 敬老の日
    '2026-09-22', // 国民の休日
    '2026-09-23', // 秋分の日
    '2026-10-12', // スポーツの日
    '2026-11-03', // 文化の日
    '2026-11-23'  // 勤労感謝の日
  ];

  var calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'ja',
    initialView: 'dayGridMonth',
    // タッチ端末でのドラッグ選択はスクロールと競合しやすく、
    // Chromeの[Intervention]警告の原因になりやすいので無効化（タップで登録に寄せる）
    selectable: !isTouch,
    height: 'auto',
    fixedWeekCount: false,
    dayMaxEvents: true,

    headerToolbar: { left: 'title', right: 'prev,next today' },

    titleFormat: { year: 'numeric', month: 'long' },

    buttonText: {
      today: '今日',
      month: '月'
    },

    // 土日の背景色
    dayCellClassNames: function(arg) {
      var classes = [];
      var dayOfWeek = arg.date.getDay();
      var dateStr = arg.date.toISOString().split('T')[0];
      
      // 土曜日
      if (dayOfWeek === 6) {
        classes.push('fc-day-sat');
      }
      // 日曜日
      if (dayOfWeek === 0) {
        classes.push('fc-day-sun');
      }
      // 祝日
      if (holidays.includes(dateStr)) {
        classes.push('fc-day-holiday');
      }
      
      return classes;
    },

    events: (typeof eventsFromPHP !== 'undefined' ? eventsFromPHP : []),

    // タッチ端末はタップで登録（PCはドラッグ選択のままでもOK）
    dateClick: function(info) {
      if (!isTouch) return;
      if (typeof openEventModal !== 'function') return;
      openEventModal({
        startStr: info.dateStr,
        endStr: sdAddOneDay(info.dateStr)
      });
    },

    select: function(info) {
      // モーダルを開く
      openEventModal(info);
      calendar.unselect();
    }
  });

  calendar.render();
  
  // グローバルからアクセスできるように
  window.calendarInstance = calendar;
});
