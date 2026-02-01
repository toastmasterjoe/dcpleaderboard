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

require_once plugin_dir_path( __FILE__ ) . 'district-point-engine/point-rule.php'; 

function wp_dcpleaderboard_rules_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'points_rule';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = " CREATE TABLE IF NOT EXISTS $table_name ( 
            `id` INT auto_increment NOT NULL,
            `automatic` TINYINT DEFAULT 1 NOT NULL,
            `multi_award` TINYINT DEFAULT 0 NOT NULL,
            `points` INT DEFAULT 1 NOT NULL,
            `name` varchar(100) NOT NULL,
            `description` varchar(255) NOT NULL,
            `function_name` varchar(255) NULL,
            CONSTRAINT point_rule_pk PRIMARY KEY (id)
        )
        $charset_collate;
        CREATE FULLTEXT INDEX point_rule_name_IDX ON $table_name (`name`);
    ";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    if ($wpdb->last_error) {
        error_log("Database  error: " . $wpdb->last_error);
    }
}

function wp_dcpleaderboard_clubs_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dcpleaderboard_clubs';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` INT NOT NULL AUTO_INCREMENT,
        `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
        `score_achieved_at` datetime NOT NULL,
        `district` varchar(45) NOT NULL,
        `division` varchar(45) NOT NULL,
        `area` varchar(45) NOT NULL,
        `club_number` varchar(45) NOT NULL,
        `club_name` varchar(255) NOT NULL,
        `club_status` varchar(45) NOT NULL,
        `csp` varchar(10) NOT NULL,
        `mem_base` INT  NOT NULL,
        `active_members` INT  NOT NULL,
        `net_growth` INT  NOT NULL,
        `goals_met` INT NOT NULL,
        `level_1` INT  NOT NULL,
        `level_2` INT  NOT NULL,
        `add_level_2` INT  NOT NULL,
        `level_3` INT  NOT NULL,
        `level_4_5_DTM` INT  NOT NULL,
        `add_level_4_5_DTM` INT  NOT NULL,
        `new_members` INT  NOT NULL,
        `add_new_members` INT  NOT NULL,
        `officers_round_1` INT  NOT NULL,
        `officers_round_2` INT  NOT NULL,
        `mem_dues_oct` INT  NOT NULL,
        `mem_dues_apr` INT  NOT NULL,
        `off_list_on_time` INT  NOT NULL,
        `ti_status` varchar(45) NOT NULL,
        `ti_status_last_year` varchar(45) NOT NULL DEFAULT '',
        `district_goals_met` INT DEFAULT 0 NOT NULL,
        `district_score_achieved_at` datetime DEFAULT NULL,
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

function wp_dcpleaderboard_rules_trigger_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'points_rule_trigger';
    $points_rule_ref = $wpdb->prefix . 'points_rule(id)';
    $dcpleaderboard_clubs_ref = $wpdb->prefix . 'dcpleaderboard_clubs(id)';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
    	id INT auto_increment NOT NULL,
    	point_rule_id INT NOT NULL,
    	club_id INT NOT NULL,
    	club_number varchar(45) NOT NULL,
    	created_date_time DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    	CONSTRAINT points_rule_trigger_pk PRIMARY KEY (id),
    	CONSTRAINT points_rule_trigger_wp_points_rule_FK FOREIGN KEY (point_rule_id) REFERENCES $points_rule_ref ,
    	CONSTRAINT points_rule_trigger_wp_dcpleaderboard_clubs_FK FOREIGN KEY (club_id) REFERENCES $dcpleaderboard_clubs_ref
    )
    $charset_collate;
    CREATE INDEX points_rule_trigger_club_number_IDX USING BTREE ON $table_name(club_number);
    CREATE INDEX points_rule_trigger_club_id_IDX USING BTREE ON $table_name(club_id);
    ";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    if ($wpdb->last_error) {
        error_log("Database  error: " . $wpdb->last_error);
    }
}

function wp_dcpleaderboard_rules_populate_table() {
    $pointRuleDriver = new PointRule();
    $existingRules = $pointRuleDriver->getAllRules();

    // Define rules to be added
    $rules = [
        new PointRule(true,false,1,"Achieve 4 level 1's","Educational Goals - Achieve 4 level 1's","award_level1_points"),
        new PointRule(true,false,1,"Achieve 2 level 2's","Educational Goals - Achieve 2 level 2's","award_level2_points"),
        new PointRule(true,false,1,"Achieve 2 additional level 2's","Educational Goals - Achieve 2 additional level 2's","award_level2_2_points"),
        new PointRule(true,false,1,"Achieve 2 level 3's","Educational Goals - Achieve 2 additional level 3's","award_level3_points"),
        new PointRule(true,false,1,"Achieve 1 level 4, 5 or DTM","Educational Goals - Achieve 1 level 4, 5 or DTM","award_level4_5_DTM_points"),
        new PointRule(true,false,1,"Achieve 1 additional level 4, 5 or DTM","Educational Goals - Achieve 1 additional level 4, 5 or DTM","award_level4_5_DTM_2_points"),
        new PointRule(true,false,1,"4 new members","One point for the first 4 new members that a club gains","award_new_members_points"),
        new PointRule(true,false,1,"4 additional new members","The second 4 new members that a club gains, award an additional point","award_new_members_2_points"),
        new PointRule(true,false,1,"Summer COT Point","4 or more officers at Summer COT","award_officers_summer_cot_points"),
        new PointRule(true,false,1,"Winter COT Point","4 or more officers at Winter COT","award_officers_winter_cot_points"),
        new PointRule(true,false,1,"One Membership Payment On Time","At least one of October or April membership payments have to be done on time","award_membership_on_time_points"),
        new PointRule(true,false,1,"Officer List On Time","The club submitted the officer list before the 30th June","award_officer_list_on_time_points"),
        new PointRule(true,true,1,"Every 5 New Members","1 extra point for every 5 new members that a club gains","award_5memberpoint_points"),
        new PointRule(false,true,2,"Sponsor for new club","Any club that is a sponsor for a new club"),
        new PointRule(false,false,1,"Top club in district conference","The club that will have most participants in the district conference")
    ];

    // Save missing rules
    foreach ($rules as $rule) {
        if (!in_array($rule->name, array_column($existingRules, 'name'))) {
            $rule->saveRule();
        }
    }
}

function wp_dcpleaderboard_install_db() {
    wp_dcpleaderboard_clubs_create_table();
    wp_dcpleaderboard_rules_create_table();
    wp_dcpleaderboard_rules_populate_table();
    wp_dcpleaderboard_rules_trigger_create_table();
}

function wp_dcpleaderboard_rules_delete_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'points_rule';

    $wpdb->query( "DROP INDEX point_rule_name_IDX ON $table_name;");
                    
    if ($wpdb->last_error) {
        error_log("Database  error: " . $wpdb->last_error);
    }

    $wpdb->query( "DROP TABLE IF EXISTS $table_name;" );
    if ($wpdb->last_error) {
        error_log("Database  error: " . $wpdb->last_error);
    }
}

function wp_dcpleaderboard_clubs_delete_table() {
    wp_dcpleaderboard_delete_table('dcpleaderboard_clubs');
}     

function wp_dcpleaderboard_rules_trigger_delete_table() {
    wp_dcpleaderboard_delete_table('points_rule_trigger');
}     

function wp_dcpleaderboard_delete_table($table) {
    global $wpdb;

    $prefixed_table_name = $wpdb->prefix . $table;

    $wpdb->query( "DROP TABLE IF EXISTS $prefixed_table_name" );
    if ($wpdb->last_error) {
        error_log("Database  error: " . $wpdb->last_error);
    }
}

function wp_dcpleaderboard_rules_truncate_table(){
    wp_dcpleaderboard_truncate_table('points_rule');
}

function wp_dcpleaderboard_rules_trigger_truncate_table(){
    wp_dcpleaderboard_truncate_table('points_rule_trigger');
}

function wp_dcpleaderboard_clubs_truncate_table(){
    wp_dcpleaderboard_truncate_table('dcpleaderboard_clubs');
}

function wp_dcpleaderboard_truncate_table($table_name){
    global $wpdb;

    $prefixed_table_name = $wpdb->prefix . $table_name;

    $wpdb->query( "TRUNCATE TABLE $prefixed_table_name;");
                    
    if ($wpdb->last_error) {
        error_log("Database  error: " . $wpdb->last_error);
    }
}

function wp_dcpleaderboard_uninstall_db() {
    wp_dcpleaderboard_rules_trigger_delete_table();
    wp_dcpleaderboard_rules_truncate_table();
    wp_dcpleaderboard_rules_delete_table();
    wp_dcpleaderboard_clubs_delete_table();
}
  
?>