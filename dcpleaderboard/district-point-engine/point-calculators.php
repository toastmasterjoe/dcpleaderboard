<?php
/** For single award functions can return true or false work well
 * How to tackle multi award rules?, provide number of triggers and function returns true if calculated number of triggers is higer than input
 */

/**
 * Prototype for single award rule
 * calculated if the rule is triggered and return true or false
 * Engine will control if the point was awarded and if a non multiaward point is already awarded we skip the rule.
 * The engine adds to the club rule triggers in case of a new trigger.
 */
function award_level1_points(array $club):bool{
    return $club['level_1'] >= 4;
}

function award_level2_points(array $club):bool{
    return $club['level_2'] >= 2;
}
function award_level2_2_points(array $club):bool{
    return $club['add_level_2'] >= 2;
}

function award_level3_points(array $club):bool{
    return $club['level_3'] >= 2;
}

function award_level4_5_DTM_points(array $club):bool{
    return $club['level_4_5_DTM'] >= 1;
}
function award_level4_5_DTM_2_points(array $club):bool{
    return $club['add_level_4_5_DTM'] >= 1;
}

function award_new_members_points(array $club):bool{
    return $club['new_members'] >= 4;
}

function award_new_members_2_points(array $club):bool{
    return $club['add_new_members'] >= 4;
}

function award_officers_summer_cot_points(array $club):bool{
    return $club['officers_round_1'] >= 4;
}

function award_officers_winter_cot_points(array $club):bool{
    return $club['officers_round_2'] >= 4;
}

function award_membership_on_time_points(array $club):bool{
    return $club['mem_dues_oct'] >= 1 || $club['mem_dues_apr'] >= 1;
}

function award_officer_list_on_time_points(array $club):bool{
    return $club['off_list_on_time'] >= 1;
}

/**
 * Prototype for multi award rule
 * calculate trigger count, if trigger count calculated is greater than triggers return true
 * Engine will control if the point was awarded and retrieve number of awards.
 * If award is retriggered the engine adds the new trigger to the club rule triggers
 * After recaluclation, engine retrieves triggers and re caluclates the points based on triggers.
 */
function award_5memberpoint_points(array $club, int $triggers = 0) : int{
    $total_members = $club['new_members']+ $club['add_new_members'];
    $actual_triggers = intdiv($total_members,5);
    return $actual_triggers - $triggers;
}

?>