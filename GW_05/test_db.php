<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/db_connection.php';

echo "<h2>Testing Database Connection - md_gw05</h2>";

// Test 1: Connection
echo "<h3>1. Connection:</h3>";
if ($conn) {
    echo "✅ Connected successfully<br>";
} else {
    echo "❌ Connection failed<br>";
}

// Test 2: Check tables
echo "<h3>2. Tables:</h3>";
$tables = $conn->query("SHOW TABLES");
if ($tables && $tables->num_rows > 0) {
    while($table = $tables->fetch_array()) {
        echo "📊 " . $table[0] . "<br>";
    }
}

// Test 3: Count documents
echo "<h3>3. Document Count:</h3>";
$count = $conn->query("SELECT COUNT(*) as total FROM document");
if ($count) {
    $total = $count->fetch_assoc()['total'];
    echo "📄 Total documents: " . $total . "<br>";
}

// Test 4: Sample documents
echo "<h3>4. Sample Documents:</h3>";
$docs = $conn->query("SELECT document_id, title, author FROM document LIMIT 5");
if ($docs && $docs->num_rows > 0) {
    echo "<ul>";
    while($doc = $docs->fetch_assoc()) {
        echo "<li>ID: {$doc['document_id']} - {$doc['title']} - {$doc['author']}</li>";
    }
    echo "</ul>";
}

// Test 5: Test AJAX endpoint
echo "<h3>5. Test AJAX:</h3>";
echo "<button onclick='testAjax()'>Test get_document_details.php?id=1</button>";
echo "<div id='result' style='margin-top:10px; background:#f0f0f0; padding:10px; border-radius:5px;'></div>";
?>

<script>
function testAjax() {
    fetch('get_document_details.php?id=1')
        .then(response => response.json())
        .then(data => {
            document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            document.getElementById('result').innerHTML = 'Error: ' + error.message;
        });
}
</script>