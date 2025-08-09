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

function processCsvString($csvString, $delimiter = ",", $enclosure = '"', $escape = "\\", $prefixIgnore = 'Month of') {
    $rows = [];
    
    $lines = explode("\n", $csvString); // Split into lines

    // Handle BOM (Byte Order Mark) if present (common in Windows-created CSVs)
    $lines[0] = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0]);

    $header = null;
    foreach ($lines as $line) {
        $line = trim($line); // Remove leading/trailing whitespace
        if (empty($line) || str_starts_with($line, $prefixIgnore)) {
            continue; // Skip empty lines or lines starting with a specific prefix
        }

        /*replace the separator with a pipe to avoid seperating more fields 
        than necessary if heading or text contains the seperator such as a comma */
        $line = str_replace($delimiter.$enclosure,'|"',$line);
        $fields = str_getcsv($line, "|", $enclosure, $escape);
        if ($header === null) {
            if(sizeof($fields)==23){
                $header = [
                            'district' ,
                            'division' ,
                            'area' ,
                            'club_number' ,
                            'club_name' ,
                            'club_status' ,
                            'mem_base' ,
                            'active_members' ,
                            'goals_met' ,
                            'level_1',
                            'level_2',
                            'add_level_2',
                            'level_3',
                            'level_4_5_DTM',
                            'add_level_4_5_DTM',
                            'new_members',
                            'add_new_members',
                            'officers_round_1',
                            'officers_round_2',
                            'mem_dues_oct',
                            'mem_dues_apr',
                            'off_list_on_time',
                            'ti_status', 
                        ];
            } else {
                $header = [
                            'district' ,
                            'division' ,
                            'area' ,
                            'club_number' ,
                            'club_name' ,
                            'club_status' ,
                            'csp',
                            'mem_base' ,
                            'active_members' ,
                            'net_growth',
                            'goals_met' ,
                            'level_1',
                            'level_2',
                            'add_level_2',
                            'level_3',
                            'level_4_5_DTM',
                            'add_level_4_5_DTM',
                            'new_members',
                            'add_new_members',
                            'officers_round_1',
                            'officers_round_2',
                            'mem_dues_oct',
                            'mem_dues_apr',
                            'off_list_on_time',
                            'ti_status', 
                        ];
            }
        } else {
            $row = array_combine($header, $fields); // Combine header and values
            $rows[] = $row;
        }
    }
    return $rows;
}


?>
