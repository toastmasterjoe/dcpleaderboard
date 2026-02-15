/*the function should always return a category
if the record has no value in last year status and in this years status
it should go to Serie D
*/


function render_progress(current, max) {

    const goals = parseInt(current, 10);
    const percentage = (goals / max) * 100;

    const color = interpolateColor("#006094", "#004165", goals / max);
    //const barId = `bar-${row['club_number']}`;
    const tooltipText = `${percentage.toFixed(0)}% completed`;

    return `
        <div class="progress-cell">
            <div class="progress-container" title="${tooltipText}">
                <div class="progress-bar" style="background-color: ${color};  width: ${percentage}%"></div>  
            </div>
            <div class="progress-text" >${goals}/${max}</div>
        </div>
    `;
}

var clubIdExpanded = null;
var clubGoalData = null;


function district_table_draw($, table) {
  var info = table.page.info();
  var virtualRowIdx = 1;
  var firstRow = true;
  var lastGoalsMet = -1;

  // Iterate over the rows that are currently visible
  table.rows({ page: "current", search: "applied" }).every(function (rowIdx) {
    // Get the DOM node for the current row
    var rowNode = this.node();
    const rowData = this.data();
    const goalsAchieved = rowData["district_goals_met"];
    if(firstRow){
        lastGoalsMet = goalsAchieved;
        firstRow = false;
    }

    // Calculate the new sequential ID
    
    if(goalsAchieved !== lastGoalsMet){
        lastGoalsMet = goalsAchieved;
        virtualRowIdx++;
    }
    var newId = info.start + virtualRowIdx;
    // Update the first cell (the ID column) with the new ID
    switch (newId) {
      case 1:
        $("td:eq(0)", rowNode).html(newId + "&nbsp;🥇");
        break;
      case 2:
        $("td:eq(0)", rowNode).html(newId + "&nbsp;🥈");
        break;
      case 3:
        $("td:eq(0)", rowNode).html(newId + "&nbsp;🥉");
        break;
      default:
        $("td:eq(0)", rowNode).html(newId);
        break;
    }
    //$('td:eq(6)', rowNode).html(render_goals(goalsAchieved,rowData));
    $("td:eq(8)", rowNode).html(render_goals($, rowData.id));
  });
}

function render_goals($, clubId) {
  if (clubIdExpanded !== clubId) {
    return `<button class="expandgoals" data-club-id="${clubId}">Goals</button>`;
  } else {
    const tableGoals = $(`
          <table class="goals-nested-table display compact" width="100%"> 
          </table>`);
    const dt = tableGoals.DataTable({
      fixedHeader: true,
      paging: false,
      searching: false,
      info: false,
      ordering: false,
      data: clubGoalData,
      columns: [
        { 
          title: "Name",
          tooltipText: "Goal description",
          data: "name" 
        },
        { 
          title: "Points Per Recurrence",
          tooltipText: "Points awarded per recurrence",
          data: "points" 
        },
        { 
          title: "Recurrence",
          tooltipText: "Number of times the goal was achieved",
          data: "trigger_count" 
        },
        { 
          title: "Points Awarded",
          tooltipText: "Total points awarded for the goal",
          data: "points_awarded" 
        },
      ],
    });
    return ` 
        <div class="nested-wrapper"> 
          ${tableGoals.prop("outerHTML")}
        </div> `;
  }
}

function calculate_eligibility(row) {
  $value =
    row.csp.toLowerCase() === "n" ||
    (row.active_members < 20 && row.active_members - row.mem_base < 3)
      ? "no"
      : "yes";
  return { eligible: $value };
}

function render_eligibility(row) {
  var result = calculate_eligibility(row);
  return `
        <div class="progress-cell">
            <div class="progress-text" >
                ${result.eligible === "no" ? "☹️" : "😎"}
            </div>
        </div>
    `;
}

