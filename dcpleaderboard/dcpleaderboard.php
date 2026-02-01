<?php
/**
 * Plugin Name: DCP Leaderboard
 * Plugin URI: https://github.com/toastmasterjoe/dcpleaderboard
 * Description: A DCP leaderboard created through the toastmasters dashboard export functionality.
 * Version: 2.4.18
 * Update URI: https://raw.githubusercontent.com/toastmasterjoe/dcpleaderboard/refs/heads/main/plugin-update.json
 * Author: Joseph Galea
 * Author URI: https://toastmaster.joegalea.me/
 * License: GPLv3 or later
 * Requires PHP: 7.4
 * Requires at least: 6.6.1
 * Text Domain: dcpleaderboard
 */

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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

ini_set('display_errors', 1);

define('PLUGIN_RELATIVE_PATH','/'.str_replace( ABSPATH, '', plugin_dir_path( __FILE__ ) ));


require_once plugin_dir_path( __FILE__ ) . 'admin/options_page_register.php'; 
require_once plugin_dir_path( __FILE__ ) . 'admin/club-view.php'; 
require_once plugin_dir_path( __FILE__ ) . 'admin/ti-dashboard-sync-rest-api.php'; 
require_once plugin_dir_path( __FILE__ ) . 'admin/ti_dashboard-sync-cron.php'; 
require_once plugin_dir_path( __FILE__ ) . 'database-tables-setup.php'; 
require_once plugin_dir_path( __FILE__ ) . 'dcpleaderboard-render.php'; 
require_once plugin_dir_path( __FILE__ ) . 'districtleaderboard-render.php'; 
require_once plugin_dir_path( __FILE__ ) . 'leaderboard-render-legacy.php'; 
require_once plugin_dir_path( __FILE__ ) . 'leaderboard-rest-api.php'; 
require_once plugin_dir_path( __FILE__ ) . 'plugin-updates.php'; 



function wp_dcpleaderboard_activate(){
    wp_dcpleaderboard_install_db();
    wp_dcpleaderboard_cron_activation();
}
register_activation_hook( __FILE__ , 'wp_dcpleaderboard_activate' );

function wp_dcpleaderboard_deactivate(){
    wp_dcpleaderboard_cron_deactivation();
}
register_deactivation_hook( __FILE__, 'wp_dcpleaderboard_deactivate' );

function wp_dcpleaderboard_uninstall() {
    //wp_dcpleaderboard_uninstall_db();
}
register_uninstall_hook( __FILE__, 'wp_dcpleaderboard_uninstall' );


//https://developer.wordpress.org/reference/functions/add_menu_page/
function dcpleaderboard_register_settings() {
    add_option( 'dcpleaderboard_district', '109');
    register_setting( 'dcpleaderboard_options_group', 'dcpleaderboard_district', 'dcpleaderboard_sanitize_callback' );
    add_option( 'dcpleaderboard_division', 'A');
    register_setting( 'dcpleaderboard_options_group', 'dcpleaderboard_division', 'dcpleaderboard_santize_callback' );
    add_action( 'update_option_dcpleaderboard_district', 'dcpleaderboard_district_changed',10,3);
}
add_action( 'admin_init', 'dcpleaderboard_register_settings' );

function dcpleaderboard_register_settings_page() {
    add_options_page('DCP Leadearboard Settings', 'DCP Leaderboard - Settings', 'manage_options', 'dcpleaderboard', 'dcpleaderboard_options_page');
    add_menu_page(
        'DCP Leader Board',        // Page title
        'DCP Leader Board',           // Menu title
        'manage_options',        // Capability
        'dcp-leaderboard',        // Menu slug
        'render_dcp_leaderboard_main_admin',// Callback function
        'dashicons-analytics', // Icon (optional)
        2                        // Position (optional)
    );
    add_submenu_page(
        'dcp-leaderboard',           // Parent slug
        'Club View',             // Page title
        'Club View',                  // Menu title
        'manage_options',           // Capability
        'club-view',          // Menu slug
        'render_dcp_leaderboard_club_view_admin'   // Callback function
    );
}

add_action( 'admin_menu', 'dcpleaderboard_register_settings_page' );
add_action( 'admin_enqueue_scripts', 'dcpleaderboard_admin_enqueue_scripts' );

function dcpleaderboard_content_shortcode_init() {
    add_shortcode('dcpleaderboard_content_legacy', 'dcpleaderboard_content_legacy_shortcode_callback');
    add_shortcode('dcpleaderboard_content', 'dcpleaderboard_content_shortcode_callback');
    add_shortcode('districtleaderboard_content', 'districtleaderboard_content_shortcode_callback');
}
add_action('init', 'dcpleaderboard_content_shortcode_init');

/*function dcpleaderboard_plugin_enqueue_styles() {
    wp_enqueue_style(
        'dcpleaderboard-style', // Handle name
        plugin_dir_url(__FILE__) . 'dcpleaderboard-styles.css' // Path to the file
    );
}
add_action('wp_enqueue_scripts', 'dcpleaderboard_plugin_enqueue_styles');*/

function dcpleaderboard_enqueue_scripts() {
    wp_enqueue_script('jquery');
    //wp_enqueue_script('my-custom-script', plugin_dir_url(__FILE__) . 'js/my-script.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'dcpleaderboard_enqueue_scripts');



?>