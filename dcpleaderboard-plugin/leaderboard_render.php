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
    
    $current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
    $clubs=$clubsDriver->get_all_clubs_paged($atts['items_per_page'], $current_page);
    error_log(print_r($clubs,true));

    // Sanitize attributes for safe output
    $title = esc_html($atts['title']);
    $color = esc_attr($atts['color']); // Use esc_attr for HTML attributes

    $pagination_args = array(
    'base'      => add_query_arg( 'paged', '%#%' ),
    'format'    => '',
    'total'     => $clubs->pages,
    'current'   => $current_page,
    'prev_text' => __( '&laquo; Previous', 'text-domain' ),
    'next_text' => __( 'Next &raquo;', 'text-domain' ),
    );


    // Start output buffering to capture HTML
    ob_start();
    ?>
    <div class="custom-table-pagination"><?=paginate_links( $pagination_args )?></div>
    <!--<div style="border: 1px solid #ccc; padding: 15px; margin: 15px 0; background-color: #f9f9f9; border-radius: 8px;">-->
    <div class="modern-table-container">    
        <h3 style="color: <?php echo $color; ?>;"><?php echo $title; ?></h3>
        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Division</th>
                    <th>Area</th>
                    <th>Club</th>
                    <th>Goals</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            
        <?php
        if(count($clubs->data) > 0){
            for ($index = 0; $index < count($clubs->data); $index++) {
                ?>
                <tr>
                    <td><?= ($index + 1) + ($current_page - 1) * $atts['items_per_page'] ?></td>
                    <td><?=wp_kses_post($clubs->data[$index]->division) // Sanitize enclosed content?></td>
                    <td><?=wp_kses_post($clubs->data[$index]->area)?></td>
                    <td><?=wp_kses_post($clubs->data[$index]->club_name)?></td>
                    <td><?=wp_kses_post($clubs->data[$index]->goals_met)?></td>
                    <td><?=wp_kses_post($clubs->data[$index]->ti_status)?></td>
                </tr>    
            <?php    
            }
        } else {
            //TODO replace with message to sync clubs
            ?>
            <tr>
                    <td rowspan="6">Club data is missing, contact the adminsitrator to sycnhronize data from dashboard</td>
            </tr>
            <?php
        }
        ?>
            </tbody>
        </table>
    </div>
    <div class="custom-table-pagination"><?=paginate_links( $pagination_args )?></div>
    <?php
    // Return the buffered content
    return ob_get_clean();
}