function init_document($) {
  $.ajax({
    url: window.location.origin + "/wp-json/dcpleaderboard/v1/divisions",
    success: function (data) {
      var divisionSelect = $("#divisionFilter");
      divisionSelect.empty().append('<option value="">All Divisions</option>');
      data.forEach(function (division) {
        divisionSelect.append(
          '<option value="' + division + '">' + division + "</option>",
        );
      });
    },
  });

  var table = $("#club_leaderboard").DataTable({
    fixedHeader: true,
    searching: true,
    processing: true,
    serverSide: false,
    responsive: true,
    ordering: false,
    pageLength: 200,
    lengthMenu: [10, 20, 50, 100, 200],
    serverMethod: "get",
    dom: '<"top-controls"lf>rt<"bottom-controls"ip>',
    /*
     *    l = length menu (entries per page)
     *    f = filter (search box)
     *    r = processing display
     *    t = table
     *    i = table info
     *    p = pagination
     */
    ajax: {
      url: window.location.origin + "/wp-json/dcpleaderboard/v1/clubs",
      data: { district_mode: true },
      dataSrc: "",
    },
    columns: [
      {
        title: "Position",
        data: null,
        defaultContent: "",
      },
      { 
        title: "Division",
        data: "division" 
      },
      { 
        title: "Area",
        data: "area" 
      },
      { 
        title: "Club",
        data: "club_name" 
      },
      { 
        title: "District Points",
        tooltipText: "Points from the District Goals",
        data: "district_goals_met" 
      }, 
      {
        title: "Category",
        tooltipText: "Club category based on last year and this year DCP status",
        data: (row, type, set) => calculate_category(row).name,
        render: (data, type, row) => render_category(row),
      },
      {
        title: "Status", 
        data: "ti_status" 
      },
      {
        title: "DCP Eligible",
        data: (row, type, set) => calculate_eligibility(row).eligible,
        render: (data, type, row) => render_eligibility(row),
      },
      {
        title: "CSP Submitted:",
        className: "none",
        data: null,
        render: (data, type, row) => {return row.csp == 'Y' ?"✅" : "🚫";}
      },
      {
        title: "Club State:",
        className: "none",
        data: "club_status",
      },
      {
        title: "Active Members:",
        className: "none",
        data: "active_members",
      },
      {
        title: "Net Growth:",
        className: "none",
        data: "net_growth",
      },
      {
        title: "New Members:",
        className: "none",
        data: null,
        render: (data, type, row) => {return +row.new_members + +row.add_new_members},
      },
      {
        title: "Retention:",
        className: "none",
        data: null,
        render: (data, type, row) => {
          const newTotal = +row.new_members + +row.add_new_members;
          const value =
            ((+row.active_members - newTotal) / +row.mem_base) * 100;
          const retention= value.toFixed(1);
          return render_progress(retention,100);
        },
      },
      {
        title: "Total Educational Awards:",
        className: "none",
        data: null,
        render: (data, type, row) => {return +row.level_1 + +row.level_2 + +row.add_level_2 + +row.level_3 + +row.level_4_5_DTM + +row.add_level_4_5_DTM},
      },
      {
        title: "Training Score:",
        className: "none",
        data: null,
        render: (data, type, row) => {
          const officers= +row.officers_round_1 + +row.officers_round_2;
          return render_progress(officers,14);
        },
      },
      {
        title: "View",
        className: "none",
        data: null,
        render: (data, type, row) => render_goals($, row.id),
      },
    ],
  });
  $(".dt-search label").hide();
  $(".dt-search input").hide();
  $(".dt-search").append($(".custom-table-filter"));
  $.fn.dataTable.ext.search.push(function (searchStr, data, index, rowData) {
    const selectedCategory = $("#categoryFilter").val();
    const selectedDivision = $("#divisionFilter").val();
    const selectedArea = $("#areaFilter").val();
    const division = data[1];
    const area = data[2];
    const category = calculate_category(rowData).name;
    if (selectedCategory && category !== selectedCategory) return false;
    if (selectedDivision && division !== selectedDivision) return false;
    if (selectedArea && area !== selectedArea) return false;
    return true;
  });

  $("#areaFilter").on("change", function () {
    table.draw();
  });

  $("#categoryFilter").on("change", function () {
    table.draw();
  });

  $("#divisionFilter").on("change", function () {
    var division = $(this).val();

    // Fetch area options from server
    if (division) {
      $.ajax({
        url:
          window.location.origin +
          "/wp-json/dcpleaderboard/v1/areas?division=" +
          division,
        success: (data) => populate_areas(data, $, table),
        error: (err) => console.log("error:" + err),
      });
    } else {
      var areaSelect = $("#areaFilter");
      areaSelect.empty().append('<option value="">All Areas</option>');
      table.draw();
    }
  });

  $("#club_leaderboard tbody").on("click", ".expandgoals", async function () {
    console.log("Expand goals clicked");
    clubIdExpanded = this.dataset.clubId;
    const url =
      window.location.origin +
      "/wp-json/districtleaderboard/v1/club/" +
      clubIdExpanded +
      "/goals";
    const response = await fetch(url);
    clubGoalData = await response.json();
    table.draw();
  });

  table.on("draw.dt", () => district_table_draw($, table));
}
