<?php
    add_action('rest_api_init', 'register_routes');
    function register_routes() {
        register_rest_route( 'dcpleaderboard/v1', '/endpoint/admin/dashboard/sync', array(
          'methods' => 'GET', // or POST, PUT, DELETE, etc.
          'callback' => 'dashboard_sync_endpoint_callback',
          'permission_callback' => '__return_true', // For testing, adjust for production!
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
    function dashboard_sync_endpoint_callback(WP_REST_Request $request ) {
        // Get parameters from the request (if any)
        //$param1 = $request->get_param( 'param1' );

        

        // Perform your logic here (e.g., database queries, calculations, etc.)
        $data = array(
            'result' => 'ok',
        );

        // Return the data as a REST response
        return rest_ensure_response( $data );
    }

    /*function my_custom_endpoint_callback_with_id( WP_REST_Request $request ) {
        $id = $request->get_param( 'id' );
    
        if ( ! $id ) {
            return new WP_Error( 'invalid_id', 'ID parameter is required.', array( 'status' => 400 ) );
        }
    
        // Perform your logic here (e.g., database queries, calculations, etc.)
        $data = array(
          'message' => 'Hello from my custom endpoint with ID!',
          'id' => $id,
        );
    
        // Return the data as a REST response
        return rest_ensure_response( $data );
      }*/
?>