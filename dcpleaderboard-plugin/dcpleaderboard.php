<?php
/**
 * Plugin Name: DCP Leaderboard
 * Plugin URI:  https://example.com/my-first-plugin/ 
 * Description: A DCP leaderboard created through the toastmasters dashboard export functionality.
 * Version:     0.1.0
 * Author:      Joseph Galea
 * Author URI:  https://example.com/
 * License:     GPLv3 or later
 * Text Domain: dcpleaderboard-plugin
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

//echo plugin_dir_path( __FILE__ ) . 'database_setup.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/dcpleaderboard_options.php'; 
require_once plugin_dir_path( __FILE__ ) . 'admin/dcpleaderboard-rest-api.php'; 
require_once plugin_dir_path( __FILE__ ) . 'database_setup.php'; 
require_once plugin_dir_path( __FILE__ ) . 'leaderboard_render.php'; 

register_activation_hook( __FILE__ , 'wp_dcpleaderboard_clubs_create_table' );
register_deactivation_hook( __FILE__, 'wp_dcpleaderboard_clubs_delete_table' );

//https://developer.wordpress.org/reference/functions/add_menu_page/
function dcpleaderboard_register_settings() {
    error_log(">>dcpleaderboard_register_settings");
    add_option( 'dcpleaderboard_export_url', 'https://dashboards.toastmasters.org/export.aspx?type=CSV&report=clubperformance~109~1/31/2025~~2024-2025');
    register_setting( 'dcpleaderboard_options_group', 'dcpleaderboard_export_url', 'dcpleaderboard_sanitize_callback' );
    add_option( 'dcpleaderboard_division', 'A');
    register_setting( 'dcpleaderboard_options_group', 'dcpleaderboard_division', 'dcpleaderboard_santize_callback' );
}
add_action( 'admin_init', 'dcpleaderboard_register_settings' );

function dcpleaderboard_register_settings_page() {
    add_options_page('DCP Leadearboard Settings', 'DCP Leaderboard - Settings', 'manage_options', 'dcpleaderboard', 'dcpleaderboard_options_page');
}
add_action( 'admin_menu', 'dcpleaderboard_register_settings_page' );
add_action( 'admin_enqueue_scripts', 'dcpleaderboard_enqueue_scripts' );

function dcpleaderboard_content_shortcode_init() {
    add_shortcode('dcpleaderboard_content', 'dcpleaderboard_content_shortcode_callback');
}
add_action('init', 'dcpleaderboard_content_shortcode_init');

function dcpleaderboard_plugin_enqueue_styles() {
    wp_enqueue_style(
        'dcpleaderboard-style', // Handle name
        plugin_dir_url(__FILE__) . 'dcpleaderboard-styles.css' // Path to the file
    );
}
add_action('wp_enqueue_scripts', 'dcpleaderboard_plugin_enqueue_styles');
?>