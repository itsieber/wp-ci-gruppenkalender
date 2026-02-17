<?php header("Content-Type: application/javascript"); ?>
jQuery(function ($) {
  // ---------- Helpers ----------
  function parseLocalDateTime(s){
    const [d] = String(s).split(" ");
    const [Y,M,D] = d.split("-").map(Number);
    return new Date(Y, M-1, D);
  }
  function normalizeRanges(arr){
    if(!Array.isArray(arr)) return [];
    return arr.map(r=>{
      const start = parseLocalDateTime(r.Beginn);
      const end   = parseLocalDateTime(r.Ende);
      return {start, end};
    });
  }
  function isInRanges(date, ranges){
    for (let i=0;i<ranges.length;i++){
      const {start,end} = ranges[i];
      if (date>=start && date<=end) return true;
    }
    return false;
  }
  function addDays(d, n){ const x=new Date(d); x.setDate(x.getDate()+n); return x; }
  function addMonths(d, n){ const x=new Date(d); x.setMonth(x.getMonth()+n); return x; }

  // ---------- Daten aus globalem Scope ----------
  const belegtRanges          = normalizeRanges(window.belegt || []);
  const teilweiseBelegtRanges = normalizeRanges(window.teilweisebelegt || []);
  const freiRanges            = normalizeRanges(window.frei || []);

  // ---------- Kalender Setup ----------
  const calendars = {};        // id -> instance
  let baseShift = 0;           // 0 = heute/+1/+2, +1 = +1/+2/+3, etc.
  const today = new Date();    // dynamischer Start

  // gemeinsame min/max (z.B. ±24 Monate rund um heute)
  const minDate = addMonths(new Date(today.getFullYear(), today.getMonth(), 1), -24);
  const maxDate = addMonths(new Date(today.getFullYear(), today.getMonth()+1, 0), 24);

  function cellClassFor(date){
    // Priorität: belegt > teilweise > frei
    let kind = "";
    if (isInRanges(date, belegtRanges)) kind = "booked";
    else if (isInRanges(date, teilweiseBelegtRanges)) kind = "partial";
    else if (isInRanges(date, freiRanges)) kind = "free";
    if (!kind) return ""; // keine Markierung

    const prevIn = (kind==="booked" ? isInRanges(addDays(date,-1), belegtRanges)
                 : kind==="partial"? isInRanges(addDays(date,-1), teilweiseBelegtRanges)
                 :                       isInRanges(addDays(date,-1), freiRanges));

    const nextIn = (kind==="booked" ? isInRanges(addDays(date,+1), belegtRanges)
                 : kind==="partial"? isInRanges(addDays(date,+1), teilweiseBelegtRanges)
                 :                       isInRanges(addDays(date,+1), freiRanges));

    // Position bestimmen
    let pos = "single";
    if (prevIn && nextIn) pos = "middle";
    else if (prevIn && !nextIn) pos = "end";
    else if (!prevIn && nextIn) pos = "start";

    return { kind, pos };
  }

function makeCellTemplate(cellData){
  const d = new Date(
    cellData.date.getFullYear(),
    cellData.date.getMonth(),
    cellData.date.getDate()
  );
  const cls = cellClassFor(d);
  if (!cls) return `<span>${cellData.text}</span>`;

  const { kind, pos } = cls;            // pos ist: start | middle | end | single
  const classes = `range range--${kind} range--${pos}`;  // <-- pos IMMER anhängen
  return `<span class="${classes}">${cellData.text}</span>`;
}


  function initCalendar(id, visibleMonthDate){
    const $el = $("#"+id);
    if (!$el.length) return;

    const inst = $el.dxCalendar({
      value: visibleMonthDate,
      currentDate: visibleMonthDate,
      min: minDate,
      max: maxDate,
      firstDayOfWeek: 1,
      zoomLevel: "month",
      maxZoomLevel: "month",
      showTodayButton: false,
      showWeekNumbers: false,
      hoverStateEnabled: false,
      activeStateEnabled: false,
      focusStateEnabled: false,
      onValueChanged: () => {},
      // Eigene Navigation benutzen
      onInitialized: e => {
        // DevExtreme Header ausblenden (optional, je nach Theme)
        $el.find(".dx-calendar-caption-button, .dx-calendar-navigator").hide();
      },
      cellTemplate: makeCellTemplate
    }).dxCalendar("instance");

    calendars[id] = inst;
  }

  function renderAll(){
    // drei Monate beginnend bei (heute + baseShift)
    const m0 = addMonths(new Date(today.getFullYear(), today.getMonth(), 1), baseShift+0);
    const m1 = addMonths(new Date(today.getFullYear(), today.getMonth(), 1), baseShift+1);
    const m2 = addMonths(new Date(today.getFullYear(), today.getMonth(), 1), baseShift+2);

    if (!calendars["cal-0"]) initCalendar("cal-0", m0); else calendars["cal-0"].option("currentDate", m0);
    if (!calendars["cal-1"]) initCalendar("cal-1", m1); else calendars["cal-1"].option("currentDate", m1);
    if (!calendars["cal-2"]) initCalendar("cal-2", m2); else calendars["cal-2"].option("currentDate", m2);
  }

  // Navigation
  $("#cal-prev").on("click", function(){ baseShift -= 1; renderAll(); });
  $("#cal-next").on("click", function(){ baseShift += 1; renderAll(); });

  // Initial
  renderAll();
});
