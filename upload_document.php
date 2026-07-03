<?php
// upload_document.php - Upload MULTIPLE document types with auto detection + Python PDF extraction
require_once 'config/db_connection.php';

// Increase max file size
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');

$message = '';
$error = '';

// Make sure uploads folder exists
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// ==========================================
// EXTRACT PDF USING PYTHON
// ==========================================
function extractWithPython($file_path, $title) {
    $python_script = __DIR__ . '/extract_pdf.py';
    
    if (!file_exists($python_script)) {
        return false;
    }
    
    $file_path_escaped = escapeshellarg($file_path);
    $title_escaped = escapeshellarg($title);
    
    // Gunakan 'python' untuk Windows
    $command = "python " . escapeshellarg($python_script) . " $file_path_escaped $title_escaped 2>&1";
    $output = shell_exec($command);
    
    if ($output) {
        $result = json_decode($output, true);
        if ($result && !isset($result['error'])) {
            return $result;
        }
    }
    
    return false;
}

// Allowed file types
$allowed_types = [
    'pdf' => ['mime' => 'application/pdf', 'icon' => '📄', 'type' => 'PDF Document'],
    'doc' => ['mime' => 'application/msword', 'icon' => '📝', 'type' => 'Word Document'],
    'docx' => ['mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'icon' => '📝', 'type' => 'Word Document'],
    'txt' => ['mime' => 'text/plain', 'icon' => '📃', 'type' => 'Text File'],
    'rtf' => ['mime' => 'text/rtf', 'icon' => '📃', 'type' => 'Rich Text Format'],
    'csv' => ['mime' => 'text/csv', 'icon' => '📊', 'type' => 'CSV File'],
    'xls' => ['mime' => 'application/vnd.ms-excel', 'icon' => '📊', 'type' => 'Excel Spreadsheet'],
    'xlsx' => ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'icon' => '📊', 'type' => 'Excel Spreadsheet'],
    'jpg' => ['mime' => 'image/jpeg', 'icon' => '🖼️', 'type' => 'JPEG Image'],
    'jpeg' => ['mime' => 'image/jpeg', 'icon' => '🖼️', 'type' => 'JPEG Image'],
    'png' => ['mime' => 'image/png', 'icon' => '🖼️', 'type' => 'PNG Image'],
    'gif' => ['mime' => 'image/gif', 'icon' => '🖼️', 'type' => 'GIF Image'],
    'bmp' => ['mime' => 'image/bmp', 'icon' => '🖼️', 'type' => 'Bitmap Image'],
    'webp' => ['mime' => 'image/webp', 'icon' => '🖼️', 'type' => 'WebP Image'],
    'mp4' => ['mime' => 'video/mp4', 'icon' => '🎥', 'type' => 'MP4 Video'],
    'avi' => ['mime' => 'video/x-msvideo', 'icon' => '🎥', 'type' => 'AVI Video'],
    'mov' => ['mime' => 'video/quicktime', 'icon' => '🎥', 'type' => 'QuickTime Video'],
    'wmv' => ['mime' => 'video/x-ms-wmv', 'icon' => '🎥', 'type' => 'WMV Video'],
    'mkv' => ['mime' => 'video/x-matroska', 'icon' => '🎥', 'type' => 'MKV Video'],
    'mp3' => ['mime' => 'audio/mpeg', 'icon' => '🎵', 'type' => 'MP3 Audio'],
    'wav' => ['mime' => 'audio/wav', 'icon' => '🎵', 'type' => 'WAV Audio'],
    'ogg' => ['mime' => 'audio/ogg', 'icon' => '🎵', 'type' => 'OGG Audio'],
    'm4a' => ['mime' => 'audio/mp4', 'icon' => '🎵', 'type' => 'M4A Audio'],
    'flac' => ['mime' => 'audio/flac', 'icon' => '🎵', 'type' => 'FLAC Audio'],
    'ppt' => ['mime' => 'application/vnd.ms-powerpoint', 'icon' => '📽️', 'type' => 'PowerPoint'],
    'pptx' => ['mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'icon' => '📽️', 'type' => 'PowerPoint'],
    'zip' => ['mime' => 'application/zip', 'icon' => '🗜️', 'type' => 'ZIP Archive'],
    'rar' => ['mime' => 'application/x-rar-compressed', 'icon' => '🗜️', 'type' => 'RAR Archive'],
    '7z' => ['mime' => 'application/x-7z-compressed', 'icon' => '🗜️', 'type' => '7Z Archive'],
    'html' => ['mime' => 'text/html', 'icon' => '🌐', 'type' => 'HTML File'],
    'css' => ['mime' => 'text/css', 'icon' => '🎨', 'type' => 'CSS File'],
    'js' => ['mime' => 'application/javascript', 'icon' => '💻', 'type' => 'JavaScript File'],
    'php' => ['mime' => 'text/php', 'icon' => '🐘', 'type' => 'PHP File'],
    'py' => ['mime' => 'text/x-python', 'icon' => '🐍', 'type' => 'Python File'],
    'java' => ['mime' => 'text/x-java', 'icon' => '☕', 'type' => 'Java File'],
    'json' => ['mime' => 'application/json', 'icon' => '📋', 'type' => 'JSON File'],
    'xml' => ['mime' => 'application/xml', 'icon' => '📋', 'type' => 'XML File'],
];

// ==========================================
// GENERATE SUMMARY FROM TITLE & FEATURES
// ==========================================
function generateSummary($title, $features, $file_extension, $extracted_text = '') {
    $summary = '';
    $title_lower = strtolower($title);
    
    // Keywords to detect topics
    $topic_keywords = [
        'database' => 'database management and design',
        'sql' => 'Structured Query Language and database queries',
        'ai' => 'Artificial Intelligence and machine learning',
        'artificial intelligence' => 'Artificial Intelligence and machine learning',
        'machine learning' => 'machine learning algorithms and applications',
        'network' => 'computer networking and communication',
        'security' => 'cybersecurity and information protection',
        'software' => 'software engineering and development',
        'programming' => 'programming concepts and coding',
        'design' => 'system design and architecture',
        'architecture' => 'system architecture and structure',
        'management' => 'project and resource management',
        'analysis' => 'data analysis and interpretation',
        'development' => 'software development and implementation',
        'cloud' => 'cloud computing and services',
        'data' => 'data management and processing',
        'system' => 'system design and implementation',
        'application' => 'application development and deployment',
        'web' => 'web development and technologies',
        'mobile' => 'mobile application development',
        'cyber' => 'cybersecurity and digital protection',
        'digital' => 'digital transformation and technology',
        'algorithm' => 'algorithms and computational methods',
        'structure' => 'data structures and organization',
        'normalization' => 'database normalization and optimization',
        'erd' => 'Entity Relationship Diagram design',
        'uml' => 'Unified Modeling Language diagrams',
        'diagram' => 'diagrams and visual representations',
        'chart' => 'charts and graphical data',
        'graph' => 'graphs and data visualization'
    ];
    
    // Detect topics from title
    $detected_topics = [];
    foreach ($topic_keywords as $keyword => $description) {
        if (strpos($title_lower, $keyword) !== false) {
            $detected_topics[] = $description;
        }
    }
    
    // If no topics detected from title, try extracted text
    if (empty($detected_topics) && !empty($extracted_text)) {
        $text_lower = strtolower($extracted_text);
        foreach ($topic_keywords as $keyword => $description) {
            if (strpos($text_lower, $keyword) !== false) {
                $detected_topics[] = $description;
                if (count($detected_topics) >= 3) break;
            }
        }
    }
    
    // Build summary based on detected features and topics
    $has_diagram = $features['has_diagram'] ?? 0;
    $has_chart = $features['has_chart'] ?? 0;
    $has_image = $features['has_image'] ?? 0;
    $has_logo = $features['has_logo'] ?? 0;
    
    // Start building summary
    $summary = "This document";
    
    // Add topic if detected
    if (!empty($detected_topics)) {
        $topics = array_unique($detected_topics);
        if (count($topics) == 1) {
            $summary .= " discusses " . $topics[0];
        } else {
            $summary .= " covers " . implode(', ', array_slice($topics, 0, 2));
            if (count($topics) > 2) {
                $summary .= ", and other related topics";
            }
        }
    } else {
        $summary .= " provides comprehensive information about " . $title;
    }
    
    // Add content features
    $features_list = [];
    if ($has_diagram) $features_list[] = "diagrams";
    if ($has_chart) $features_list[] = "charts";
    if ($has_image) $features_list[] = "visual illustrations";
    if ($has_logo) $features_list[] = "branding elements";
    
    if (!empty($features_list)) {
        $summary .= ", featuring " . implode(' and ', $features_list);
    }
    
    // Add file type
    $file_types = [
        'pdf' => 'PDF document',
        'doc' => 'Word document',
        'docx' => 'Word document',
        'ppt' => 'PowerPoint presentation',
        'pptx' => 'PowerPoint presentation',
        'xls' => 'Excel spreadsheet',
        'xlsx' => 'Excel spreadsheet',
        'jpg' => 'image file',
        'png' => 'image file',
        'mp4' => 'video file'
    ];
    
    $file_type_desc = $file_types[$file_extension] ?? $file_extension . ' file';
    $summary .= " in " . $file_type_desc . " format";
    
    // Add purpose
    $summary .= ", designed to serve as a reference and educational material for the subject area.";
    
    // If we have extracted text, add first sentence
    if (!empty($extracted_text) && !str_starts_with($extracted_text, '[This is a scanned')) {
        $first_sentence = preg_split('/[.!?]+/', $extracted_text);
        if (!empty($first_sentence) && strlen(trim($first_sentence[0])) > 20) {
            $summary .= " The document begins with: \"" . trim($first_sentence[0]) . ".\"";
        }
    }
    
    return $summary;
}

// ==========================================
// GENERATE KEYWORDS FROM TITLE
// ==========================================
function generateKeywords($title, $extracted_text = '') {
    $keywords = [];
    $title_lower = strtolower($title);
    
    // Common technical keywords
    $tech_keywords = [
        'database', 'sql', 'ai', 'machine learning', 'deep learning', 'neural network',
        'network', 'security', 'software', 'programming', 'design', 'architecture',
        'management', 'analysis', 'development', 'cloud', 'data', 'system',
        'application', 'web', 'mobile', 'cyber', 'digital', 'algorithm',
        'structure', 'normalization', 'erd', 'uml', 'diagram', 'chart', 'graph',
        'advantages', 'disadvantages', 'benefits', 'features', 'implementation',
        'methodology', 'framework', 'architecture', 'infrastructure', 'protocol',
        'encryption', 'authentication', 'authorization', 'firewall', 'vpn',
        'api', 'rest', 'soap', 'microservices', 'docker', 'kubernetes',
        'javascript', 'python', 'java', 'php', 'html', 'css', 'react', 'angular',
        'nodejs', 'express', 'mongodb', 'mysql', 'postgresql', 'oracle',
        'agile', 'scrum', 'waterfall', 'devops', 'ci/cd', 'testing'
    ];
    
    // Extract keywords from title
    $title_words = explode(' ', str_replace(['-', '_', '/'], ' ', $title_lower));
    foreach ($title_words as $word) {
        $word = trim($word);
        if (strlen($word) > 2) {
            foreach ($tech_keywords as $keyword) {
                if (strpos($word, $keyword) !== false || strpos($keyword, $word) !== false) {
                    $keywords[] = $keyword;
                }
            }
        }
    }
    
    // If no keywords from title, try extracted text
    if (empty($keywords) && !empty($extracted_text)) {
        $text_lower = strtolower($extracted_text);
        foreach ($tech_keywords as $keyword) {
            if (strpos($text_lower, $keyword) !== false) {
                $keywords[] = $keyword;
                if (count($keywords) >= 5) break;
            }
        }
    }
    
    // If still empty, use default based on file type
    if (empty($keywords)) {
        $default_keywords = ['document', 'report', 'information'];
        $keywords = $default_keywords;
    }
    
    // Remove duplicates and limit
    $keywords = array_unique($keywords);
    return implode(', ', array_slice($keywords, 0, 8));
}

// ==========================================
// EXTRACT TEXT CONTENT (PHP Fallback)
// ==========================================
function extractTextContent($file_path, $file_extension) {
    $content = '';
    
    if ($file_extension === 'pdf') {
        $pdf_content = file_get_contents($file_path);
        if ($pdf_content) {
            // Try multiple extraction methods
            $extracted = '';
            
            // Method 1: BT/ET blocks
            preg_match_all('/BT(.*?)ET/s', $pdf_content, $text_blocks);
            foreach ($text_blocks[1] as $block) {
                $clean = preg_replace('/[^\x20-\x7E]/', ' ', $block);
                $clean = preg_replace('/\s+/', ' ', $clean);
                if (strlen($clean) > 10) {
                    $extracted .= $clean . ' ';
                }
            }
            
            // Method 2: Text in parentheses
            if (strlen($extracted) < 50) {
                preg_match_all('/\(([^)]{5,})\)/', $pdf_content, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $match) {
                        $clean = preg_replace('/[^\x20-\x7E]/', ' ', $match);
                        $clean = preg_replace('/\s+/', ' ', $clean);
                        if (strlen($clean) > 5) {
                            $extracted .= $clean . ' ';
                        }
                    }
                }
            }
            
            // Method 3: TJ operator
            if (strlen($extracted) < 50) {
                preg_match_all('/\[(.*?)\]\s*TJ/s', $pdf_content, $tj_blocks);
                foreach ($tj_blocks[1] as $block) {
                    preg_match_all('/\(([^)]*)\)/', $block, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $match) {
                            $clean = preg_replace('/[^\x20-\x7E]/', ' ', $match);
                            $clean = preg_replace('/\s+/', ' ', $clean);
                            if (strlen($clean) > 5) {
                                $extracted .= $clean . ' ';
                            }
                        }
                    }
                }
            }
            
            // Clean final content
            $content = preg_replace('/\s+/', ' ', $extracted);
            $content = trim($content);
            
            // If still empty, try simple text extraction
            if (empty($content)) {
                $text = strip_tags($pdf_content);
                $text = preg_replace('/[^\w\s\.\,\!\?\-\:\;]/', ' ', $text);
                $text = preg_replace('/\s+/', ' ', $text);
                $content = trim($text);
            }
        }
    } 
    elseif ($file_extension === 'docx') {
        $zip = new ZipArchive();
        if ($zip->open($file_path) === true) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml) {
                $content = strip_tags($xml);
                $content = html_entity_decode($content);
                $content = preg_replace('/\s+/', ' ', $content);
                $content = trim($content);
            }
            $zip->close();
        }
    } 
    elseif (in_array($file_extension, ['txt', 'csv', 'html', 'css', 'js', 'php', 'py', 'java', 'json', 'xml', 'rtf'])) {
        $content = file_get_contents($file_path);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
    }
    
    return $content;
}

