<?php
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

?>