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

function get_report_date() {
    return date('n/d/Y', strtotime('-1 day'));
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

function build_dashboard_url_club_progress(){
    return 'https://dashboards.toastmasters.org/export.aspx?type=CSV&report=clubperformance~109~'.get_report_date().'~~'.get_programme_year();
}
?>