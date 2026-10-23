<?php
function export_to_excel($filename, $headers, $data, $title = '') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename.xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    echo "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" 
              xmlns:x=\"urn:schemas-microsoft-com:office:excel\" 
              xmlns=\"http://www.w3.org/TR/REC-html40\">";
    echo "<head><meta charset='UTF-8'></head>";
    echo "<body>";
    
    if ($title) {
        echo "<h2 style='text-align:center; font-family: Arial; color: #8B0000;'>$title</h2>";
    }
    
    echo "<table border='1' style='border-collapse: collapse; font-family: Arial;'>";
    echo "<tr style='background-color: #8B0000; color: #FFD700; font-weight: bold;'>";
    foreach ($headers as $header) {
        echo "<th style='padding: 8px; border: 1px solid #000;'>$header</th>";
    }
    echo "</tr>";
    
    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td style='padding: 6px; border: 1px solid #000;'>$cell</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</body></html>";
    exit;
}
?>