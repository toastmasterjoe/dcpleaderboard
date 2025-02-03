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

        $line = str_replace(',"','|"',$line);
        $fields = str_getcsv($line, "|", $enclosure, $escape);

        if ($header === null) {
            $header = $fields; // First row is the header
        } else {
            $row = array_combine($header, $fields); // Combine header and values
            $rows[] = $row;
        }
    }
    return $rows;
}
?>
