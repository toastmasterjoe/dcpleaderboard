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
?>

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
  <table>
  <tr valign="top">
  <th scope="row"><label for="dcpleaderboard_export_url">Export URL</label></th>
  <td><input type="text" size=255 id="dcpleaderboard_export_url" name="dcpleaderboard_export_url" value="<?php echo get_option('dcpleaderboard_export_url'); ?>" /></td>
  </tr>
  <th scope="row"><label for="dcpleaderboard_district">District</label></th>
  <td><input type="text" size=255 id="dcpleaderboard_district" name="dcpleaderboard_district" value="<?php echo get_option('dcpleaderboard_district'); ?>" /></td>
  </tr>
  </table>
  <?php  submit_button(); ?>
  </form>
  <div>
    
     <button id="my-submit" onclick="onMySubmitClick(event);">Synchronize Club Data</button>
    
  </div>
</div>