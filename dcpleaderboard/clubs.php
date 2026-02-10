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
class Clubs {
    // https://g.co/gemini/share/415f9ef8785e plugin in class
    private static string $tableName = "";
    private static string $district = "";

    private static function load(){
        if(empty(self::$tableName)) {
            global $wpdb;
            self::$tableName = $wpdb->prefix . 'dcpleaderboard_clubs'; // Replace with your table name
        }
        if(empty(self::$district)){
            self::$district = get_option('dcpleaderboard_district');
        }
    }
    public function __construct(){
        self::load();
    }

    public function get_divisions() {

        //TODO: add caching;
        global $wpdb;
        $tableName=self::$tableName;         
        // Prepared statement (Highly recommended for security):
        $sql = $wpdb->prepare( "SELECT distinct division FROM {$tableName}  where district = %s order by division", self::$district );
        $row = $wpdb->get_results( $sql, ARRAY_A ); 
        return $row;
    }

    public function get_areas($division) {
         //TODO: add caching;
        global $wpdb;
        $tableName=self::$tableName;
        // Prepared statement (Highly recommended for security):
        $sql = $wpdb->prepare( "SELECT distinct area FROM {$tableName} WHERE district = %s and division = %s order by area", self::$district, $division );
        $row = $wpdb->get_results( $sql, ARRAY_A ); 
        return $row;
    }

    public function get_club_by_number($clubNumber){
        global $wpdb;
        $tableName=self::$tableName;
        // Prepared statement (Highly recommended for security):
        $sql = $wpdb->prepare( "SELECT * FROM {$tableName} WHERE club_number = %s", $clubNumber );
        $row = $wpdb->get_row( $sql, ARRAY_A ); 
        return $row;
    }

    public function get_club_by_id($clubId){
        global $wpdb;
        $tableName=self::$tableName;
        // Prepared statement (Highly recommended for security):
        $sql = $wpdb->prepare( "SELECT * FROM {$tableName} WHERE id = %d", $clubId );
        $row = $wpdb->get_row( $sql, ARRAY_A ); 
        return $row;
    }

    public static function getAllClubs(bool $orderByDCP = true){
        self::load();
         //TODO: add caching;
        global $wpdb;
        
        $sql = "";
        $table = self::$tableName;
        if($orderByDCP){
            $sql = "SELECT * FROM {$table} where district = %s ORDER BY goals_met DESC, score_achieved_at ASC";
        } else {
            $sql = "SELECT * FROM {$table} where district = %s ORDER BY district_goals_met DESC, district_score_achieved_at ASC";
        }
        
        // Prepared statement (Highly recommended for security):
        $preparedStatement = $wpdb->prepare( $sql, self::$district);
        $rows = $wpdb->get_results( $preparedStatement, OBJECT ); 
        return $rows;
    }

    public function getClubsByPageAndSearch(array $args){
        global $wpdb;
        $offset = $args['offset'];
        $items_per_page = $args['number'];
        $columns = $args['search_columns'];
        $search_term = $args['search'];
        
        
        $params = [self::$district];
        $where = [];

        if($search_term) {
            foreach ($columns as $column) {
                $where[] = "{$column} LIKE %s";
                $params[] = "%{$search_term}%";
            }
        }
        
        $where_sql = 'WHERE district = %s ' . ($search_term ? 'AND ' : '') . implode(' OR ', $where);
        
        $tableName=self::$tableName;
        $count_sql = "SELECT count(id) FROM {$tableName} $where_sql";
        $total_items = $wpdb->get_var( $wpdb->prepare($count_sql, ...$params) );

        $data_sql ="SELECT * FROM {$tableName} $where_sql  LIMIT %d OFFSET %d";
        $params[] = $items_per_page;
        $params[] = $offset;
        // Prepared statement (Highly recommended for security):
        $sql = $wpdb->prepare($data_sql, ...$params);
        $rows = $wpdb->get_results( $sql, OBJECT ); 
        error_log($wpdb->last_query);
        return (object)array (
            "pages" => ceil( $total_items / $items_per_page ), 
            "record_count" => $total_items,
            "data" => $rows
        );
    }

