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

require_once plugin_dir_path( __FILE__ ) . 'point-rule.php'; 
require_once plugin_dir_path( __FILE__ ) . 'point-rule-triggers.php'; 

class PointsEngine {

    public static function reCalculatePoints(array $clubs){
        
        new PointRule();//initialize if necessary
        $rules = PointRule::getAllRules();
        $rulesMap = [];
        foreach ($rules as $rule) {
            $rulesMap += [$rule->getId() => $rule];
        }

        //Load Historic Triggers
        $triggers = new PointRuleTriggers();
        PointsEngine::invokeNewTriggers($triggers, $clubs, $rulesMap);

        //Load All Triggers including those just triggered
        $triggers = new PointRuleTriggers();
        $clubPoints = PointsEngine::reCalculatePointsFromTriggers($triggers, $clubs, $rulesMap);
        return $clubPoints;
    }

    private static function invokeNewTriggers($triggers, $clubs, $rulesMap){
        foreach ($clubs as $club) {
            foreach ($rulesMap as $id => $rule){
                if($rule->isAutomatic()) {
                    if($rule->isMultiAward()) {
                        $triggerCount = $triggers->getTriggerCountOnRule($club['club_number'], $id);
                        $clubRecord = new Clubs();
                        $newTriggerCount = $rule->isTriggered($club, $triggerCount);
                        $record = $clubRecord->get_club_by_number($club['club_number']);
                        for($count = 0; $count < $newTriggerCount; $count++){
                            $triggerRecord = new PointRuleTriggerRecord($id, $record['id'], $club['club_number']);
                            $triggerRecord->createTrigger();
                        }
                    } else {
                        $wasTriggered = $triggers->hasTriggersOnRule($club['club_number'], $id);
                        if(!$wasTriggered) {
                            $count = $rule->isTriggered($club);
                            if($count > 0){
                                //register trigger
                                $clubRecord = new Clubs();
                                $record = $clubRecord->get_club_by_number($club['club_number']);
                                $triggerRecord = new PointRuleTriggerRecord($id, $record['id'], $club['club_number']);
                                $triggerRecord->createTrigger();
                            }
                        }    
                    }
                }
                
            }
        }
    }    

    private static function reCalculatePointsFromTriggers($triggers, $clubs, $rulesMap){
        $clubDistrictPoints = [];
        foreach ($clubs as $club) {
            $points = 0;
            $triggerCounts = $triggers->getAllTriggerCounts($club['club_number']);
            foreach($triggerCounts as $ruleId => $count) {
                $rule = $rulesMap[$ruleId];
                if ($rule) {
                    if ($rule->isMultiAward()) {
                        $points += $rule->getPoints() * $count;
                    } else {
                        $points += $rule->getPoints();
                    }
                }
            }
            $clubDistrictPoints += [$club['club_number'] => $points];
        }
        return $clubDistrictPoints;
    }
}