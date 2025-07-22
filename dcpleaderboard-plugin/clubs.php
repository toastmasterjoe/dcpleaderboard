<?php
    class Clubs {
        // https://g.co/gemini/share/415f9ef8785e plugin in class
        private $table_name;

        public function __construct(){
            global $wpdb;
            $this->table_name = $wpdb->prefix . 'dcpleaderboard_clubs'; // Replace with your table name
        }

        public function get_club_by_number($clubNumber){
            global $wpdb;
                     
            // Prepared statement (Highly recommended for security):
            $sql = $wpdb->prepare( 'SELECT * FROM '.$this->table_name.' WHERE club_number = %s', $clubNumber );
            $row = $wpdb->get_row( $sql, ARRAY_A ); 

            return $row;
        }

        public function get_all_clubs(){
            global $wpdb;
            
            // Prepared statement (Highly recommended for security):
            $sql = $wpdb->prepare( "SELECT * FROM {$this->table_name} ORDER BY goals_met DESC, score_achieved_at ASC");
            $rows = $wpdb->get_results( $sql, OBJECT ); 

            return $rows;
        }

        public function get_all_clubs_paged($items_per_page){
            global $wpdb;
            $current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
            $offset = ( $current_page - 1 ) * $items_per_page;
            $total_items = $wpdb->get_var( "SELECT count(id) FROM {$this->table_name}" );
            // Prepared statement (Highly recommended for security):
            $sql = $wpdb->prepare( "SELECT * FROM {$this->table_name} ORDER BY goals_met DESC, score_achieved_at ASC LIMIT %d OFFSET %d",$items_per_page, $offset);
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
           
           $sql = "INSERT INTO $this->table_name
            (`updated_at`, `score_achieved_at`, 
            `district`, `division`, `area`, `club_number`, `club_name`, `club_status`, 
            `mem_base`, `active_members`, `goals_met`,
            `level_1`, `level_2`, `add_level_2`, `level_3`, `level_4_5_DTM`, `add_level_4_5_DTM`,
            `new_members`, `add_new_members`,
            `officers_round_1`, `officers_round_2`, 
            `mem_dues_oct`, `mem_dues_apr`, `off_list_on_time`,
            `ti_status`)
            VALUES (%s, %s, 
                %s, %s, %s, %s, %s, %s,
                %d, %d, %d,
                %d, %d, %d, %d, %d, %d,
                %d, %d, 
                %d, %d,
                %d, %d, %d,
                %s)";
                trim($sql);
           $wpdb->query( $wpdb->prepare( $sql, current_time('mysql'), current_time('mysql'), $clubData['district'], $clubData['division'], $clubData['area'], $clubData['club_number'], $clubData['club_name'], $clubData['club_status'], 
            $clubData['mem_base'], $clubData['active_members'], $clubData['goals_met'],
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

        public function update_club($clubData, $currentClubData) {
            // https://developer.wordpress.org/reference/classes/wpdb/update/
            global $wpdb;  // Make sure $wpdb is available
            $sql = "UPDATE $this->table_name SET
                    `updated_at` = %s, `score_achieved_at` = %s, 
                    `district` = %s, `division`= %s, `area`=%s, 
                    `club_name` = %s, `club_status` = %s, 
                    `mem_base` = %d, `active_members` = %d, `goals_met` = %d,
                    `level_1` = %d, `level_2` = %d, `add_level_2` = %d, 
                    `level_3` = %d, `level_4_5_DTM` = %d, `add_level_4_5_DTM` = %d,
                    `new_members` = %d, `add_new_members` = %d,
                    `officers_round_1` = %d, `officers_round_2` = %d, 
                    `mem_dues_oct` = %d, `mem_dues_apr` = %d, `off_list_on_time` = %d,
                    `ti_status` = %s
                WHERE id = %d;";
            $scoreAchievedAt = $clubData['goals_met']>$currentClubData['goals_met'] ? current_time('mysql') : $currentClubData['score_achieved_at'];
            $wpdb->query( $wpdb->prepare( $sql, current_time('mysql'), $scoreAchievedAt, $clubData['district'], $clubData['division'], $clubData['area'], $clubData['club_name'], $clubData['club_status'], 
                    $clubData['mem_base'], $clubData['active_members'], $clubData['goals_met'],
                        $clubData['level_1'], $clubData['level_2'], $clubData['add_level_2'], $clubData['level_3'], $clubData['level_4_5_DTM'], $clubData['add_level_4_5_DTM'],
                        $clubData['new_members'], $clubData['add_new_members'],
                        $clubData['officers_round_1'], $clubData['officers_round_2'], 
                        $clubData['mem_dues_oct'], $clubData['mem_dues_apr'], $clubData['off_list_on_time'],
                        $clubData['ti_status'], $currentClubData['id'])); 
            
            if ($wpdb->last_error) {
                error_log("Database update error: " . $wpdb->last_error);
            } else {
                $rows_affected = $wpdb->rows_affected; // Get the number of rows updated
                if($rows_affected > 1){
                    error_log("Error: multiple records updated");
                } else if ($rows_affected <= 0) {
                    error_log("Error: no record has been updated");
                }
            }
           
        }
    }
?>