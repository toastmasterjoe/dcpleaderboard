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

function get_report_date() {
    return date('n/d/Y', strtotime('-1 day'));
}

function get_end_of_previous_year_date() {
    $monthNumber = (int) date('n');//no leading zero
    $currentYear = (int) date('Y');
    
    return sprintf("6/30/%04d",  $monthNumber < 7 ? $currentYear - 1 : $currentYear);
}

function get_programme_year() {
    $monthNumber = (int) date('n');//no leading zero
    $currentYear = (int) date('Y');
    if($monthNumber >= 7){
        //Month is July new program year
        return sprintf("%04d-%04d", $currentYear, $currentYear+1);
    } else {
        return sprintf("%04d-%04d", $currentYear-1, $currentYear);
    } 
}

function get_previous_programme_year() {
    $monthNumber = (int) date('n');//no leading zero
    $currentYear = (int) date('Y');
    if($monthNumber >= 7){
        //Month is July new program year
        return sprintf("%04d-%04d", $currentYear-1, $currentYear);
    } else {
        return sprintf("%04d-%04d", $currentYear-2, $currentYear-1);
    } 
}

function build_dashboard_url_club_progress(string $district){
    return 'https://dashboards.toastmasters.org/'.get_programme_year().'/export.aspx?type=CSV&report=clubperformance~'.$district.'~'.get_report_date().'~~'.get_programme_year();
}

function build_dashboard_previous_year_url(string $district){
    return 'https://dashboards.toastmasters.org/'.get_previous_programme_year().'/export.aspx?type=CSV&report=clubperformance~'.$district.'~'.get_end_of_previous_year_date().'~~'.get_previous_programme_year();
}
?>