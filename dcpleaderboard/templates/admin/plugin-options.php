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
  <h2>DCP Leaderboard Settings</h2>
  <form method="post" action="options.php">
  <?php settings_fields( 'dcpleaderboard_options_group' ); ?>
  <h3>Toastmasters Dashboard Data Export</h3>
  <p>The District number is required to build the url</p>
  <table>
  <tr>
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