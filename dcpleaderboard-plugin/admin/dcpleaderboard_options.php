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
function dcpleaderboard_options_page()
{
  $template_file = locate_template( 'dcpleaderboard-plugin/templates/admin/plugin-options.php' );
  if ( $template_file ) {
      include( $template_file ); 
  } else {
      // Fallback: Include the template file from your plugin's directory
      include( plugin_dir_path( __FILE__ ) . '../templates/admin/plugin-options.php' ); 
  }
//$csv = file_get_contents(get_option('dcpleaderboard_export_url'));
} 

function dcpleaderboard_enqueue_scripts() {
  // 1. Enqueue your JavaScript file:
  wp_enqueue_script( 'remote_script',  PLUGIN_RELATIVE_PATH.'/templates/admin/js/remote.js', array(), null, true ); // 'true' puts the script in the footer
  // 2. Localize the data:
  wp_localize_script( 'remote_script', 'remote_script_params', array(
      'api_url' => rest_url(),
      'nonce' => wp_create_nonce( 'wp_rest' ),
  ) );
}

?>