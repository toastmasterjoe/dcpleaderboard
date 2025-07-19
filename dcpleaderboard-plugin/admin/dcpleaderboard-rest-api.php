<?php
    require_once plugin_dir_path( __FILE__ ) . '/../toastmasters_util.php'; 
    require_once plugin_dir_path( __FILE__ ) . '/../general_utils.php'; 

    $memoryStream = ''; 

    add_action('rest_api_init', 'register_routes');
    function register_routes() {
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
        $url = build_dashboard_url_club_progress();
        $content = downloadRemoteFileAsStream($url);
        $result = processCsvString($content);
        
        $data = array(
            'status' => 'success',
            'data' => $result,
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

      function downloadRemoteFileAsStream($remoteUrl) {
        global $memoryStream;
        $memoryStream = '';
        // Initialize cURL session
        $ch = curl_init($remoteUrl);
        
    
        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, // Return the transfer as a string
            CURLOPT_HEADER => false,       // Don't include the header in the output
            CURLOPT_FOLLOWLOCATION => true, // Follow redirects if any
            CURLOPT_CONNECTTIMEOUT => 30,   // Connection timeout in seconds
            CURLOPT_TIMEOUT => 60,          // Transfer timeout in seconds
            CURLOPT_BUFFERSIZE => 8192,     // Buffer size for reading data in chunks (adjust as needed)
            // Important for large files:  Stream the download
            CURLOPT_WRITEFUNCTION => 'readFileChunk',
            CURLOPT_NOPROGRESS => false, // Allow progress reporting (optional)
            CURLOPT_PROGRESSFUNCTION => function ($resource, $download_size, $downloaded, $upload_size, $uploaded) {
                // This callback can be used to display download progress.
                if ($download_size > 0) {
                    $progress = ($downloaded / $download_size) * 100;
                    // You can log or display $progress here.  Be mindful of performance in production.
                    // Example: error_log("Download Progress: " . $progress . "%");
                }
            },
        ]);
    
        // Execute the cURL session
        $result = curl_exec($ch);
    
        // Check for errors
        if ($result === false) {
            error_log(curl_error($ch)); // Log the error for debugging
            return false; // Or throw an exception
        }
    
        // Get content type and length (for setting headers later if needed)
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    
        curl_close($ch);    
        error_log($memoryStream);    
        return $memoryStream;
    }

    function readFileChunk($ch, $str) {
        global $memoryStream;
        $memoryStream .= $str;
        return strlen($str); // Return the number of bytes written.    
    }
?>