// ==========================================
// AUTO DETECT CONTENT FEATURES
// ==========================================
function autoDetectContentFeatures($file_path, $file_extension, $title, $content = '') {
    $has_logo = 0;
    $has_image = 0;
    $has_diagram = 0;
    $has_chart = 0;
    $logo_type = NULL;
    $image_type = NULL;
    $diagram_type = NULL;
    $chart_type = NULL;
    
    $title_lower = strtolower($title);
    $content_lower = strtolower($content);
    
    // 1. DETECT FROM TITLE
    if (preg_match('/logo|brand|emblem|trademark|symbol|icon/i', $title_lower)) {
        $has_logo = 1;
        $logo_type = 'Logo';
    }
    if (preg_match('/image|picture|photo|screenshot|visual|gambar|foto|snapshot/i', $title_lower)) {
        $has_image = 1;
        $image_type = 'Image';
    }
    if (preg_match('/diagram|figure|fig|rajah|schematic|flowchart|architecture|design|layout|flow|process|class|sequence|use case|uml|erd|entity|relationship/i', $title_lower)) {
        $has_diagram = 1;
        $diagram_type = 'Diagram';
    }
    if (preg_match('/chart|graph|graf|carta|plot|bar|pie|line|histogram|scatter|trend|comparison/i', $title_lower)) {
        $has_chart = 1;
        $chart_type = 'Chart';
    }
    
    // 2. DETECT FROM FILE EXTENSION
    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])) {
        $has_image = 1;
        if (!$image_type) $image_type = 'Image File';
        if ($file_extension === 'svg') {
            $has_diagram = 1;
            if (!$diagram_type) $diagram_type = 'Vector Diagram';
        }
    }
    
    if (in_array($file_extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'])) {
        $has_image = 1;
        if (!$image_type) $image_type = 'Video Thumbnail';
    }
    
    if (in_array($file_extension, ['ppt', 'pptx'])) {
        $has_diagram = 1;
        $has_chart = 1;
        if (!$diagram_type) $diagram_type = 'Presentation Slides';
        if (!$chart_type) $chart_type = 'Presentation Charts';
    }
    
    if (in_array($file_extension, ['xls', 'xlsx'])) {
        $has_chart = 1;
        if (!$chart_type) $chart_type = 'Spreadsheet Charts';
    }
    
    // 3. DETECT FROM FILE SIZE
    if (file_exists($file_path)) {
        $size = filesize($file_path);
        if ($file_extension === 'pdf' && $size > 300000) {
            $has_image = 1;
            if (!$image_type) $image_type = 'Embedded Images';
        }
        if ($file_extension === 'pdf' && $size > 1500000) {
            $has_diagram = 1;
            $has_chart = 1;
            if (!$diagram_type) $diagram_type = 'Diagrams';
            if (!$chart_type) $chart_type = 'Charts';
        }
        if (in_array($file_extension, ['jpg', 'jpeg', 'png']) && $size > 100000) {
            $has_image = 1;
            if (!$image_type) $image_type = 'High Quality Image';
        }
    }
    
    // 4. DETECT FROM CONTENT
    if (!empty($content)) {
        if (preg_match('/logo|brand|emblem|trademark|symbol|watermark/i', $content_lower)) {
            $has_logo = 1;
            if (!$logo_type) $logo_type = 'Logo';
        }
        if (preg_match('/image|picture|photo|screenshot|visual|gambar|foto|snapshot|illustration/i', $content_lower)) {
            $has_image = 1;
            if (!$image_type) $image_type = 'Images';
        }
        if (preg_match('/diagram|figure|fig|rajah|schematic|flowchart|architecture|design|layout|flow|process|class diagram|sequence diagram|uml|entity relationship|erd/i', $content_lower)) {
            $has_diagram = 1;
            if (!$diagram_type) $diagram_type = 'Diagram';
        }
        if (preg_match('/chart|graph|graf|carta|plot|bar chart|pie chart|line graph|histogram|scatter|comparison|trend/i', $content_lower)) {
            $has_chart = 1;
            if (!$chart_type) $chart_type = 'Chart';
        }
        if (preg_match('/figure \d|fig\. \d|rajah \d/i', $content_lower)) {
            $has_diagram = 1;
            if (!$diagram_type) $diagram_type = 'Figures';
        }
        if (preg_match('/table \d|jadual \d|tbl \d/i', $content_lower)) {
            $has_chart = 1;
            if (!$chart_type) $chart_type = 'Tables/Charts';
        }
    }
    
    // 5. PDF SPECIFIC
    if ($file_extension === 'pdf') {
        $pdf_content = file_get_contents($file_path);
        if ($pdf_content) {
            if (strpos($pdf_content, '/Image') !== false || 
                strpos($pdf_content, '/XObject') !== false ||
                preg_match('/\/Subtype\s*\/Image/', $pdf_content)) {
                $has_image = 1;
                if (!$image_type) $image_type = 'Embedded Images';
            }
            if (strpos($pdf_content, '/Form') !== false || 
                strpos($pdf_content, '/BBox') !== false) {
                $has_diagram = 1;
                if (!$diagram_type) $diagram_type = 'Form Elements';
            }
        }
    }
    
    // 6. DOCX SPECIFIC
    if ($file_extension === 'docx') {
        $zip = new ZipArchive();
        if ($zip->open($file_path) === true) {
            $image_count = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (strpos($filename, 'word/media/') !== false) {
                    $image_count++;
                }
            }
            if ($image_count > 0) {
                $has_image = 1;
                if (!$image_type) $image_type = 'Embedded Images';
                if ($image_count > 2) {
                    $has_diagram = 1;
                    if (!$diagram_type) $diagram_type = 'Multiple Diagrams';
                }
            }
            $zip->close();
        }
    }
    
    return [
        'has_logo' => $has_logo,
        'has_image' => $has_image,
        'has_diagram' => $has_diagram,
        'has_chart' => $has_chart,
        'logo_type' => $logo_type,
        'image_type' => $image_type,
        'diagram_type' => $diagram_type,
        'chart_type' => $chart_type
    ];
}

