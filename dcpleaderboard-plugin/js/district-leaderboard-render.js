/*the function should always return a category
if the record has no value in last year status and in this years status
it should go to Serie D
*/

function render_goals(data) {
    const goals = parseInt(data, 10);
    const percentage = (goals / 10) * 100;



    const color = interpolateColor("#006094", "#004165", goals / 15);
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
            'data': {district_mode : true},
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
                data: (row, type, set) => calculate_category(row).name,
                render: (data, type, row) => render_category(row)
            },
            {
                data: 'district_goals_met',
                render: (data, type, row) => render_goals(data)
            }
        ]
    });
    $('.dt-search label').hide();
    $('.dt-search input').hide();
    $('.dt-search').append($(".custom-table-filter"));
    $.fn.dataTable.ext.search.push(function (searchStr, data, index, rowData) {
        const selectedCategory = $('#categoryFilter').val();
        const selectedDivision = $('#divisionFilter').val();
        const selectedArea = $('#areaFilter').val();
        const division = data[1];
        const area = data[2];
        const category = calculate_category(rowData).name;
        console.log(`is row ${category} the same as selected ${selectedCategory}`);
        if (selectedCategory && category !== selectedCategory) return false;
        if (selectedDivision && division !== selectedDivision) return false;
        if (selectedArea && area !== selectedArea) return false;
        return true;
    });

    $('#areaFilter').on('change', function () {
        table.draw();
    });

    $('#categoryFilter').on('change', function () {
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

    table.on('draw.dt', ()=> table_draw($, table));

} 