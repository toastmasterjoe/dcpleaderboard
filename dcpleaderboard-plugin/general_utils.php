<?php
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
            $_ = $fields; // First row is the header - skip header, do not process content, instead use field names in table
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
            $row = array_combine($header, $fields); // Combine header and values
            $rows[] = $row;
        }
    }
    return $rows;
}
?>
