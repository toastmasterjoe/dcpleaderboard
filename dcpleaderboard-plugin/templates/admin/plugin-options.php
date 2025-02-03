<!--
<script src="<?php echo plugins_url('js/remote.js', __FILE__ ); ?>"></script>
-->
<div>
  <?php screen_icon(); ?>
  <h2>DCP Leaderboard Settings</h2>
  <form method="post" action="options.php">
  <?php settings_fields( 'dcpleaderboard_options_group' ); ?>
  <h3>Toastmasters Dashboard Data Export</h3>
  <p>The url will provide the url to use to extract data from the dashboard in csv format</p>
  <tr valign="top">
  <th scope="row"><label for="dcpleaderboard_export_url">Export URL</label></th>
  <td><input type="text" size=255 id="dcpleaderboard_export_url" name="dcpleaderboard_export_url" value="<?php echo get_option('dcpleaderboard_export_url'); ?>" /></td>
  </tr>
  </table>
  <?php  submit_button(); ?>
  </form>
  <div>
    
     <button id="my-submit" onclick="onMySubmitClick(event);">submit</button>
    
  </div>
</div>