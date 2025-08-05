function render_category(data, type, row){
                            console.log(row.ti_status);
                            var category = '';
                            switch(data){
                                case '':
                                    category = 'D';
                                    break;
                                case 'D':
                                    category = 'C';
                                    break;
                                case 'S':
                                    category = 'B';
                                    break;
                                case 'P':
                                    category = 'A';
                                    break;
                                default:
                                    category = 'D';
                                    break;
                            }
                            var newCategory = '';
                            switch(row.ti_status){
                                case '':
                                    newCategory = 'D';
                                    break;
                                case 'D':
                                    newCategory = 'C';
                                    break;
                                case 'S':
                                    newCategory = 'B';
                                    break;
                                case 'P':
                                    newCategory = 'A';
                                    break;
                                default:
                                    newCategory = 'A';
                                    break;
                            }
                            return `
                            <div class="progress-cell">
                                <div class="progress-text" >${( (newCategory < category) ?'<span class="promotion-marker">&#9650;</span>': '<span class="promotion-marker">&nbsp;</span>')}
                                    Serie ${( (newCategory < category) ? newCategory : category )}
                                </div>
                            </div>
                            `;
                        }
        function init_document($) {
            $.ajax({
                url: window.location.origin + '/wp-json/dcpleaderboard/v1/divisions',
                success: function(data) {
                    var divisionSelect = $('#divisionFilter');
                    divisionSelect.empty().append('<option value="">All Divisions</option>');
                    data.forEach(function(division) {
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
		          	'url':window.location.origin + '/wp-json/dcpleaderboard/v1/clubs',
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
                    { data: 'ti_status_last_year',
                        render: (data, type, row) => render_category(data, type, row)
                    },
                    { data: 'goals_met',
                        render: function(data, type, row) {
                        const goals = parseInt(data, 10);
                        const percentage = (goals / 10) * 100;

                        // Interpolate between #006094 and #004165
                        function interpolateColor(startHex, endHex, factor) {
                            const hexToRgb = hex => [
                            parseInt(hex.slice(1, 3), 16),
                            parseInt(hex.slice(3, 5), 16),
                            parseInt(hex.slice(5, 7), 16)
                            ];

                            const rgbToHex = rgb => '#' + rgb.map(val => {
                            const hex = Math.round(val).toString(16);
                            return hex.length === 1 ? '0' + hex : hex;
                            }).join('');

                            const startRGB = hexToRgb(startHex);
                            const endRGB = hexToRgb(endHex);

                            const resultRGB = startRGB.map((startVal, i) =>
                            startVal + (endRGB[i] - startVal) * factor
                            );

                            return rgbToHex(resultRGB);
                        }

                        const color = interpolateColor("#006094", "#004165", goals / 10);
                        const barId = `bar-${Math.random().toString(36).substr(2, 9)}`;
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
                    }
		      	]
		   });
           $('.dt-search label').hide();
           $('.dt-search input').hide();
           $('.dt-search').append($(".custom-table-filter"));
           $.fn.dataTable.ext.search.push(function (searchStr, data, index) {
                   const selectedDivision = $('#divisionFilter').val();
                   const selectedArea = $('#areaFilter').val();
                   const division = data[1];
                   const area = data[2];
                   if (selectedDivision && division !== selectedDivision) return false;
                   if (selectedArea && area !== selectedArea) return false;
                   return true;
           });
           
           $('#areaFilter').on('change', function() {
                table.draw();
            });

           $('#divisionFilter').on('change', function() {
                var division = $(this).val();
               
                // Fetch area options from server
                if(division){
                    $.ajax({
                        url: window.location.origin + '/wp-json/dcpleaderboard/v1/areas?division=' + division,
                        success: function(data) {
                            var areaSelect = $('#areaFilter');
                            areaSelect.empty().append('<option value="">All Areas</option>');
                            data.forEach(function(area) {
                                areaSelect.append('<option value="' + area + '">' + area + '</option>');
                            });
                            table.draw();
                        },
                        error: function(err){
                            console.log('error:'+err);
                        }
                    });
                } else {
                    var areaSelect = $('#areaFilter');
                    areaSelect.empty().append('<option value="">All Areas</option>');
                    table.draw();
                }
            });

             table.on('draw.dt', function() {
                var info = table.page.info();
                var virtualRowIdx = 1;
                // Iterate over the rows that are currently visible
                table.rows({ page: 'current', search: 'applied' }).every(function(rowIdx) {
                    // Get the DOM node for the current row
                    var rowNode = this.node();
                    
                    // Calculate the new sequential ID
                    var newId = info.start + virtualRowIdx;
                    virtualRowIdx++;
                    // Update the first cell (the ID column) with the new ID
                    switch (newId){
                        case 1:
                            $('td:eq(0)', rowNode).html(newId+'&nbsp;🥇');
                            break;
                        case 2:
                            $('td:eq(0)', rowNode).html(newId+'&nbsp;🥈');
                            break;
                        case 3:
                            $('td:eq(0)', rowNode).html(newId+'&nbsp;🥉');
                            break;
                        default:
                            $('td:eq(0)', rowNode).html(newId);
                            break;
                    }
                });
            });
           
		} 