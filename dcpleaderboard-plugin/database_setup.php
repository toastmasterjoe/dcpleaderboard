<?php

    function wp_dcpleaderboard_clubs_create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dcpleaderboard_clubs';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
            `score_achieved_at` datetime NOT NULL,
            `district` varchar(45) NOT NULL,
            `division` varchar(45) NOT NULL,
            `area` varchar(45) NOT NULL,
            `club_number` varchar(45) NOT NULL,
            `club_name` varchar(255) NOT NULL,
            `club_status` varchar(45) NOT NULL,
            `mem_base` int(11) NOT NULL,
            `active_members` int(11) NOT NULL,
            `goals_met` int(11) NOT NULL,
            `level_1` int(11) NOT NULL,
            `level_2` int(11) NOT NULL,
            `add_level_2` int(11) NOT NULL,
            `level_3` int(11) NOT NULL,
            `level_4_5_DTM` int(11) NOT NULL,
            `add_level_4_5_DTM` int(11) NOT NULL,
            `new_members` int(11) NOT NULL,
            `add_new_members` int(11) NOT NULL,
            `officers_round_1` int(11) NOT NULL,
            `officers_round_2` int(11) NOT NULL,
            `mem_dues_oct` int(11) NOT NULL,
            `mem_dues_apr` int(11) NOT NULL,
            `off_list_on_time` int(11) NOT NULL,
            `ti_status` varchar(45) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `club_number_UNIQUE` (`club_number`),
            UNIQUE KEY `id_UNIQUE` (`id`)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        if ($wpdb->last_error) {
            error_log("Database  error: " . $wpdb->last_error);
        }
    }

    function wp_dcpleaderboard_clubs_delete_table() {
        global $wpdb;
    
        $table_name = $wpdb->prefix . 'dcpleaderboard_clubs';
    
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
        if ($wpdb->last_error) {
            error_log("Database  error: " . $wpdb->last_error);
        }
    }      
      
?>