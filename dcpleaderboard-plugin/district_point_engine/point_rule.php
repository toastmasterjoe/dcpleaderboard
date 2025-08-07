<?php

// This class implements all three properties as traditional, un-hooked
// properties. That's entirely valid.
class PointRule 
{
    private int $id;
    private string $uuid;
    private bool $automatic;
    private bool $multiAward;
    private int $points; // TODO should function determine point or points be based on the rule
    /**
     * In case of manual rule use $points
     * In case of automatic rule should this value be ignored or else it can be the number of points per award
     */
    private string $name;
    private string $functionName;

    public function __construct(int $id, string $uuid, bool $automatic, bool $multiAward, int $points, string $name, string $functionName)
    {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->automatic = $automatic;
        $this->name = $name;
        $this->functionName = $functionName;
    }


    public function calculate($club): int{
        if(($automatic) && ($functionName)){
            if (function_exists($functionName)) {
                try{
                    return $functionName($club, $points);
                } catch(Exception $e){
                    error_log($e->getMessage());
                }
            } else {
                error_log("Function '{$functionName}' does not exist.");
            }
        } else {
            throw new Exception($automatic?'Function name not set':'Calculate should not be invoked on manual rule');
        }
    }
}

/*
    We only need to know when district goal value was incremented like dcp goals.
    TODO: Decide whether to calculate District goals from full and half dcp goals as a single goal or seperate goal.
*/ 

    
?>