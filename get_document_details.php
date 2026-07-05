<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once 'config/db_connection.php';

// Function to get file type and icon
function getFileTypeInfo($file_path) {
    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    $file_types = [
        'pdf' => ['type' => 'PDF Document', 'icon' => '📄', 'label' => 'PDF'],
        'doc' => ['type' => 'Word Document', 'icon' => '📝', 'label' => 'WORD'],
        'docx' => ['type' => 'Word Document', 'icon' => '📝', 'label' => 'WORD'],
        'txt' => ['type' => 'Text File', 'icon' => '📃', 'label' => 'TEXT'],
        'rtf' => ['type' => 'Rich Text Format', 'icon' => '📃', 'label' => 'RTF'],
        'csv' => ['type' => 'CSV File', 'icon' => '📊', 'label' => 'CSV'],
        'xls' => ['type' => 'Excel Spreadsheet', 'icon' => '📊', 'label' => 'EXCEL'],
        'xlsx' => ['type' => 'Excel Spreadsheet', 'icon' => '📊', 'label' => 'EXCEL'],
        'jpg' => ['type' => 'JPEG Image', 'icon' => '🖼️', 'label' => 'IMAGE'],
        'jpeg' => ['type' => 'JPEG Image', 'icon' => '🖼️', 'label' => 'IMAGE'],
        'png' => ['type' => 'PNG Image', 'icon' => '🖼️', 'label' => 'IMAGE'],
        'gif' => ['type' => 'GIF Image', 'icon' => '🖼️', 'label' => 'IMAGE'],
        'mp4' => ['type' => 'MP4 Video', 'icon' => '🎥', 'label' => 'VIDEO'],
        'avi' => ['type' => 'AVI Video', 'icon' => '🎥', 'label' => 'VIDEO'],
        'mov' => ['type' => 'QuickTime Video', 'icon' => '🎥', 'label' => 'VIDEO'],
        'mp3' => ['type' => 'MP3 Audio', 'icon' => '🎵', 'label' => 'AUDIO'],
        'wav' => ['type' => 'WAV Audio', 'icon' => '🎵', 'label' => 'AUDIO'],
        'ppt' => ['type' => 'PowerPoint', 'icon' => '📽️', 'label' => 'POWERPOINT'],
        'pptx' => ['type' => 'PowerPoint', 'icon' => '📽️', 'label' => 'POWERPOINT'],
        'zip' => ['type' => 'ZIP Archive', 'icon' => '🗜️', 'label' => 'ZIP'],
        'rar' => ['type' => 'RAR Archive', 'icon' => '🗜️', 'label' => 'RAR'],
        'html' => ['type' => 'HTML File', 'icon' => '🌐', 'label' => 'HTML'],
        'css' => ['type' => 'CSS File', 'icon' => '🎨', 'label' => 'CSS'],
        'js' => ['type' => 'JavaScript File', 'icon' => '💻', 'label' => 'JS'],
        'php' => ['type' => 'PHP File', 'icon' => '🐘', 'label' => 'PHP'],
        'py' => ['type' => 'Python File', 'icon' => '🐍', 'label' => 'PYTHON'],
    ];
    
    if (isset($file_types[$extension])) {
        return $file_types[$extension];
    }
    
    return ['type' => 'Unknown File', 'icon' => '📁', 'label' => 'FILE'];
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'No document ID provided']);
    exit;
}

$doc_id = intval($_GET['id']);

// Get document details INCLUDING content features
$sql = "SELECT 
            d.document_id,
            d.title,
            d.author,
            d.file_path,
            d.upload_date,
            d.page_count,
            d.publication_year,
            c.category_name,
            COALESCE(m.keywords, 'No keywords available') as keywords,
            COALESCE(m.summary, 'No summary available') as summary,
            COALESCE(cf.has_logo, 0) as has_logo,
            COALESCE(cf.has_diagram, 0) as has_diagram,
            COALESCE(cf.has_chart, 0) as has_chart,
            COALESCE(cf.has_image, 0) as has_image
        FROM document d 
        LEFT JOIN category c ON d.category_id = c.category_id
        LEFT JOIN metadata m ON d.document_id = m.document_id
        LEFT JOIN content_features cf ON d.document_id = cf.document_id
        WHERE d.document_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Document not found']);
    exit;
}

$doc = $result->fetch_assoc();

// Get file type info
$file_info = getFileTypeInfo($doc['file_path']);

// Get similar documents using CBR with feature details
$cbr_sql = "SELECT 
                d.document_id,
                d.title,
                d.author,
                d.page_count,
                d.file_path,
                COALESCE(cf2.has_logo, 0) as has_logo,
                COALESCE(cf2.has_diagram, 0) as has_diagram,
                COALESCE(cf2.has_chart, 0) as has_chart,
                COALESCE(cf2.has_image, 0) as has_image,
                (
                    (CASE WHEN cf1.has_logo = cf2.has_logo THEN 25 ELSE 0 END) +
                    (CASE WHEN cf1.has_diagram = cf2.has_diagram THEN 25 ELSE 0 END) +
                    (CASE WHEN cf1.has_chart = cf2.has_chart THEN 25 ELSE 0 END) +
                    (CASE WHEN cf1.has_image = cf2.has_image THEN 25 ELSE 0 END)
                ) as similarity_score
            FROM content_features cf1
            JOIN content_features cf2 ON cf1.document_id != cf2.document_id
            JOIN document d ON cf2.document_id = d.document_id
            WHERE cf1.document_id = ? 
              AND cf2.document_id != ?
            ORDER BY similarity_score DESC
            LIMIT 5";

$cbr_stmt = $conn->prepare($cbr_sql);
$cbr_stmt->bind_param("ii", $doc_id, $doc_id);
$cbr_stmt->execute();
$cbr_result = $cbr_stmt->get_result();

$similar_docs = [];
while($similar = $cbr_result->fetch_assoc()) {
    if($similar['similarity_score'] > 0) {
        $similar_file_info = getFileTypeInfo($similar['file_path']);
        $similar_docs[] = [
            'document_id' => $similar['document_id'],
            'title' => $similar['title'],
            'author' => $similar['author'],
            'page_count' => $similar['page_count'],
            'similarity' => $similar['similarity_score'],
            'has_logo' => (bool)$similar['has_logo'],
            'has_diagram' => (bool)$similar['has_diagram'],
            'has_chart' => (bool)$similar['has_chart'],
            'has_image' => (bool)$similar['has_image'],
            'file_type' => $similar_file_info['label'],
            'file_icon' => $similar_file_info['icon']
        ];
    }
}

// Response with current features and file type
$response = [
    'success' => true,
    'document_id' => $doc['document_id'],
    'title' => $doc['title'],
    'author' => $doc['author'],
    'category' => $doc['category_name'],
    'page_count' => $doc['page_count'],
    'upload_date' => date('d/m/Y', strtotime($doc['upload_date'])),
    'publication_year' => $doc['publication_year'] ?? 'N/A',
    'file_path' => $doc['file_path'],
    'summary' => $doc['summary'],
    'keywords' => $doc['keywords'],
    'file_type' => $file_info['label'],
    'file_icon' => $file_info['icon'],
    'file_type_full' => $file_info['type'],
    'current_features' => [
        'has_logo' => (bool)$doc['has_logo'],
        'has_diagram' => (bool)$doc['has_diagram'],
        'has_chart' => (bool)$doc['has_chart'],
        'has_image' => (bool)$doc['has_image']
    ],
    'similar_docs' => $similar_docs
];

echo json_encode($response);
$conn->close();
?>