// ==========================================
// PROCESS UPLOAD
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category_id = intval($_POST['category_id']);
    $publication_year = intval($_POST['publication_year']);
    $page_count = intval($_POST['page_count']);
    
    $manual_keywords = trim($_POST['keywords']);
    $manual_summary = trim($_POST['summary']);
    
    if (empty($title) || empty($author) || $category_id == 0) {
        $error = "Please fill in all required fields.";
    } elseif (!isset($_FILES["file"]) || $_FILES["file"]["error"] != 0) {
        $error = "Please select a file to upload.";
    } else {
        $file_extension = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
        
        if (!isset($allowed_types[$file_extension])) {
            $error = "File type .$file_extension is not allowed. Allowed types: " . implode(', ', array_keys($allowed_types));
        } else {
            $target_dir = "uploads/";
            $original_name = pathinfo($_FILES["file"]["name"], PATHINFO_FILENAME);
            $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $original_name);
            $file_name = time() . '_' . $safe_name . '.' . $file_extension;
            $target_file = $target_dir . $file_name;
            
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                
                // ==========================================
                // USE PYTHON FOR PDF EXTRACTION
                // ==========================================
                $python_result = false;
                $extracted_text = '';
                $features = [];
                $auto_summary = '';
                $auto_keywords = '';
                
                if ($file_extension === 'pdf') {
                    $python_result = extractWithPython($target_file, $title);
                }
                
                if ($python_result && !isset($python_result['error'])) {
                    // Use Python result
                    $extracted_text = $python_result['text'] ?? '';
                    $features = [
                        'has_logo' => $python_result['has_logo'] ?? 0,
                        'has_image' => $python_result['has_image'] ?? 0,
                        'has_diagram' => $python_result['has_diagram'] ?? 0,
                        'has_chart' => $python_result['has_chart'] ?? 0,
                        'logo_type' => $python_result['has_logo'] ? 'Logo' : NULL,
                        'image_type' => $python_result['has_image'] ? 'Images' : NULL,
                        'diagram_type' => $python_result['has_diagram'] ? 'Diagram' : NULL,
                        'chart_type' => $python_result['has_chart'] ? 'Chart' : NULL
                    ];
                    
                    $auto_summary = $python_result['summary'] ?? '';
                    $auto_keywords = $python_result['keywords'] ?? '';
                    
                    // Update page count
                    if (!empty($python_result['page_count'])) {
                        $page_count = $python_result['page_count'];
                    }
                } else {
                    // Fallback to PHP extraction
                    $extracted_text = extractTextContent($target_file, $file_extension);
                    $features = autoDetectContentFeatures($target_file, $file_extension, $title, $extracted_text);
                }
                
                // Generate keywords
                if (empty($manual_keywords)) {
                    if (!empty($auto_keywords)) {
                        $keywords = $auto_keywords;
                    } else {
                        $keywords = generateKeywords($title, $extracted_text);
                    }
                } else {
                    $keywords = $manual_keywords;
                }
                
                // Generate summary
                if (empty($manual_summary)) {
                    if (!empty($auto_summary)) {
                        $summary = $auto_summary;
                    } else {
                        $summary = generateSummary($title, $features, $file_extension, $extracted_text);
                    }
                } else {
                    $summary = $manual_summary;
                }
                
                $file_icon = $allowed_types[$file_extension]['icon'];
                $file_type_name = $allowed_types[$file_extension]['type'];
                
                // Insert into document
                $sql = "INSERT INTO document (title, author, file_path, upload_date, page_count, publication_year, category_id) 
                        VALUES (?, ?, ?, CURDATE(), ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssiii", $title, $author, $target_file, $page_count, $publication_year, $category_id);
                
                if ($stmt->execute()) {
                    $document_id = $conn->insert_id;
                    
                    // Insert into metadata
                    $meta_sql = "INSERT INTO metadata (document_id, keywords, summary) VALUES (?, ?, ?)";
                    $meta_stmt = $conn->prepare($meta_sql);
                    $meta_stmt->bind_param("iss", $document_id, $keywords, $summary);
                    $meta_stmt->execute();
                    
                    // ==========================================
                    // INSERT INTO content_features
                    // ==========================================
                    $feature_vector = "logo:{$features['has_logo']},image:{$features['has_image']},diagram:{$features['has_diagram']},chart:{$features['has_chart']}";
                    
                    // Prepare values with proper NULL handling
                    $logo_type = $features['logo_type'] ?? NULL;
                    $image_type = $features['image_type'] ?? NULL;
                    $diagram_type = $features['diagram_type'] ?? NULL;
                    $chart_type = $features['chart_type'] ?? NULL;
                    
                    $has_logo = $features['has_logo'] ?? 0;
                    $has_image = $features['has_image'] ?? 0;
                    $has_diagram = $features['has_diagram'] ?? 0;
                    $has_chart = $features['has_chart'] ?? 0;
                    
                    $cf_sql = "INSERT INTO content_features 
                              (document_id, logo_type, image_type, diagram_type, chart_type, 
                               has_logo, has_image, has_diagram, has_chart, feature_vector) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $cf_stmt = $conn->prepare($cf_sql);
                    
                    // Bind parameters
                    $cf_stmt->bind_param(
                        "issssiiiss", 
                        $document_id,
                        $logo_type,
                        $image_type,
                        $diagram_type,
                        $chart_type,
                        $has_logo,
                        $has_image,
                        $has_diagram,
                        $has_chart,
                        $feature_vector
                    );
                    $cf_stmt->execute();
                    
                    // Build success message
                    $message = "✅ Document uploaded successfully!<br>";
                    $message .= "📁 File type: $file_icon $file_type_name<br>";
                    $message .= "📄 Pages: $page_count<br>";
                    $message .= "🔑 Keywords: " . htmlspecialchars($keywords) . "<br>";
                    $message .= "📝 Summary: " . htmlspecialchars($summary) . "<br><br>";
                    $message .= "📊 <strong>Auto-Detected Features:</strong><br>";
                    $message .= "🔴 Logo: " . ($features['has_logo'] ? '✅ YES' : '❌ NO');
                    if (!empty($features['logo_type'])) $message .= " (" . htmlspecialchars($features['logo_type']) . ")";
                    $message .= "<br>";
                    $message .= "🖼️ Image: " . ($features['has_image'] ? '✅ YES' : '❌ NO');
                    if (!empty($features['image_type'])) $message .= " (" . htmlspecialchars($features['image_type']) . ")";
                    $message .= "<br>";
                    $message .= "📊 Diagram: " . ($features['has_diagram'] ? '✅ YES' : '❌ NO');
                    if (!empty($features['diagram_type'])) $message .= " (" . htmlspecialchars($features['diagram_type']) . ")";
                    $message .= "<br>";
                    $message .= "📈 Chart: " . ($features['has_chart'] ? '✅ YES' : '❌ NO');
                    if (!empty($features['chart_type'])) $message .= " (" . htmlspecialchars($features['chart_type']) . ")";
                    $message .= "<br><br>";
                    $message .= "🤖 Using: " . ($python_result ? '✅ Python Extraction' : 'PHP Extraction');
                    $message .= "<br><br>⏳ Redirecting to system...";
                    
                    header("refresh:5;url=index.php");
                } else {
                    $error = "Database error: " . $conn->error;
                }
            } else {
                $error = "Failed to save file. Check folder permissions.";
            }
        }
    }
}

