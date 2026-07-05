<?php
// cbr_analyzer.php - Auto detect visual features from PDF
require_once 'config/db_connection.php';

function analyzePDFFeatures($pdf_path) {
    $features = [
        'has_logo' => false,
        'has_diagram' => false,
        'has_chart' => false,
        'has_image' => false
    ];
    
    // Check if file exists
    if (!file_exists($pdf_path)) {
        return $features;
    }
    
    // Read file content
    $content = file_get_contents($pdf_path);
    
    // Detect Logo (look for logo keywords in first few KB)
    $logo_keywords = ['logo', 'university', 'company', 'copyright', '©', '™', 'registered'];
    foreach($logo_keywords as $keyword) {
        if(stripos(substr($content, 0, 5000), $keyword) !== false) {
            $features['has_logo'] = true;
            break;
        }
    }
    
    // Detect Diagram
    $diagram_keywords = ['diagram', 'figure', 'erd', 'flowchart', 'schematic', 'architecture', 'layout'];
    foreach($diagram_keywords as $keyword) {
        if(stripos($content, $keyword) !== false) {
            $features['has_diagram'] = true;
            break;
        }
    }
    
    // Detect Chart
    $chart_keywords = ['chart', 'graph', 'plot', 'bar chart', 'pie chart', 'line graph', 'histogram'];
    foreach($chart_keywords as $keyword) {
        if(stripos($content, $keyword) !== false) {
            $features['has_chart'] = true;
            break;
        }
    }
    
    // Detect Image (check for image markers in PDF)
    $image_markers = ['/Image', '/XObject', '/Subtype /Image'];
    foreach($image_markers as $marker) {
        if(strpos($content, $marker) !== false) {
            $features['has_image'] = true;
            break;
        }
    }
    
    return $features;
}

// Function to update all documents features
function updateAllDocumentsFeatures($conn) {
    $sql = "SELECT document_id, file_path FROM document";
    $result = $conn->query($sql);
    
    $updated = 0;
    while($doc = $result->fetch_assoc()) {
        $features = analyzePDFFeatures($doc['file_path']);
        
        $update_sql = "UPDATE content_features SET 
                       has_logo = {$features['has_logo']},
                       has_diagram = {$features['has_diagram']},
                       has_chart = {$features['has_chart']},
                       has_image = {$features['has_image']}
                       WHERE document_id = {$doc['document_id']}";
        
        if($conn->query($update_sql)) {
            $updated++;
        }
    }
    
    return $updated;
}

// Run if accessed directly
if (basename($_SERVER['PHP_SELF']) == 'cbr_analyzer.php') {
    echo "<h2>CBR Auto Analyzer</h2>";
    $count = updateAllDocumentsFeatures($conn);
    echo "✅ Updated $count documents with auto-detected features!";
    echo "<br><a href='index.php'>Back to System</a>";
}
?>