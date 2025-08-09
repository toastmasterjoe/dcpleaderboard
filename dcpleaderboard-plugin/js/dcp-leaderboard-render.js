/*the function should always return a category
if the record has no value in last year status and in this years status
it should go to Serie D
*/

function dcp_table_draw($, table) {
    var info = table.page.info();
    var virtualRowIdx = 1;

    // Iterate over the rows that are currently visible
    table.rows({ page: 'current', search: 'applied' }).every(function (rowIdx) {
        // Get the DOM node for the current row
        var rowNode = this.node();

        const rowData = this.data();
        const value = rowData['goals_met']; 

        // Calculate the new sequential ID
        var newId = info.start + virtualRowIdx;
        virtualRowIdx++;
        // Update the first cell (the ID column) with the new ID
        switch (newId) {
            case 1:
                $('td:eq(0)', rowNode).html(newId + '&nbsp;🥇');
                break;
            case 2:
                $('td:eq(0)', rowNode).html(newId + '&nbsp;🥈');
                break;
            case 3:
                $('td:eq(0)', rowNode).html(newId + '&nbsp;🥉');
                break;
            default:
                $('td:eq(0)', rowNode).html(newId);
                break;
        }
        $('td:eq(6)', rowNode).html(render_goals(value,rowData));
    });
}

function render_goals(data) {
    const goals = parseInt(data, 10);
    const percentage = (goals / 10) * 100;



    const color = interpolateColor("#006094", "#004165", goals / 10);
    const barId = `bar-${Math.random().toString(36).slice(2, 9)}`;
    const tooltipText = `${percentage.toFixed(0)}% completed`;

    setTimeout(() => {
        const el = document.getElementById(barId);
        if (el) el.style.width = `${percentage}%`;
    }, 100);

    return `
        <div class="progress-cell">
            <div class="progress-container" title="${tooltipText}">
                <div class="progress-bar" id="${barId}" style="background-color: ${color};"></div>  
            </div>
            <div class="progress-text" >${goals}/10</div>
        </div>
    `;
}

function calculate_eligibility(row) {
    $value = row.csp.toLowerCase() === 'n' ||(row.active_members < 20 && (row.active_members - row.mem_base) < 3) ?'no' :'yes' 
    return { eligible : $value };
}

function render_eligibility(row) {
    var result = calculate_eligibility(row);
    return `
        <div class="progress-cell">
            <div class="progress-text" >
                ${(result.eligible === 'no' ? '☹️' : '😎')}
            </div>
        </div>
    `;
}


function init_document($) {
    $.ajax({
        url: window.location.origin + '/wp-json/dcpleaderboard/v1/divisions',
        success: function (data) {
            var divisionSelect = $('#divisionFilter');
            divisionSelect.empty().append('<option value="">All Divisions</option>');
            data.forEach(function (division) {
                divisionSelect.append('<option value="' + division + '">' + division + '</option>');
            });
        }
    });

    var table = $('#club_leaderboard').DataTable({
        searching: true,
        processing: true,
        serverSide: false,
        responsive: true,
        ordering: false,
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        serverMethod: 'get',
        ajax: {
            'url': window.location.origin + '/wp-json/dcpleaderboard/v1/clubs',
            dataSrc: ''
        },
        columns: [
            {
                "data": null,
                "defaultContent": ""
            },
            { data: 'division' },
            { data: 'area' },
            { data: 'club_name' },
            { data: 'ti_status' },
            {
                data: (row, type, set) => calculate_eligibility(row).eligible,
                render: (data, type, row) => render_eligibility(row)
            },
            {
                data: 'goals_met',
                render: (data, type, row) => render_goals(data)
            }
        ]
    });
    $('.dt-search label').hide();
    $('.dt-search input').hide();
    $('.dt-search').append($(".custom-table-filter"));
    $.fn.dataTable.ext.search.push(function (searchStr, data, index, rowData) {
        const selectedDivision = $('#divisionFilter').val();
        const selectedArea = $('#areaFilter').val();
        const division = data[1];
        const area = data[2];
        if (selectedDivision && division !== selectedDivision) return false;
        if (selectedArea && area !== selectedArea) return false;
        return true;
    });

    $('#areaFilter').on('change', function () {
        table.draw();
    });

    $('#divisionFilter').on('change', function () {
        var division = $(this).val();

        // Fetch area options from server
        if (division) {
            $.ajax({
                url: window.location.origin + '/wp-json/dcpleaderboard/v1/areas?division=' + division,
                success: (data) => populate_areas (data , $, table) ,
                error:  (err) => console.log('error:' + err)
            });
        } else {
            var areaSelect = $('#areaFilter');
            areaSelect.empty().append('<option value="">All Areas</option>');
            table.draw();
        }
    });

    table.on('draw.dt', ()=> dcp_table_draw($, table));

} 