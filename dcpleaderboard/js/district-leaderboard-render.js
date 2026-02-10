/*the function should always return a category
if the record has no value in last year status and in this years status
it should go to Serie D
*/

/*
function render_goals(data, row) {

    const goals = parseInt(data, 10);
    const percentage = (goals / 16) * 100;

    const color = interpolateColor("#006094", "#004165", goals / 16);
    const barId = `bar-${row['club_number']}`;
    const tooltipText = `${percentage.toFixed(0)}% completed`;

    return `
        <div class="progress-cell">
            <div class="progress-container" title="${tooltipText}">
                <div class="progress-bar" id="${barId}" style="background-color: ${color};  width: ${percentage}%"></div>  
            </div>
            <div class="progress-text" >${goals}/16</div>
        </div>
    `;
}
*/

var clubIdExpanded = null;
var clubGoalData = null;

function district_table_draw($, table) {
  var info = table.page.info();
  var virtualRowIdx = 1;

  // Iterate over the rows that are currently visible
  table.rows({ page: "current", search: "applied" }).every(function (rowIdx) {
    // Get the DOM node for the current row
    var rowNode = this.node();

    const rowData = this.data();
    const goalsAchieved = rowData["district_goals_met"];
    const clubId = rowData["id"];

    // Calculate the new sequential ID
    var newId = info.start + virtualRowIdx;
    virtualRowIdx++;
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
            <thead> 
              <tr> 
                <th>Name</th> 
                <th>Points</th> 
                <th>Trigger Count</th> 
                <th>Points Awarded</th> 
              </tr> 
            </thead> 
            <tbody></tbody> 
          </table>`);
    const dt = tableGoals.DataTable({
      paging: false,
      searching: false,
      info: false,
      ordering: false,
      data: clubGoalData,
      columns: [
        { data: "name" },
        { data: "points" },
        { data: "trigger_count" },
        { data: "points_awarded" },
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
        data: null,
        defaultContent: "",
      },
      { data: "division" },
      { data: "area" },
      { data: "club_name" },
      { data: "district_goals_met" },
      {
        data: (row, type, set) => calculate_category(row).name,
        render: (data, type, row) => render_category(row),
      },
      { data: "ti_status" },
      {
        data: (row, type, set) => calculate_eligibility(row).eligible,
        render: (data, type, row) => render_eligibility(row),
      },
      {
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
    const url = window.location.origin + "/wp-json/districtleaderboard/v1/club/" + clubIdExpanded + "/goals";
    const response = await fetch(url); 
    clubGoalData = await response.json();   
    table.draw(); 
  });

  table.on("draw.dt", () => district_table_draw($, table));
}
