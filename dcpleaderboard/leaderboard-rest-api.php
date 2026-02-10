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
if (! defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . './clubs.php';
require_once plugin_dir_path(__FILE__) . './district-point-engine/points-club-view.php';
add_action('rest_api_init', 'register_routes');

function register_routes()
{
    register_rest_route('dcpleaderboard/v1', '/clubs', [
        'methods'  => 'GET', // or POST, PUT, DELETE, etc.
        'callback' => 'clubs_leaderboard_endpoint_callback',
    ]);

    register_rest_route('dcpleaderboard/v1', '/divisions', [
        'methods'  => 'GET', // or POST, PUT, DELETE, etc.
        'callback' => 'divisions_endpoint_callback',
    ]);

    register_rest_route('dcpleaderboard/v1', '/areas', [
        'methods'  => 'GET', // or POST, PUT, DELETE, etc.
        'callback' => 'areas_endpoint_callback',
    ]);

    register_rest_route('districtleaderboard/v1', '/club/(?P<club_id>\d+)/goals', [
        'methods'  => 'GET', // or POST, PUT, DELETE, etc.
        'callback' => 'club_district_points_endpoint_callback',
        'args'     => [ // Optional arguments for the endpoint
            'club_id' => [
                'required' => true,
            ],
        ],
    ]);

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

function divisions_endpoint_callback(WP_REST_Request $request)
{
    $allDivisions          = [];
    $clubsDriver           = new Clubs();
    $divisions_in_district = $clubsDriver->get_divisions();
    foreach ($divisions_in_district as $field => $this_division) {
        $division_name = $this_division['division'];
        array_push($allDivisions, $division_name);
    }
    return rest_ensure_response($allDivisions);
}

function areas_endpoint_callback(WP_REST_Request $request)
{
    // Get parameters from the request (if any)
    //$param1 = $request->get_param( 'param1' );
    // Perform your logic here (e.g., database queries, calculations, etc.)

    $division = $request->has_param('division') ? sanitize_text_field($request->get_param('division')) : '';
    if ($division) {
        $allAreas          = [];
        $clubsDriver       = new Clubs();
        $areas_in_division = $clubsDriver->get_areas($division);
        foreach ($areas_in_division as $field => $this_area) {
            $area_name = $this_area['area'];
            array_push($allAreas, $area_name);
        }
        return rest_ensure_response($allAreas);
    } else {
        $error = new WP_Error(
            'missing_parameter',                                        // Error code (string)
            __('The "division" parameter is required.', 'text-domain'), // Error message (translatable)
            ['status' => 400]                                           // HTTP status code
        );

        // Return a WP_REST_Response with the WP_Error object
        return new WP_REST_Response($error, 400);
    }
}

function clubs_leaderboard_endpoint_callback(WP_REST_Request $request)
{
    // Get parameters from the request (if any)
    //$param1 = $request->get_param( 'param1' );
    // Perform your logic here (e.g., database queries, calculations, etc.)

    $division = $request->has_param('division') ? sanitize_text_field($request->get_param('division')) : '';
    $area     = $request->has_param('area') ? sanitize_text_field($request->get_param('area')) : '';

    $current_page   = $request->has_param('page') ? sanitize_text_field($request->get_param('page')) : 1;
    $items_per_page = 20; // TODO decide how to make this common for shortcode and api

    $clubsDriver = new Clubs();
    if ($request->has_param('page')) {

        $clubs = $clubsDriver->get_all_clubs_paged($items_per_page, $current_page, $division, $area);
    } else {
        $orderByDCP = ! $request->has_param('district_mode');
        $clubs      = Clubs::getAllClubs($orderByDCP);
    }

    return rest_ensure_response($clubs);
}

function club_district_points_endpoint_callback(WP_REST_Request $request)
{
    // Get parameters from the request (if any)
    //$param1 = $request->get_param( 'param1' );
    // Perform your logic here (e.g., database queries, calculations, etc.)

    $club_id = $request->has_param('club_id') ? sanitize_text_field($request->get_param('club_id')) : '';
    if ($club_id) {
        $district_points = PointsClubView::getDistrictClubPoints($club_id);
        return rest_ensure_response($district_points);
    } else {
        return new WP_Error('missing_parameter', 
                  __('The "club_id" parameter is required.', 'text-domain'), 
                  ['status' => 400]);
    }
}
