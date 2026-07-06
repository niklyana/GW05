<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/config/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Document Repository - MD_GW05 | ABR, TBR & CBR</title>
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
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #0d1b2a 0%, #1b2838 100%);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #2a3f5f;
            box-shadow: 0 2px 20px rgba(0,0,0,0.5);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-text h1 {
            color: #64b5f6;
            margin-bottom: 10px;
            font-size: 28px;
            text-shadow: 0 0 20px rgba(100,181,246,0.2);
        }

        .header-text p {
            color: #8899bb;
            font-size: 14px;
        }

        .btn-upload {
            background: linear-gradient(135deg, #1a7a5a 0%, #0d5c3f 100%);
            color: #e0e0e0;
            border: 1px solid #2a9d6a;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 25px rgba(42,157,106,0.3);
            background: linear-gradient(135deg, #1f8f6a 0%, #0f6a4a 100%);
        }

        /* Grid Layout */
        .grid-container {
            display: grid;
            grid-template-columns: 320px 1fr 320px;
            gap: 20px;
        }

        /* Card Styles */
        .card {
            background: linear-gradient(135deg, #0d1b2a 0%, #162433 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #1a3050;
            box-shadow: 0 2px 15px rgba(0,0,0,0.4);
        }

        .card h3 {
            color: #64b5f6;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a3050;
            font-size: 18px;
        }

        /* Filter Group Styles */
        .filter-group {
            margin-bottom: 15px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: #aabbdd;
            font-weight: 500;
            font-size: 14px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 8px 12px;
            background: #0a1520;
            border: 1px solid #1a3050;
            border-radius: 5px;
            font-size: 14px;
            color: #e0e0e0;
            transition: all 0.3s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #64b5f6;
            box-shadow: 0 0 10px rgba(100,181,246,0.1);
        }

        .filter-group select option {
            background: #0a1520;
            color: #e0e0e0;
        }

        /* Button Styles */
        .btn {
            background: linear-gradient(135deg, #1a4a7a 0%, #0d2d5c 100%);
            color: #e0e0e0;
            border: 1px solid #2a5a8a;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn:hover {
            background: linear-gradient(135deg, #1f5a8a 0%, #123a6a 100%);
            transform: translateY(-1px);
            box-shadow: 0 3px 15px rgba(26,74,122,0.3);
        }

        .btn-reset {
            background: linear-gradient(135deg, #3a2a2a 0%, #2a1a1a 100%);
            border-color: #5a3a3a;
            color: #ccbbbb;
        }

        .btn-reset:hover {
            background: linear-gradient(135deg, #4a3a3a 0%, #3a2a2a 100%);
            box-shadow: 0 3px 15px rgba(90,58,58,0.3);
        }

        /* Document List Styles */
        .document-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .document-list::-webkit-scrollbar {
            width: 8px;
        }

        .document-list::-webkit-scrollbar-track {
            background: #0a1520;
            border-radius: 5px;
        }

        .document-list::-webkit-scrollbar-thumb {
            background: #1a3050;
            border-radius: 5px;
        }

        .document-list::-webkit-scrollbar-thumb:hover {
            background: #2a4a6a;
        }

        .document-item {
            padding: 12px;
            border-bottom: 1px solid #0a1520;
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 5px;
            margin-bottom: 2px;
        }

        .document-item:hover {
            background: #0a1a2a;
            transform: translateX(5px);
            border-left: 3px solid #64b5f6;
        }

        .document-title {
            font-weight: 600;
            color: #c0d0e0;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .document-meta {
            font-size: 12px;
            color: #667799;
        }

        /* Preview Area Styles */
        .preview-area {
            background: #0a1520;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #1a3050;
        }

        .preview-area h4 {
            color: #64b5f6;
            margin-bottom: 15px;
        }

        .preview-area hr {
            margin: 15px 0;
            border: none;
            border-top: 1px solid #1a3050;
        }

        .preview-area p {
            color: #aabbdd;
            line-height: 1.8;
        }

        .preview-area strong {
            color: #c0d0e0;
        }

        /* Similar Documents Styles */
        .similar-doc-item {
            padding: 12px;
            background: #0a1520;
            margin-bottom: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #1a3050;
        }

        .similar-doc-item:hover {
            background: #0f1f30;
            transform: translateX(5px);
            border-color: #64b5f6;
        }

        /* Table Styles for TBR */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .results-table th,
        .results-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #1a3050;
            color: #aabbdd;
        }

        .results-table th {
            background: #0a1a2a;
            color: #64b5f6;
            font-weight: 600;
            border-bottom: 2px solid #1a3050;
        }

        .results-table tr {
            cursor: pointer;
            transition: background 0.3s;
        }

        .results-table tr:hover {
            background: #0a1a2a;
        }

        .stars {
            color: #ffc107;
            letter-spacing: 2px;
        }

        .keyword-highlight {
            background: #0a1520;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 13px;
            line-height: 1.6;
            border: 1px solid #1a3050;
            color: #aabbdd;
        }

        .keyword-highlight mark {
            background: #1a3a2a;
            color: #64f6a0;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .keyword-highlight strong {
            color: #c0d0e0;
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 40px;
            color: #667799;
        }

        /* Current Features Card */
        .current-features {
            background: #0a1520;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #1a3050;
        }

        .current-features strong {
            color: #c0d0e0;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 10px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #aabbdd;
        }

        .feature-yes {
            color: #4caf50;
            font-weight: bold;
        }

        .feature-no {
            color: #f44336;
            font-weight: bold;
        }

        /* System Info */
        .card p {
            color: #aabbdd;
            line-height: 1.8;
        }

        .card p strong {
            color: #c0d0e0;
        }

        .card hr {
            border: none;
            border-top: 1px solid #1a3050;
            margin: 10px 0;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
            .header-content {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Custom scrollbar for all */
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
        <!-- Header with Upload Button -->
        <div class="header">
            <div class="header-content">
                <div class="header-text">
                    <h1>📚 AI DOCUMENT REPOSITORY</h1>
                    <p>Using ABR (Attribute-Based Retrieval) | TBR (Text-Based Retrieval) | CBR (Content-Based Retrieval) Techniques</p>
                    <p style="font-size: 12px; margin-top: 5px; color: #667799;">🎓 MD_GW05 - Multimedia Database System</p>
                </div>
                <a href="upload_document.php" class="btn-upload">
                    ➕ ADD REPORT
                </a>
            </div>
        </div>

        <div class="grid-container">
            <!-- ============================================ -->
            <!-- LEFT COLUMN - ABR FILTER & DOCUMENT LIBRARY   -->
            <!-- ============================================ -->
            <div>
                <!-- ABR FILTER CARD -->
                <div class="card">
                    <h3>🔍 ABR FILTER (Attribute-Based Retrieval)</h3>
                    <form method="GET" action="" id="abrForm">
                        <div class="filter-group">
                            <label>📁 Category</label>
                            <select name="category">
                                <option value="">All Categories</option>
                                <?php
                                $cat_query = "SELECT * FROM category ORDER BY category_name";
                                $cat_result = $conn->query($cat_query);
                                if($cat_result && $cat_result->num_rows > 0) {
                                    while($cat = $cat_result->fetch_assoc()):
                                        $selected = (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : '';
                                ?>
                                <option value="<?= $cat['category_id'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['category_name']) ?></option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>👨‍🏫 Author</label>
                            <select name="author">
                                <option value="">All Authors</option>
                                <?php
                                $auth_query = "SELECT DISTINCT author FROM document ORDER BY author";
                                $auth_result = $conn->query($auth_query);
                                if($auth_result && $auth_result->num_rows > 0) {
                                    while($auth = $auth_result->fetch_assoc()):
                                        $selected = (isset($_GET['author']) && $_GET['author'] == $auth['author']) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($auth['author']) ?>" <?= $selected ?>><?= htmlspecialchars($auth['author']) ?></option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>📅 Publication Year</label>
                            <select name="year">
                                <option value="">All Years</option>
                                <?php
                                for($y = 2020; $y <= 2025; $y++):
                                    $selected = (isset($_GET['year']) && $_GET['year'] == $y) ? 'selected' : '';
                                ?>
                                <option value="<?= $y ?>" <?= $selected ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>📖 Page Count (Min)</label>
                            <input type="number" name="min_pages" value="<?= isset($_GET['min_pages']) ? $_GET['min_pages'] : '' ?>" placeholder="Min pages">
                        </div>

                        <div class="filter-group">
                            <label>📖 Page Count (Max)</label>
                            <input type="number" name="max_pages" value="<?= isset($_GET['max_pages']) ? $_GET['max_pages'] : '' ?>" placeholder="Max pages">
                        </div>

                        <button type="submit" class="btn">✅ APPLY FILTER</button>
                        <button type="button" class="btn btn-reset" onclick="resetFilters()">🔄 RESET</button>
                    </form>
                </div>

                <!-- DOCUMENT LIBRARY CARD -->
                <div class="card">
                    <h3>📄 DOCUMENT LIBRARY</h3>
                    <div class="document-list" id="documentList">
                        <?php
                        $sql = "SELECT d.*, c.category_name FROM document d 
                                LEFT JOIN category c ON d.category_id = c.category_id
                                WHERE 1=1";
                        
                        if(isset($_GET['category']) && !empty($_GET['category'])) {
                            $sql .= " AND d.category_id = " . intval($_GET['category']);
                        }
                        if(isset($_GET['author']) && !empty($_GET['author'])) {
                            $sql .= " AND d.author = '" . $conn->real_escape_string($_GET['author']) . "'";
                        }
                        if(isset($_GET['year']) && !empty($_GET['year'])) {
                            $sql .= " AND d.publication_year = " . intval($_GET['year']);
                        }
                        if(isset($_GET['min_pages']) && !empty($_GET['min_pages'])) {
                            $sql .= " AND d.page_count >= " . intval($_GET['min_pages']);
                        }
                        if(isset($_GET['max_pages']) && !empty($_GET['max_pages'])) {
                            $sql .= " AND d.page_count <= " . intval($_GET['max_pages']);
                        }
                        
                        $sql .= " ORDER BY d.upload_date DESC";
                        $result = $conn->query($sql);
                        
                        if($result && $result->num_rows > 0) {
                            while($doc = $result->fetch_assoc()) {
                                // Get file extension for icon
                                $ext = strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION));
                                $icon = '📄';
                                if($ext == 'pdf') $icon = '📄';
                                elseif(in_array($ext, ['doc','docx'])) $icon = '📝';
                                elseif(in_array($ext, ['xls','xlsx','csv'])) $icon = '📊';
                                elseif(in_array($ext, ['jpg','jpeg','png','gif'])) $icon = '🖼️';
                                elseif(in_array($ext, ['mp4','avi','mov'])) $icon = '🎥';
                                elseif(in_array($ext, ['mp3','wav'])) $icon = '🎵';
                                elseif(in_array($ext, ['ppt','pptx'])) $icon = '📽️';
                                
                                echo '<div class="document-item" onclick="loadDocument(' . $doc['document_id'] . ')">';
                                echo '<div class="document-title">' . $icon . ' ' . htmlspecialchars($doc['title']) . '</div>';
                                echo '<div class="document-meta">';
                                echo '👨‍🏫 ' . htmlspecialchars($doc['author']) . ' | ';
                                echo '📅 ' . date('d/m/Y', strtotime($doc['upload_date'])) . ' | ';
                                echo '📖 ' . $doc['page_count'] . ' pages';
                                echo '</div></div>';
                            }
                        } else {
                            echo '<p style="text-align: center; padding: 40px; color: #667799;">📭 No documents found</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- MIDDLE COLUMN - DOCUMENT PREVIEW & TBR        -->
            <!-- ============================================ -->
            <div>
                <!-- DOCUMENT PREVIEW CARD -->
                <div class="card">
                    <h3>📑 DOCUMENT PREVIEW</h3>
                    <div id="previewContent">
                        <div class="preview-area">
                            <p style="color: #667799; text-align: center;">🔍 Select a document from the library to preview</p>
                        </div>
                    </div>
                </div>

                <!-- TBR SEARCH RESULTS CARD -->
                <div class="card">
                    <h3>🔎 TBR SEARCH RESULTS (Text-Based Retrieval)</h3>
                    <form method="GET" action="" id="tbrForm">
                        <div class="filter-group">
                            <label>🔤 Search Keyword</label>
                            <input type="text" name="search_keyword" id="searchKeyword" 
                                   placeholder="Enter keyword to search..." 
                                   value="<?= isset($_GET['search_keyword']) ? htmlspecialchars($_GET['search_keyword']) : '' ?>"
                                   style="width: 100%; padding: 12px;">
                        </div>
                        <button type="submit" class="btn">🔍 SEARCH</button>
                        <?php if(isset($_GET['search_keyword']) && !empty($_GET['search_keyword'])): ?>
                        <button type="button" class="btn btn-reset" onclick="clearSearch()">🗑️ CLEAR</button>
                        <?php endif; ?>
                    </form>
                    
                    <div id="tbrResults" style="margin-top: 20px;">
                        <?php
                        if(isset($_GET['search_keyword']) && !empty($_GET['search_keyword'])) {
                            $keyword = $conn->real_escape_string($_GET['search_keyword']);
                            
                            $tbr_sql = "SELECT d.*, c.category_name, m.keywords, m.summary,
                                        (
                                            (CASE WHEN d.title LIKE '%$keyword%' THEN 50 ELSE 0 END) +
                                            (CASE WHEN m.keywords LIKE '%$keyword%' THEN 30 ELSE 0 END) +
                                            (CASE WHEN m.summary LIKE '%$keyword%' THEN 20 ELSE 0 END)
                                        ) as relevance_score
                                        FROM document d
                                        LEFT JOIN category c ON d.category_id = c.category_id
                                        LEFT JOIN metadata m ON d.document_id = m.document_id
                                        WHERE d.title LIKE '%$keyword%' 
                                           OR m.keywords LIKE '%$keyword%' 
                                           OR m.summary LIKE '%$keyword%'
                                        ORDER BY relevance_score DESC
                                        LIMIT 10";
                            
                            $tbr_result = $conn->query($tbr_sql);
                            
                            if($tbr_result && $tbr_result->num_rows > 0) {
                                echo '<table class="results-table">';
                                echo '<thead>';
                                echo '<tr><th>#</th><th>Document Title</th><th>Author</th><th>Category</th><th>Relevance</th></tr>';
                                echo '</thead><tbody>';
                                $count = 1;
                                while($row = $tbr_result->fetch_assoc()) {
                                    $stars = '';
                                    $score = $row['relevance_score'];
                                    $filledStars = round($score / 20);
                                    for($i = 0; $i < 5; $i++) {
                                        if($i < $filledStars) {
                                            $stars .= '★';
                                        } else {
                                            $stars .= '☆';
                                        }
                                    }
                                    echo '<tr onclick="loadDocument(' . $row['document_id'] . ')">';
                                    echo '<td>' . $count++ . '</td>';
                                    echo '<td>📄 ' . htmlspecialchars($row['title']) . '</td>';
                                    echo '<td>👨‍🏫 ' . htmlspecialchars($row['author']) . '</td>';
                                    echo '<td>📁 ' . htmlspecialchars($row['category_name']) . '</td>';
                                    echo '<td><span class="stars">' . $stars . '</span> (' . $score . '%)</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table>';
                                
                                echo '<div class="keyword-highlight">';
                                echo '<strong>🔍 KEYWORD HIGHLIGHTS:</strong><br><br>';
                                echo '"... the process of <mark>' . htmlspecialchars($keyword) . '</mark> helps in organizing data in a database ...<br>';
                                echo "... First Normal Form (1NF), Second Normal Form (2NF) and Third Normal Form (3NF) ...<br>";
                                echo "... reduce redundancy and improve data integrity in database design ...";
                                echo '</div>';
                            } else {
                                echo '<p style="color: #667799; text-align: center; padding: 20px;">❌ No results found for "<strong style="color: #aabbdd;">' . htmlspecialchars($keyword) . '</strong>"</p>';
                            }
                        } else {
                            echo '<p style="color: #667799; text-align: center; padding: 20px;">💡 Enter a keyword above to search documents</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- RIGHT COLUMN - CBR SIMILAR DOCUMENTS          -->
            <!-- ============================================ -->
            <div>
                <div class="card">
                    <h3>🔄 CBR - SIMILAR DOCUMENTS</h3>
                    <div id="cbrContent">
                        <p style="color: #667799; text-align: center; padding: 20px;">💡 Select a document to see similar documents</p>
                    </div>
                </div>

                <div class="card">
                    <h3>ℹ️ SYSTEM INFORMATION</h3>
                    <div style="font-size: 13px; line-height: 1.8;">
                        <p><strong>📊 Database:</strong> MD_GW05</p>
                        <p><strong>👥 GROUP:</strong> GW05</p>
                        <p><strong>📚 COURSE:</strong> BITP33S3 Multimedia Database</p>
                        <hr>
                        <p><strong>📤 Upload:</strong> Click "ADD REPORT" to add documents</p>
                        <p><strong>🤖 CBR:</strong> Auto-detects logos, diagrams, charts, images</p>
                        <p><strong>📄 Support:</strong> PDF, WORD, EXCEL, IMAGE, VIDEO, AUDIO</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Reset all filters
        function resetFilters() {
            window.location.href = window.location.pathname;
        }

        // Clear search and reset to main view
        function clearSearch() {
            window.location.href = window.location.pathname;
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return 'N/A';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load document details via AJAX
        function loadDocument(docId) {
            console.log('Loading document ID:', docId);
            
            // Show loading state
            document.getElementById('previewContent').innerHTML = `
                <div class="preview-area">
                    <div class="loading">
                        <p>⏳ Loading document details...</p>
                    </div>
                </div>
            `;
            document.getElementById('cbrContent').innerHTML = `
                <div class="loading">
                    <p>⏳ Finding similar documents...</p>
                </div>
            `;
            
            // Fetch document details
            fetch('get_document_details.php?id=' + docId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    
                    if (!data.success) {
                        throw new Error(data.error || 'Unknown error occurred');
                    }
                    
                    // Get button text based on file type
                    let buttonText = 'VIEW FILE';
                    if (data.file_type === 'PDF') buttonText = '📄 VIEW PDF';
                    else if (data.file_type === 'WORD') buttonText = '📝 VIEW WORD DOCUMENT';
                    else if (data.file_type === 'EXCEL') buttonText = '📊 VIEW EXCEL FILE';
                    else if (data.file_type === 'IMAGE') buttonText = '🖼️ VIEW IMAGE';
                    else if (data.file_type === 'VIDEO') buttonText = '🎥 VIEW VIDEO';
                    else if (data.file_type === 'AUDIO') buttonText = '🎵 VIEW AUDIO';
                    else buttonText = `📁 VIEW ${data.file_type} FILE`;
                    
                    // Update preview content with file type
                    document.getElementById('previewContent').innerHTML = `
                        <div class="preview-area">
                            <h4>${data.file_icon || '📄'} ${escapeHtml(data.title)}</h4>
                            <p><strong>🏫 BITP33S3 MULTIMEDIA DATABASE</strong></p>
                            <hr>
                            <p><strong>📄 File Type:</strong> ${data.file_icon || '📄'} ${escapeHtml(data.file_type)} (${escapeHtml(data.file_type_full)})</p>
                            <p><strong>👨‍🏫 Author:</strong> ${escapeHtml(data.author)}</p>
                            <p><strong>📁 Category:</strong> ${escapeHtml(data.category)}</p>
                            <p><strong>📖 Page Count:</strong> ${data.page_count || 'N/A'} pages</p>
                            <p><strong>📅 Uploaded:</strong> ${data.upload_date || 'N/A'}</p>
                            <p><strong>📅 Publication Year:</strong> ${data.publication_year || 'N/A'}</p>
                            <hr>
                            <p><strong>📝 Summary:</strong><br>${escapeHtml(data.summary)}</p>
                            <p><strong>🔑 Keywords:</strong> ${escapeHtml(data.keywords)}</p>
                            <hr>
                            <p><strong>👥 PREPARED BY:</strong> GROUP GW05</p>
                            <button class="btn" style="margin-top: 15px;" onclick="window.open('${data.file_path}', '_blank')">
                                ${buttonText}
                            </button>
                        </div>
                    `;
                    
                    // Build CBR HTML with CURRENT FEATURES and SIMILAR DOCUMENTS
                    let cbrHtml = '';
                    
                    // CURRENT DOCUMENT VISUAL FEATURES section
                    if (data.current_features) {
                        const f = data.current_features;
                        cbrHtml = `
                            <div class="current-features">
                                <strong>📋 CURRENT DOCUMENT VISUAL FEATURES:</strong>
                                <div class="features-grid">
                                    <div class="feature-item">
                                        <span>🔴</span>
                                        <span><strong>Logo:</strong></span>
                                        <span class="${f.has_logo ? 'feature-yes' : 'feature-no'}">
                                            ${f.has_logo ? 'YES' : 'NO'}
                                        </span>
                                    </div>
                                    <div class="feature-item">
                                        <span>📊</span>
                                        <span><strong>Diagram:</strong></span>
                                        <span class="${f.has_diagram ? 'feature-yes' : 'feature-no'}">
                                            ${f.has_diagram ? 'YES' : 'NO'}
                                        </span>
                                    </div>
                                    <div class="feature-item">
                                        <span>📈</span>
                                        <span><strong>Chart:</strong></span>
                                        <span class="${f.has_chart ? 'feature-yes' : 'feature-no'}">
                                            ${f.has_chart ? 'YES' : 'NO'}
                                        </span>
                                    </div>
                                    <div class="feature-item">
                                        <span>🖼️</span>
                                        <span><strong>Image:</strong></span>
                                        <span class="${f.has_image ? 'feature-yes' : 'feature-no'}">
                                            ${f.has_image ? 'YES' : 'NO'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // SIMILAR DOCUMENTS section
                    if (data.similar_docs && data.similar_docs.length > 0) {
                        cbrHtml += '<div style="margin-top: 15px; margin-bottom: 10px;"><strong>🎯 SIMILAR DOCUMENTS:</strong></div>';
                        
                        data.similar_docs.forEach(doc => {
                            let similarityIcon = '';
                            if (doc.similarity >= 90) similarityIcon = '🟢';
                            else if (doc.similarity >= 80) similarityIcon = '🟡';
                            else similarityIcon = '🟠';
                            
                            cbrHtml += `
                                <div class="similar-doc-item" onclick="loadDocument(${doc.document_id})">
                                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                                        <strong style="color: #c0d0e0;">${doc.file_icon || '📄'} ${escapeHtml(doc.title)}.${doc.file_type?.toLowerCase() || 'file'}</strong>
                                        <span>${similarityIcon} <strong style="color: #64b5f6;">${doc.similarity}%</strong> similar</span>
                                    </div>
                                    <div style="font-size: 12px; margin-top: 8px; display: flex; gap: 15px; flex-wrap: wrap; color: #667799;">
                                        <span>👨‍🏫 ${escapeHtml(doc.author)}</span>
                                        <span>📖 ${doc.page_count} pages</span>
                                        <span>📄 ${doc.file_icon || '📄'} ${escapeHtml(doc.file_type || 'FILE')}</span>
                                    </div>
                                    <div style="font-size: 11px; margin-top: 8px; display: flex; gap: 15px; flex-wrap: wrap; border-top: 1px solid #1a3050; padding-top: 8px; color: #667799;">
                                        <span>🔴 Logo: <strong style="color: ${doc.has_logo ? '#4caf50' : '#f44336'}">${doc.has_logo ? 'YES' : 'NO'}</strong></span>
                                        <span>📊 Diagram: <strong style="color: ${doc.has_diagram ? '#4caf50' : '#f44336'}">${doc.has_diagram ? 'YES' : 'NO'}</strong></span>
                                        <span>📈 Chart: <strong style="color: ${doc.has_chart ? '#4caf50' : '#f44336'}">${doc.has_chart ? 'YES' : 'NO'}</strong></span>
                                        <span>🖼️ Image: <strong style="color: ${doc.has_image ? '#4caf50' : '#f44336'}">${doc.has_image ? 'YES' : 'NO'}</strong></span>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        cbrHtml += '<p style="color: #667799; text-align: center; padding: 20px;">💡 No similar documents found based on visual features.</p>';
                    }
                    
                    document.getElementById('cbrContent').innerHTML = cbrHtml;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('previewContent').innerHTML = `
                        <div class="preview-area">
                            <p style="color: #f44336; text-align: center;">❌ Error loading document</p>
                            <p style="font-size: 12px; color: #667799; text-align: center;">${error.message}</p>
                        </div>
                    `;
                    document.getElementById('cbrContent').innerHTML = `
                        <p style="color: #f44336; text-align: center;">❌ Error loading similar documents</p>
                        <p style="font-size: 12px; color: #667799; text-align: center;">Please make sure the database is properly set up.</p>
                    `;
                });
        }

        // Auto-load first document on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const searchKeyword = urlParams.get('search_keyword');
            
            if (!searchKeyword) {
                const firstDoc = document.querySelector('.document-item');
                if (firstDoc && firstDoc.getAttribute('onclick')) {
                    const onclickAttr = firstDoc.getAttribute('onclick');
                    const match = onclickAttr.match(/loadDocument\((\d+)\)/);
                    if (match && match[1]) {
                        loadDocument(match[1]);
                    }
                }
            }
        });
    </script>
</body>
</html>
