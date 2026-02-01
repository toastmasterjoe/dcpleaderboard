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

//These should be immutable
class PointRuleTriggerRecord {

    private int $id;
    private int $pointRuleId;
    private int $clubDbId;
    private string $clubNumber;
    private DateTimeInterface $createdDate;

    private static string $tableName;
    private static string $clubsTableName;
    private static string $district;

    public static function load(){
       
        if(empty(self::$tableName)){
            global $wpdb;
            self::$tableName = $wpdb->prefix . 'points_rule_trigger'; 
            self::$clubsTableName = $wpdb->prefix . 'dcpleaderboard_clubs'; 
        }

        if(empty(self::$district)){
            self::$district = get_option('dcpleaderboard_district');
        }
    }

    public function __construct(int $pointRuleId, int $clubDbId,string $clubNumber, DateTimeInterface $createdDate=null, $id=0) {
        self::load();
        $this->pointRuleId = $pointRuleId;
        $this->clubDbId = $clubDbId;
        $this->clubNumber = $clubNumber;
        $this->createdDate = $createdDate??= new DateTimeImmutable();
        $this->id = $id;
    }

    public function getId():int {
        return $this->id;
    }

    public function getPointRuleId():int{
        return $this->pointRuleId;
    }

    public function getClubDbId(): int{
        return $this->clubDbId;
    }

    public function getClubNumber(): string{
        return $this->clubNumber;
    }

    public function getCreatedDateTime(): DateTimeInterface{
        return $this->createdDate;
    }

    public function getTriggerCount(): int{
        global $wpdb;
        self::load();
        $tableName = self::$tableName; 
        $sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$tableName}
                                WHERE point_rule_id = %d AND club_id = %d", $this->pointRuleId, $this->clubDbId);
        $count = $wpdb->get_var( $sql ); 
        return intval($count);
    }

    public static function getAllTiggers(): array {
        global $wpdb;
        self::load();
        $tableName = self::$tableName; 
        $clubsTableName = self::$clubsTableName;
        $sql = $wpdb->prepare( "SELECT * FROM {$tableName}
                                WHERE club_id in (select id from {$clubsTableName} where district = %s) ORDER BY club_id",self::$district);
        $rows = $wpdb->get_results( $sql, ARRAY_A ); 
        $results = array();
        foreach ($rows as $row){
            $createdDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $row['created_date_time']);
            $result = new PointRuleTriggerRecord($row['point_rule_id'],$row['club_id'],$row['club_number'],$createdDateTime, $row['id']);
            array_push($results, $result);
        }
        return $results;
    }

    public static function getTriggersByClubNumber(int $club_id): array {
        global $wpdb;
        self::load();
        $trigger_table = self::$tableName;
        $trigger_counts = $wpdb->get_results($wpdb->prepare("SELECT point_rule_id, COUNT(*) as count FROM $trigger_table WHERE club_id = %d GROUP BY point_rule_id", $club_id), ARRAY_A);
        return array_column($trigger_counts, 'count', 'point_rule_id');
    }

    public function createTrigger() {
        global $wpdb;
        $tableName = self::$tableName;
        $insert_sql = "INSERT INTO $tableName
                        (point_rule_id, club_id, club_number)
                        VALUES(%d, %d, %s);";

        trim($insert_sql);
        $wpdb->query( $wpdb->prepare( $insert_sql, $this->pointRuleId,
                                    $this->clubDbId,
                                    $this->clubNumber));
        if ($wpdb->last_error) {
            error_log("Database insert error: " . $wpdb->last_error);
        } else {
            $inserted_id = $wpdb->insert_id;
            return $inserted_id;
        }
        return 0;
    }

    public function deleteTrigger() {
        global $wpdb;
        $tableName = self::$tableName;
        $result = $wpdb->query($wpdb->prepare("DELETE FROM $tableName WHERE point_rule_id = %d AND club_id = %d LIMIT 1", $this->pointRuleId, $this->clubDbId));
        if ($wpdb->last_error) {
            error_log("Database delete error: " . $wpdb->last_error);
            return false;
        } else {
            return $result > 0;
        }
    }
}

class PointRuleTriggers {

    private array $triggerMap = [];

    public function __construct(){
        $triggers = PointRuleTriggerRecord::getAllTiggers();
        
        foreach ($triggers as $trigger) {
            if(array_key_exists($trigger->getClubNumber(), $this->triggerMap)) {
                $clubTriggers = $this->triggerMap[$trigger->getClubNumber()];
                if(array_key_exists($trigger->getPointRuleId(),  $clubTriggers)) {
                    array_push($clubTriggers[$trigger->getPointRuleId()], $trigger->getCreatedDateTime());
                } else {
                    $clubTriggers[$trigger->getPointRuleId()] = [$trigger->getCreatedDateTime()] ;
                }
                $this->triggerMap[$trigger->getClubNumber()] = $clubTriggers;
            } else {
                $this->triggerMap[$trigger->getClubNumber()] = [$trigger->getPointRuleId()=>[$trigger->getCreatedDateTime()]] ;
            }
        }
    }

    public function hasTriggersOnRule($clubNumber, $ruleId) : bool{
        return array_key_exists($clubNumber,$this->triggerMap) && 
                array_key_exists($ruleId,$this->triggerMap[$clubNumber]) && 
                !empty($this->triggerMap[$clubNumber][$ruleId]);
    }

    public function getTriggerCountOnRule($clubNumber, $ruleId) : int{
        return array_key_exists($clubNumber,$this->triggerMap) && 
                array_key_exists($ruleId,$this->triggerMap[$clubNumber]) ?
                   size_of($this->triggerMap[$clubNumber][$ruleId]) :
                    0;
    }

    public function getAllTriggerCounts($clubNumber) : array {
        if(!array_key_exists($clubNumber,$this->triggerMap)) {
            return [];
        }
        $clubRuleTriggers = $this->triggerMap[$clubNumber];
        $triggerCounts = [];
        foreach($clubRuleTriggers as $ruleId => $triggeDates) {
            $triggerCounts[$ruleId] = sizeOf($triggeDates);
        }
        return $triggerCounts;
    }
}

?>