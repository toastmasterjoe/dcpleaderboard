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

require_once plugin_dir_path( __FILE__ ) . 'point-calculators.php'; 

class PointRule 
{
    private int $id;
    private bool $automatic;
    private bool $multiAward;
    private int $points; // TODO should function determine point or points be based on the rule
    /**
     * In case of manual rule use $points
     * In case of automatic rule should this value be ignored or else it can be the number of points per award
     */
    private string $name;
    private string $description;
    private string $functionName;

    private static string $tableName;


    public static function load(){
        if(empty(self::$tableName)){
            global $wpdb;
            self::$tableName = $wpdb->prefix . 'points_rule'; 
        }
    }
    public function __construct(bool $automatic = false, bool $multiAward = false, int $points = 1, string $name ="rule", string $description="", string $functionName="",int $id = 0)
    {
        self::load();
        if($automatic && empty($functionName)){
            throw new Exception("misconfigured rule, function name is required for automatic rules");
        }
        $this->id = $id;
        $this->automatic = $automatic;
        $this->multiAward = $multiAward;
        $this->points = $points;
        $this->name = $name;
        $this->description = $description;
        $this->functionName = $functionName;
    }

    public function getId(): int{
        return $this->id;
    }

    public function isAutomatic(): bool{
        return $this->automatic;
    }

    public function isMultiAward(): int{
        return $this->multiAward;
    }

    public function getPoints(): int{
        return $this->points;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getRuleById(int $id): PointRule {
        global $wpdb;
                 
        // Prepared statement (Highly recommended for security):
        $tableName=self::$tableName;
        $sql = $wpdb->prepare( "SELECT * FROM {$tableName} WHERE id = %d", $id );
        $row = $wpdb->get_row( $sql, ARRAY_A ); 
        $result = new PointRule($row['automatic'],$row['multi_award'],$row['points'],$row['name'], $row['description'],$row['function_name'], $row['id']);
        return $result;
    }

    public static function getAllRules(): array {
        self::load();
        global $wpdb;
        $tableName=self::$tableName;
        // Prepared statement (Highly recommended for security):
        $sql = $wpdb->prepare( "SELECT * FROM {$tableName} ");
        $rows = $wpdb->get_results( $sql, ARRAY_A ); 
        $results = array();
        foreach ($rows as $row){
            $result = new PointRule($row['automatic'],$row['multi_award'],$row['points'],$row['name'], $row['description'],$row['function_name'], $row['id']);
            array_push($results, $result);
        }
        return $results;
    }

    public function saveRule() {
        if (empty($id)) {
            return $this->createRule();
        } else {
            return $this->updateRule();
        }
    }

    private function createRule() {
        global $wpdb;
        $tableName=self::$tableName;
        $insert_sql = "INSERT INTO {$tableName}
                        ( `automatic`, `multi_award`, `points`, `name`, `description`, `function_name`)
                        VALUES(%d, %d, %d, %s, %s, %s);
        ";
        trim($insert_sql);
        $wpdb->query( $wpdb->prepare( $insert_sql, (int)$this->automatic,
                                    (int)$this->multiAward,
                                    $this->points,
                                    $this->name,
                                    $this->description, 
                                    $this->functionName));
        if ($wpdb->last_error) {
            error_log("Database insert error: " . $wpdb->last_error);
        } else {
            $inserted_id = $wpdb->insert_id;
            return $inserted_id;
        }
        return 0;
    }

    public function updateRule() {
        global $wpdb;
        $tableName=self::$tableName;
        $update_sql = "UPDATE {$tableName}
                        SET `automatic`=%d, `multi_award`=%d, `points`=%d, `name`=%s, `description`=%s, `function_name`=%s
                        WHERE `id`=%d;";
        $wpdb->query( $wpdb->prepare( $update_sql, (int)$this->automatic,
                                    (int)$this->multiAward,
                                    $this->points,
                                    $this->name,
                                    $this->description, 
                                    $this->functionName,
                                    $this->id));
        if ($wpdb->last_error) {
            error_log("Database update error: " . $wpdb->last_error);
            return 0;
        } else {
            $rows_affected = $wpdb->rows_affected; // Get the number of rows updated
            if($rows_affected > 1){
                error_log("Error: multiple records updated");
            } else if ($rows_affected <= 0) {
                error_log("Error: no record has been updated");
            }
            return $rows_affected;
        }
    }

    public function isTriggered(array $club, int $triggers = 0): int{
        if(($this->automatic) && ($this->functionName)){
            if (function_exists($this->functionName)) {
                try{
                    $functionName = $this->functionName;
                    if($this->multiAward) {
                         return $functionName($club, $triggers);
                    } else {
                        return $functionName($club) ? 1 : 0 ;
                    }
                    
                } catch(Exception $e){
                    error_log($e->getMessage());
                }
            } else {
                error_log("Function '{$this->functionName}' does not exist.");
            }
        } else {
            throw new Exception($this->name.$this->automatic?'Function name not set':'Calculate should not be invoked on manual rule');
        }
        return 0;
    }

    public function calculatePoints(array $club, int $triggers = 0): int{
        if(($this->automatic) && ($this->functionName)){
            if (function_exists($this->functionName)) {
                try{
                    $functionName = $this->functionName;
                    if($this->multiAward) {
                         return $functionName($club, $triggers) * $this->points;
                    } else {
                        return $functionName($club) ? $this->points : 0 ;
                    }
                    
                } catch(Exception $e){
                    error_log($e->getMessage());
                }
            } else {
                error_log("Function '{$this->functionName}' does not exist.");
            }
        } else {
            throw new Exception($this->name.$this->automatic?'Function name not set':'Calculate should not be invoked on manual rule');
        }
        return 0;
    }
}

/*
    We only need to know when district goal value was incremented like dcp goals.
    TODO: Decide whether to calculate District goals from full and half dcp goals as a single goal or seperate goal.
    How to account for increased points to a rule: 
    One Option: In the database store the point rule triggered and the date, but recalculate points every time, to enable for increased assigned points to a rule. Then store total per club
        Its important to store point rule triggered or manually awarded so admin can check these especially when awarding points.
    */ 
/** From admin screen you can only create manual rules */
    
?>