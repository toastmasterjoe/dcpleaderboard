<?php
/**
 * Plugin Name: DCP Leaderboard
 * Plugin URI:  https://example.com/my-first-plugin/ 
 * Description: A DCP leaderboard created through the toastmasters dashboard export functionality.
 * Version:     1.0.0
 * Author:      Joseph Galea
 * Author URI:  https://example.com/
 * License:     GPLv3 or later
 * Text Domain: dcpleaderboard-plugin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('PLUGIN_RELATIVE_PATH','/'.str_replace( ABSPATH, '', plugin_dir_path( __FILE__ ) ));

require_once plugin_dir_path( __FILE__ ) . '/admin/dcpleaderboard_options.php'; 
require_once plugin_dir_path( __FILE__ ) . '/admin/dcpleaderboard-rest-api.php'; 
require_once plugin_dir_path( __FILE__ ) . '/database_setup.php'; 

//https://developer.wordpress.org/reference/functions/add_menu_page/
function dcpleaderboard_register_settings() {
    add_option( 'dcpleaderboard_export_url', 'https://dashboards.toastmasters.org/export.aspx?type=CSV&report=clubperformance~109~1/31/2025~~2024-2025');
    register_setting( 'dcpleaderboard_options_group', 'dcpleaderboard_export_url', 'dcpleaderboard_callback' );
}
add_action( 'admin_init', 'dcpleaderboard_register_settings' );

function dcpleaderboard_register_settings_page() {
    add_options_page('DCP Leadearboard Settings', 'DCP Leaderboard - Settings', 'manage_options', 'dcpleaderboard', 'dcpleaderboard_options_page');
}
add_action( 'admin_menu', 'dcpleaderboard_register_settings_page' );
add_action( 'admin_enqueue_scripts', 'dcpleaderboard_enqueue_scripts' );
?>