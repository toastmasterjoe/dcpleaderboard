<?php
/*
 * DCP Leaderboard Plugin
 * Copyright (C) 2025 Joseph Galea
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

// Exit if accessed directly to prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'clubs.php'; 


/**
 * Callback function for the `dcpleaderboard_content` shortcode.
 * This function generates the content that will be embedded.
 *
 * @param array $atts Attributes passed to the shortcode.
 * @param string $content Content enclosed within the shortcode tags (if any).
 * @return string The HTML content to display.
 */
function dcpleaderboard_content_shortcode_callback($atts, $content = null) {
    // Define default attributes for the shortcode
    $atts = shortcode_atts(
        array(
            'title' => 'DCP Leaderboard',
            'color' => '#004165',
            'items_per_page' => 20,
        ),
        $atts,
        'dcpleaderboard_content'
    );

    $clubsDriver = new Clubs();
    
    // Handle filters
    $division = isset($_GET['division']) ? sanitize_text_field($_GET['division']) : '';
    $area = isset($_GET['area']) ? sanitize_text_field($_GET['area']) : '';

    $current_page = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
    $clubs=$clubsDriver->get_all_clubs_paged($atts['items_per_page'], $current_page, $division, $area);

    // Sanitize attributes for safe output
    $title = esc_html($atts['title']);
    $color = esc_attr($atts['color']); // Use esc_attr for HTML attributes

    // Start output buffering to capture HTML
    ob_start();
    ?>

    

	<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css" />
  
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
            $.ajax({
                url: '<?=site_url().'/wp-json/dcpleaderboard/v1/divisions'?>',
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
                ordering:false,
                pageLength: 20,
                lengthMenu: [10, 20, 50, 100],
		      	serverMethod: 'get',
		      	ajax: {
		          	'url':'<?=site_url().'/wp-json/dcpleaderboard/v1/clubs'?>',
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
                        url: '<?=site_url().'/wp-json/dcpleaderboard/v1/areas?division='?>' + division,
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
                    $('td:eq(0)', rowNode).html(newId);
                });
            });
           
		} );

         
        
	</script>
    
    
    
    <!--<div style="border: 1px solid #ccc; padding: 15px; margin: 15px 0; background-color: #f9f9f9; border-radius: 8px;">-->
    <div class="modern-table-container">    
        <div class="custom-table-filter">
            <label for="division">Division:</label>
            <select id="divisionFilter" class="dt-input">
                <option value="">All Divisions</option>
            </select>

            <label for="area">Area:</label>
            <select id="areaFilter" class="dt-input">
                <option value="">All Areas</option>
            </select>
        </div>
        <h3 style="color: <?php echo $color; ?>;"><?php echo $title; ?></h3>
        <table id="club_leaderboard">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Division</th>
                    <th>Area</th>
                    <th>Club</th>
                    <th>Status</th>
                    <th>DCP Goals</th>
                    <!--<th>-</th>-->
                </tr>
            </thead>
        </table>
    </div>
    <?php
    // Return the buffered content
    return ob_get_clean();
}