    public function get_all_clubs_paged($items_per_page, $current_page, $division, $area){
        global $wpdb;
        $offset = ( $current_page - 1 ) * $items_per_page;

        // Build WHERE clause
        $where = ["district = %s"];
        $params = [self::$district];
        
        if ($division) {
            $where[] = "division = %s";
            $params[] = $division;
        }
        if ($area) {
            $where[] = "area = %s";
            $params[] = $area;
        }
        
        $where_sql = '';
        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }
        $tableName=self::$tableName;
        $count_sql = "SELECT count(id) FROM {$tableName} $where_sql";
        $total_items = $wpdb->get_var( $wpdb->prepare($count_sql, ...$params) );

        $data_sql ="SELECT * FROM {$tableName} $where_sql ORDER BY goals_met DESC, score_achieved_at ASC LIMIT %d OFFSET %d";
        $params[] = $items_per_page;
        $params[] = $offset;
        // Prepared statement (Highly recommended for security):
        $sql = $wpdb->prepare($data_sql, ...$params);
        $rows = $wpdb->get_results( $sql, OBJECT ); 
        return (object)array (
            "pages" => ceil( $total_items / $items_per_page ), 
            "data" => $rows
        );
    }

    public function upsert_all_clubs($clubs){
        foreach ($clubs as $club) {
            $foundClub = $this->get_club_by_number($club['club_number']);
            if(!$foundClub){
                $this->insert_club($club);
            } else {
                $this->update_club($club, $foundClub);
            }
        }
     }
    public function insert_club($clubData){
       // https://developer.wordpress.org/reference/classes/wpdb/insert/
       global $wpdb;  // Make sure $wpdb is available
       
       // Example using placeholders (more secure and often preferred):
       $tableName = self::$tableName;
       $sql = "INSERT INTO $tableName
        (`updated_at`, `score_achieved_at`, 
        `district`, `division`, `area`, `club_number`, `club_name`, `club_status`, `csp`,
        `mem_base`, `active_members`, `net_growth`, `goals_met`,
        `level_1`, `level_2`, `add_level_2`, `level_3`, `level_4_5_DTM`, `add_level_4_5_DTM`,
        `new_members`, `add_new_members`,
        `officers_round_1`, `officers_round_2`, 
        `mem_dues_oct`, `mem_dues_apr`, `off_list_on_time`,
        `ti_status`)
        VALUES (%s, %s, 
            %s, %s, %s, %s, %s, %s, %s,
            %d, %d, %d, %d,
            %d, %d, %d, %d, %d, %d,
            %d, %d, 
            %d, %d,
            %d, %d, %d,
            %s)";
            trim($sql);
       $wpdb->query( $wpdb->prepare( $sql, current_time('mysql'), current_time('mysql'), $clubData['district'], $clubData['division'], $clubData['area'], $clubData['club_number'], $clubData['club_name'], $clubData['club_status'], $clubData['csp'], 
        $clubData['mem_base'], $clubData['active_members'], $clubData['net_growth'], $clubData['goals_met'],
        $clubData['level_1'], $clubData['level_2'], $clubData['add_level_2'], $clubData['level_3'], $clubData['level_4_5_DTM'], $clubData['add_level_4_5_DTM'],
        $clubData['new_members'], $clubData['add_new_members'],
        $clubData['officers_round_1'], $clubData['officers_round_2'], 
        $clubData['mem_dues_oct'], $clubData['mem_dues_apr'], $clubData['off_list_on_time'],
        $clubData['ti_status'] ) );
       
       if ($wpdb->last_error) {
           error_log("Database insert error: " . $wpdb->last_error);
       } else {
           $inserted_id = $wpdb->insert_id;
           return $inserted_id;
       }
       return 0;
    }
    public function update_club($newClubData, $currentClubData) {
        // https://developer.wordpress.org/reference/classes/wpdb/update/
        global $wpdb;  // Make sure $wpdb is available
        $tableName = self::$tableName;
        $sql = "UPDATE $tableName SET
                `updated_at` = %s, `score_achieved_at` = %s, 
                `district` = %s, `division`= %s, `area`=%s, 
                `club_name` = %s, `club_status` = %s, `csp` = %s, 
                `mem_base` = %d, `active_members` = %d, `net_growth` = %d, `goals_met` = %d,
                `level_1` = %d, `level_2` = %d, `add_level_2` = %d, 
                `level_3` = %d, `level_4_5_DTM` = %d, `add_level_4_5_DTM` = %d,
                `new_members` = %d, `add_new_members` = %d,
                `officers_round_1` = %d, `officers_round_2` = %d, 
                `mem_dues_oct` = %d, `mem_dues_apr` = %d, `off_list_on_time` = %d,
                `ti_status` = %s
            WHERE id = %d;";
        $scoreAchievedAt = $newClubData['goals_met']>$currentClubData['goals_met'] ? current_time('mysql') : $currentClubData['score_achieved_at'];
        $wpdb->query( $wpdb->prepare( $sql, current_time('mysql'), $scoreAchievedAt, $newClubData['district'], $newClubData['division'], $newClubData['area'], $newClubData['club_name'], $newClubData['club_status'], $newClubData['csp'], 
                $newClubData['mem_base'], $newClubData['active_members'], $newClubData['net_growth'], $newClubData['goals_met'],
                    $newClubData['level_1'], $newClubData['level_2'], $newClubData['add_level_2'], $newClubData['level_3'], $newClubData['level_4_5_DTM'], $newClubData['add_level_4_5_DTM'],
                    $newClubData['new_members'], $newClubData['add_new_members'],
                    $newClubData['officers_round_1'], $newClubData['officers_round_2'], 
                    $newClubData['mem_dues_oct'], $newClubData['mem_dues_apr'], $newClubData['off_list_on_time'],
                    $newClubData['ti_status'], $currentClubData['id'])); 
  
        if ($wpdb->last_error) {
            error_log("Database update error: " . $wpdb->last_error);
        } else {
            $rows_affected = $wpdb->rows_affected; // Get the number of rows updated
            if($rows_affected > 1){
                error_log("Error: multiple records updated");
            } 
        }
       
    }

    public function update_club_ti_status_last_year($clubs) {
        // https://developer.wordpress.org/reference/classes/wpdb/update/
        global $wpdb;  // Make sure $wpdb is available
        $tableName = self::$tableName;
        $sql = "UPDATE $tableName SET
                `ti_status_last_year` = %s
            WHERE club_number = %s;";
        foreach ($clubs as $clubData) {
            $wpdb->query( $wpdb->prepare( $sql, 
                        $clubData['ti_status'], $clubData['club_number'])); 
            if ($wpdb->last_error) {
                error_log("Database update error: " . $wpdb->last_error);
            } else {
                $rows_affected = $wpdb->rows_affected; // Get the number of rows updated
                if($rows_affected > 1){
                    error_log("Error: multiple records updated");
                } 
            }
        }
       
    }

    public function update_club_district_points($clubDistrictPoints){
        foreach ($clubDistrictPoints as $clubNumber => $clubPoints) {
            $foundClub = $this->get_club_by_number($clubNumber);
            global $wpdb;  // Make sure $wpdb is available
            $tableName = self::$tableName;
            $sql = "UPDATE $tableName SET
                    `district_goals_met` = %d,
                    `district_score_achieved_at` = %s
                WHERE id = %d;";
            $scoreAchievedAt = $clubPoints != $foundClub['district_goals_met'] ? current_time('mysql') : $foundClub['district_score_achieved_at'];
            $wpdb->query( $wpdb->prepare( $sql, $clubPoints, $scoreAchievedAt, $foundClub['id'])); 
    
            if ($wpdb->last_error) {
                error_log("Database update error: " . $wpdb->last_error);
            } else {
                $rows_affected = $wpdb->rows_affected; // Get the number of rows updated
                if($rows_affected > 1){
                    error_log("Error: multiple records updated");
                } 
            }
        }
     }
}
?>