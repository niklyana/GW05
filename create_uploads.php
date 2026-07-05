<?php
// Create uploads directory
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
    echo "✅ Uploads folder created<br>";
}

// Create sample text files (as PDF placeholders)
$documents = [
    'database_design_report.pdf',
    'sql_normalization.pdf',
    'ai_research.pdf',
    'hci_usability.pdf',
    'se_report.pdf',
    'relational_model.pdf',
    'db_normalization.pdf',
    'erd_examples.pdf',
    'relational_concepts.pdf',
    'ml_basics.pdf',
    'deep_learning.pdf',
    'cloud_arch.pdf',
    'cybersecurity.pdf',
    'mobile_dev.pdf',
    'data_mining.pdf'
];

foreach($documents as $doc) {
    $filepath = 'uploads/' . $doc;
    if (!file_exists($filepath)) {
        $content = "This is a sample document: " . str_replace('.pdf', '', $doc) . "\n";
        $content .= "Created for MD_GW05 Document Repository System\n";
        $content .= "This file serves as a placeholder for the actual PDF document.\n";
        file_put_contents($filepath, $content);
        echo "📄 Created: $doc<br>";
    }
}

echo "<br>✅ All sample files created successfully!";
echo "<br><a href='index.php'>Go to Index Page</a>";
?>