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
    wp_enqueue_script('jquery');
   
    wp_enqueue_script(
        'bootstrap', // handle
        plugin_dir_url(__FILE__)  . 'js/bootstrap.bundle.min.js', // script path
        array(), // dependencies
        '5.3.7', // version
        true // load in footer
    );

    wp_enqueue_script(
        'datatables', // handle
        plugin_dir_url(__FILE__)  . 'js/datatables.min.js', // script path
        array('jquery', 'bootstrap'), // dependencies
        '2.3.2', // version
        true // load in footer
    );

     wp_enqueue_script(
        'leaderboard-render', // handle
        plugin_dir_url(__FILE__)  . 'js/leaderboard-render.js', // script path
        array('jquery', 'bootstrap','datatables'), // dependencies
        '1.0.0', // version
        false // do not load in footer
    );

    wp_enqueue_script(
        'dcp-leaderboard-render', // handle
        plugin_dir_url(__FILE__)  . 'js/dcp-leaderboard-render.js', // script path
        array('jquery', 'bootstrap','datatables'), // dependencies
        '1.0.0', // version
        false // do not load in footer
    );

    wp_enqueue_style(
        'bootstrap', // Handle name
        plugin_dir_url(__FILE__) . 'css/bootstrap.min.css' // Path to the file
    );

    wp_enqueue_style(
        'datatables', // Handle name
        plugin_dir_url(__FILE__) . 'css/datatables.min.css' // Path to the file
    );

    wp_enqueue_style(
        'dcpleaderboard-style', // Handle name
        plugin_dir_url(__FILE__) . 'css/dcpleaderboard-styles.css' // Path to the file
    );
    
    // Handle filters
    /*$division = isset($_GET['division']) ? sanitize_text_field($_GET['division']) : '';
    $area = isset($_GET['area']) ? sanitize_text_field($_GET['area']) : '';

    $current_page = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;*/

    // Sanitize attributes for safe output
    $title = esc_html($atts['title']);
    $color = esc_attr($atts['color']); // Use esc_attr for HTML attributes

    // Start output buffering to capture HTML
    ob_start();
  ?>
    <script type="text/javascript">
        jQuery(document).ready(($)=>init_document($));
    </script>
    <!--<div style="border: 1px solid #ccc; padding: 15px; margin: 15px 0; background-color: #f9f9f9; border-radius: 8px;">-->
    <div class="modern-table-container">    
        <div class="custom-table-filter">
            <label for="divisionFilter">Division:</label>
            <select id="divisionFilter" class="dt-input">
                <option value="">All Divisions</option>
            </select>

            <label for="areaFilter">Area:</label>
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
                    <th>Eligible</th>
                    <th>DCP Goals</th>
                </tr>
            </thead>
        </table>
    </div>
    <?php
    // Return the buffered content
    return ob_get_clean();
}

