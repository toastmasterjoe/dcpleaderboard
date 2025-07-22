<?php

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
            'title' => 'Default Title',
            'color' => 'blue',
        ),
        $atts,
        'dcpleaderboard_content'
    );

    $clubsDriver = new Clubs();
    $clubs=$clubsDriver->get_all_clubs();
    error_log(print_r($clubs,true));

    // Sanitize attributes for safe output
    $title = esc_html($atts['title']);
    $color = esc_attr($atts['color']); // Use esc_attr for HTML attributes

    // Start output buffering to capture HTML
    ob_start();
    ?>
    <div style="border: 1px solid #ccc; padding: 15px; margin: 15px 0; background-color: #f9f9f9; border-radius: 8px;">
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
        if(count($clubs) > 0){
            for ($index = 0; $index < count($clubs); $index++) {
                ?>
                <tr>
                    <td><?=$index?></td>
                    <td><?=wp_kses_post($clubs[$index]->division) // Sanitize enclosed content?></td>
                    <td><?=wp_kses_post($clubs[$index]->area)?></td>
                    <td><?=wp_kses_post($clubs[$index]->club_name)?></td>
                    <td><?=wp_kses_post($clubs[$index]->goals_met)?></td>
                    <td><?=wp_kses_post($clubs[$index]->ti_status)?></td>
                </tr>    
            <?php    
            }
        } else {
            //TODO replace with message to sync clubs
            error_log("no clubs");
        }
        ?>
            </tbody>
        </table>
    </div>
    <?php
    // Return the buffered content
    return ob_get_clean();
}
