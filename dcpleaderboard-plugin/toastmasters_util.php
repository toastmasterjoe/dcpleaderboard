<?php
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