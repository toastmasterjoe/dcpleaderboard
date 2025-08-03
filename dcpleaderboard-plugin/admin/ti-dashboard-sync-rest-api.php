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
require_once plugin_dir_path( __FILE__ ) . 'ti-dashboard-sync.php'; 
add_action('rest_api_init', 'register_admin_routes');

function register_admin_routes() {
    register_rest_route( 'dcpleaderboard/v1', '/endpoint/admin/clubs', array(
      'methods' => 'GET', // or POST, PUT, DELETE, etc.
      'callback' => 'dashboard_sync_endpoint_callback',
      'permission_callback' => 'dcp_admin_check', // For testing, adjust for production!
      /*'args' => array( // Optional arguments for the endpoint
        'id' => array(
          'validate_callback' => 'rest_validate_request_arg',
        ),
      ),*/
    ));
      
    /*register_rest_route( 'my-plugin/v1', '/endpoint/(?P<id>\d+)', array(
      'methods' => 'GET', // or POST, PUT, DELETE, etc.
      'callback' => 'my_custom_endpoint_callback_with_id',
      'permission_callback' => '__return_true', // For testing, adjust for production!
      'args' => array( // Optional arguments for the endpoint
        'id' => array(
          'validate_callback' => 'rest_validate_request_arg',
        ),
      ),
    ));*/
}

function dcp_admin_check () {
    return current_user_can( 'manage_options' ); // Example: test for admin user
    // Or for a custom capability:
    // return current_user_can( 'manage_my_plugin_data' );
};

function dashboard_sync_endpoint_callback(WP_REST_Request $request ) {
    // Get parameters from the request (if any)
    //$param1 = $request->get_param( 'param1' );
    // Perform your logic here (e.g., database queries, calculations, etc.)
    $result = dashboard_sync();
    return rest_ensure_response( $result );
}



?>