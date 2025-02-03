<?php
    class Clubs {
        // https://g.co/gemini/share/415f9ef8785e plugin in class
        private $table_name;

        public function __construct(){
            global $wpdb;
            $table_name = $wpdb->prefix . 'dcpleaderboard_clubs'; // Replace with your table name
        }

        public function get_club_by_number($clubNumber){
            global $wpdb;
                     
            // Prepared statement (Highly recommended for security):
            $sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE club_number = %s", $clubNumber );
            $row = $wpdb->get_row( $sql, ARRAY_A ); 

            return $row;
        }
    }
?>