$categories = $conn->query("SELECT * FROM category ORDER BY category_name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Document - AI Document Repository</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0e17;
            padding: 20px;
            min-height: 100vh;
            color: #e0e0e0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #0d1b2a 0%, #1b2838 100%);
            border-radius: 10px;
            padding: 25px 30px;
            margin-bottom: 20px;
            border: 1px solid #2a3f5f;
            box-shadow: 0 2px 20px rgba(0,0,0,0.5);
        }

        .header h1 {
            color: #64b5f6;
            font-size: 24px;
            margin-bottom: 8px;
            text-shadow: 0 0 20px rgba(100,181,246,0.2);
        }

        .header p {
            color: #8899bb;
            font-size: 13px;
        }

        .header .badge {
            display: inline-block;
            background: #1a3a5a;
            color: #64b5f6;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 11px;
            margin-top: 8px;
        }

        /* Main Card */
        .card {
            background: linear-gradient(135deg, #0d1b2a 0%, #162433 100%);
            border-radius: 10px;
            padding: 30px;
            border: 1px solid #1a3050;
            box-shadow: 0 2px 15px rgba(0,0,0,0.4);
        }

        .card-title {
            color: #64b5f6;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .card-subtitle {
            color: #667799;
            font-size: 13px;
            margin-bottom: 25px;
        }

        /* Form */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #aabbdd;
            font-size: 13px;
            font-weight: 500;
        }

        .form-group label .required {
            color: #f44336;
        }

        .form-group label .auto-badge {
            display: inline-block;
            background: #1a3a2a;
            color: #64f6a0;
            padding: 1px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: normal;
            margin-left: 8px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            background: #0a1520;
            border: 1px solid #1a3050;
            border-radius: 6px;
            font-size: 14px;
            color: #e0e0e0;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #64b5f6;
            box-shadow: 0 0 15px rgba(100,181,246,0.1);
        }

        .form-control::placeholder {
            color: #445566;
        }

        select.form-control option {
            background: #0a1520;
            color: #e0e0e0;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 70px;
            font-family: inherit;
        }

        /* File Input */
        .file-upload-wrapper {
            position: relative;
            border: 2px dashed #1a3050;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #0a1520;
        }

        .file-upload-wrapper:hover {
            border-color: #64b5f6;
            background: #0f1f30;
        }

        .file-upload-wrapper .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .file-upload-wrapper .text {
            color: #8899bb;
            font-size: 14px;
        }

        .file-upload-wrapper .text strong {
            color: #64b5f6;
        }

        .file-upload-wrapper .sub-text {
            color: #445566;
            font-size: 12px;
            margin-top: 8px;
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-wrapper .file-name {
            color: #64f6a0;
            font-size: 13px;
            margin-top: 10px;
            display: none;
        }

        .file-upload-wrapper.has-file {
            border-color: #2a7a5a;
            background: #0a1a15;
        }

        .file-upload-wrapper.has-file .file-name {
            display: block;
        }

        /* Feature Box */
        .feature-box {
            background: #0a1520;
            border: 1px solid #1a3050;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 18px;
        }

        .feature-box .feature-label {
            color: #aabbdd;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .feature-box .feature-label .auto-badge {
            display: inline-block;
            background: #1a3a5a;
            color: #64b5f6;
            padding: 1px 10px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: normal;
            margin-left: 8px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #8899bb;
            font-size: 13px;
        }

        .feature-item .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        .feature-item .status.auto {
            background: #1a3a5a;
            color: #64b5f6;
        }

        /* Buttons */
        .btn-row {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1a4a7a 0%, #0d2d5c 100%);
            color: #e0e0e0;
            border: 1px solid #2a5a8a;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1f5a8a 0%, #123a6a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 25px rgba(26,74,122,0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
            color: #8899bb;
            border: 1px solid #3a3a3a;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #3a3a3a 0%, #2a2a2a 100%);
        }

        .btn-success {
            background: linear-gradient(135deg, #1a7a5a 0%, #0d5c3f 100%);
            color: #e0e0e0;
            border: 1px solid #2a9d6a;
            width: 100%;
            justify-content: center;
            padding: 14px;
            font-size: 16px;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #1f8f6a 0%, #0f6a4a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 25px rgba(42,157,106,0.3);
        }

        /* Messages */
        .message {
            background: #0a1a15;
            border: 1px solid #2a7a5a;
            color: #64f6a0;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error {
            background: #1a0a0a;
            border: 1px solid #7a2a2a;
            color: #f6a0a0;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .message .small, .error .small {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #1a3050;
            margin: 20px 0;
        }

        /* Info Box */
        .info-box {
            background: #0a1520;
            border: 1px solid #1a3050;
            border-radius: 6px;
            padding: 15px 20px;
            margin-top: 20px;
        }

        .info-box .title {
            color: #aabbdd;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .info-box .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .info-box .tag {
            display: inline-block;
            background: #0d1b2a;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 11px;
            color: #8899bb;
            border: 1px solid #1a3050;
        }

        .info-box .desc {
            color: #445566;
            font-size: 12px;
            margin-top: 10px;
            line-height: 1.6;
        }

        .info-box .desc strong {
            color: #8899bb;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                padding: 0 10px;
            }
            .card {
                padding: 20px;
            }
            .feature-grid {
                grid-template-columns: 1fr;
            }
            .btn-row {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
            .header h1 {
                font-size: 20px;
            }
            .header {
                padding: 18px 20px;
            }
            .card-title {
                font-size: 18px;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0a1520;
        }
        ::-webkit-scrollbar-thumb {
            background: #1a3050;
            border-radius: 5px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #2a4a6a;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- HEADER -->
    <div class="header">
        <h1>📚 AI DOCUMENT REPOSITORY</h1>
        <p>Using ABR (Attribute-Based Retrieval) | TBR (Text-Based Retrieval) | CBR (Content-Based Retrieval) Techniques</p>
        <span class="badge">🎓 MD_GW05 - Multimedia Database System</span>
    </div>

    <!-- MAIN CARD -->
    <div class="card">
        <div class="card-title">📤 UPLOAD DOCUMENT</div>
        <div class="card-subtitle">Support pelbagai jenis file | Auto-detect keywords, summary &amp; visual features</div>

        <?php if($message): ?>
            <div class="message">
                <?= $message ?>
                <div class="small">⏳ Redirecting to system...</div>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="error">
                ❌ <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="uploadForm">

            <!-- Title -->
            <div class="form-group">
                <label>📄 Document Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="e.g., AI in Database Management">
            </div>

            <!-- Author -->
            <div class="form-group">
                <label>👨‍🏫 Author <span class="required">*</span></label>
                <input type="text" name="author" class="form-control" required placeholder="e.g., Dr. Ahmad">
            </div>

            <!-- Category -->
            <div class="form-group">
                <label>📁 Category <span class="required">*</span></label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['category_id'] ?>"><?= $cat['category_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Year & Pages -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>📅 Publication Year</label>
                    <input type="number" name="publication_year" class="form-control" value="<?= date('Y') ?>">
                </div>
                <div class="form-group">
                    <label>📖 Page Count</label>
                    <input type="number" name="page_count" class="form-control" placeholder="e.g., 25">
                </div>
            </div>

            <!-- Keywords -->
            <div class="form-group">
                <label>🔑 Keywords <span class="auto-badge">Auto-detect if empty</span></label>
                <textarea name="keywords" class="form-control" placeholder="Leave empty to auto-detect from title and content"></textarea>
            </div>

            <!-- Summary -->
            <div class="form-group">
                <label>📝 Summary <span class="auto-badge">Auto-generate if empty</span></label>
                <textarea name="summary" class="form-control" placeholder="Leave empty to auto-generate from title and detected features"></textarea>
            </div>

            <!-- Feature Box -->
            <div class="feature-box">
                <div class="feature-label">🤖 Auto Detect Visual Features <span class="auto-badge">Auto</span></div>
                <div class="feature-grid">
                    <div class="feature-item">
                        <span>🔴</span> Logo
                        <span class="status auto">AUTO</span>
                    </div>
                    <div class="feature-item">
                        <span>🖼️</span> Image
                        <span class="status auto">AUTO</span>
                    </div>
                    <div class="feature-item">
                        <span>📊</span> Diagram
                        <span class="status auto">AUTO</span>
                    </div>
                    <div class="feature-item">
                        <span>📈</span> Chart
                        <span class="status auto">AUTO</span>
                    </div>
                </div>
                <div style="font-size: 11px; color: #445566; margin-top: 10px;">
                    ✅ Based on: filename, file size, file type, content analysis
                </div>
            </div>

            <!-- File Upload -->
            <div class="form-group">
                <label>📎 Choose File <span class="required">*</span></label>
                <div class="file-upload-wrapper" id="fileUploadWrapper">
                    <div class="icon">📄</div>
                    <div class="text">Drop your file here or <strong>browse</strong></div>
                    <div class="sub-text">Support: PDF, Word, Excel, CSV, Text, Images, Videos, Audio, Code files</div>
                    <div class="file-name" id="fileName"></div>
                    <input type="file" name="file" id="fileInput" required>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-success">🤖 UPLOAD &amp; AUTO-DETECT</button>

            <!-- Back Button -->
            <div style="margin-top: 12px; text-align: center;">
                <a href="index.php" style="color: #667799; text-decoration: none; font-size: 13px;">
                    ⬅ BACK TO SYSTEM
                </a>
            </div>

            <hr class="divider">

            <!-- Info Box -->
            <div class="info-box">
                <div class="title">📁 Jenis File Yang Disokong</div>
                <div class="tags">
                    <span class="tag">📄 PDF</span>
                    <span class="tag">📝 WORD (doc, docx)</span>
                    <span class="tag">📊 EXCEL (xls, xlsx)</span>
                    <span class="tag">📃 CSV</span>
                    <span class="tag">📃 TEXT (txt, rtf)</span>
                    <span class="tag">🖼️ IMAGE (jpg, png, gif)</span>
                    <span class="tag">🎥 VIDEO (mp4, avi, mov)</span>
                    <span class="tag">🎵 AUDIO (mp3, wav)</span>
                    <span class="tag">📽️ POWERPOINT (ppt, pptx)</span>
                    <span class="tag">🗜️ ARCHIVE (zip, rar)</span>
                    <span class="tag">💻 CODE (html, css, js, php, py)</span>
                </div>
                <div class="desc">
                    <strong>🤖 Auto-Detection:</strong>
                    <br>• Keywords diekstrak dari title dan content
                    <br>• Summary di-generate dari title dan detected features
                    <br>• Visual features (Logo, Image, Diagram, Chart) auto-detect
                    <br>• PDF extraction menggunakan <strong>Python</strong> (lebih tepat)
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    // File upload display
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const wrapper = document.getElementById('fileUploadWrapper');
        const fileName = document.getElementById('fileName');
        const file = this.files[0];
        
        if (file) {
            const size = (file.size / 1024 / 1024).toFixed(2);
            fileName.textContent = '📎 ' + file.name + ' (' + size + ' MB)';
            wrapper.classList.add('has-file');
        } else {
            fileName.textContent = '';
            wrapper.classList.remove('has-file');
        }
    });

    // Drag and drop visual
    const wrapper = document.getElementById('fileUploadWrapper');
    wrapper.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#64b5f6';
        this.style.background = '#0f1f30';
    });
    wrapper.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#1a3050';
        this.style.background = '#0a1520';
    });
    wrapper.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#1a3050';
        this.style.background = '#0a1520';
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('fileInput').files = files;
            document.getElementById('fileInput').dispatchEvent(new Event('change'));
        }
    });
</script>

</body>
</html>