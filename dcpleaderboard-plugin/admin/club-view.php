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

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


require_once plugin_dir_path( __FILE__ ) . '/options_page_register.php'; 
require_once plugin_dir_path( __FILE__ ) . '/../clubs.php'; 


function render_dcp_leaderboard_main_admin(){
    dcpleaderboard_options_page();
}

function render_dcp_leaderboard_club_view_admin(){
    echo '<div class="wrap"><h1>Advanced User Table</h1>';

    $table = new Club_View_Table();
    $table->prepare_items();

    echo '<form method="post">';
    $table->search_box('Search Clubs', 'club_search');
    $table->display();
    echo '</form>';

    echo '</div>';
}

class Club_View_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct([
            'singular' => 'club',
            'plural'   => 'clubs',
            'ajax'     => true
        ]);
    }

    public function get_columns() {
        return [
            'cb'    => '<input type="checkbox" />',
            'id'  => 'Id',
            'club_number' => 'Club Number',
            'club_name' => 'Club Name'
        ];
    }

    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="club[]" value="%s" />', $item['id']);
    }

    public function column_default($item, $column_name) {
        return $item[$column_name];
    }

    public function get_bulk_actions() {
        return [
            'delete' => 'Delete'
        ];
    }

    public function process_bulk_action() {
        if ($this->current_action() === 'delete' && !empty($_POST['user'])) {
            foreach ($_POST['user'] as $user_id) {
                wp_delete_user($user_id);
            }
            echo '<div class="updated"><p>Selected users deleted.</p></div>';
        }
    }

    public function prepare_items() {
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        // Get users
        $args = [
            'number' => $per_page,
            'offset' => ($current_page - 1) * $per_page,
            'search' => $search,
            'search_columns' => ['club_number', 'club_name']
        ];
        
        $clubDriver = new Clubs();
        $clubs = $clubDriver->getClubsByPageAndSearch($args);
        $total_clubs = $clubs->record_count;

        $data = [];
        foreach ($clubs->data as $club) {
            $data[] = [
                'id'    => $club->id,
                'club_number'  => $club->club_number,
                'club_name' => $club->club_name,
            ];
        }

        $this->items = $data;
        $this->_column_headers = [$this->get_columns(), [], []];
        $this->set_pagination_args([
            'total_items' => $total_clubs,
            'per_page'    => $per_page
        ]);

        $this->process_bulk_action();
    }
   
}

?>