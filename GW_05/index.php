<?php
session_start();

// 1. Dapatkan nama group daripada URL parameter atau nama folder semasa
if (!isset($_GET['group'])) {
    $group = basename(dirname(__FILE__));
} else {
    $group = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['group']);
}

// 2. Try multiple paths for database connection
$db_paths = [
    'config/db_connection.php',
    '../config/db_connection.php',
    '../../config/db_connection.php',
    'db.php',
    '../db.php'
];

$db_found = false;
foreach ($db_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $db_found = true;
        break;
    }
}

if (!$db_found) {
    // Direct connection if no db file found
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gw05_md";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

// 3. Check connection
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed. Please check your configuration.");
}

// 4. Get members from document table
$members = [];
$sql = "SELECT DISTINCT author as full_name FROM document WHERE author IS NOT NULL AND author != '' ORDER BY author";

if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $matric_no = 'A' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        
        $members[] = [
            'full_name' => $row['full_name'],
            'matric_no' => $matric_no
        ];
    }
    $stmt->close();
} else {
    // Try metadata table if document fails
    $sql = "SELECT DISTINCT author as full_name FROM metadata WHERE author IS NOT NULL AND author != '' ORDER BY author";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $matric_no = 'A' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
            
            $members[] = [
                'full_name' => $row['full_name'],
                'matric_no' => $matric_no
            ];
        }
        $stmt->close();
    }
}

$conn->close(); 
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Senarai Ahli Kumpulan | <?php echo htmlspecialchars($group); ?></title>
    <style>
        body { background: #0f0f0f; color: white; font-family: sans-serif; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 40px; }
        
        /* Gaya reka bentuk jadual (Table Styling) */
        .table-container { border: 1px solid #444; border-radius: 12px; overflow: hidden; background: rgba(255,255,255,0.02); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        
        /* FONT SIZE BESAR: Saiz padding dan font th & td dibesarkan */
        th, td { padding: 22px 30px; border-bottom: 1px solid #333; font-size: 1.3rem; }
        th { background: #161616; color: #00d2ff; font-size: 1.2rem; text-transform: uppercase; letter-spacing: 1px; }
        
        tr:last-child td { border-bottom: none; }
        tr:hover { background: rgba(255,255,255,0.04); transition: 0.2s; }
        
        /* Memastikan jika ada data XXXXX yang panjang, ia tidak melimpah keluar */
        .text-break { word-break: break-all; line-height: 1.5; font-size: 1.35rem; font-weight: 500; }
        .matrix-code { color: #00d2ff; font-weight: bold; font-family: monospace; font-size: 1.4rem; }
        .bil-col { font-size: 1.3rem; font-weight: bold; }
        
        /* Button container for both buttons */
        .button-container {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn-back, .btn-project {
            display: inline-block;
            padding: 14px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
        }
        
        .btn-back {
            background: #555;
            color: white;
        }
        .btn-back:hover {
            background: #666;
            transform: translateY(-2px);
        }
        
        .btn-project {
            background: #00d2ff;
            color: #0f0f0f;
        }
        .btn-project:hover {
            background: #00e5ff;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 210, 255, 0.3);
        }
        
        .total-members {
            text-align: right;
            margin-top: 15px;
            color: #888;
            font-size: 1rem;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>SENARAI AHLI KUMPULAN</h1>
    <div style="border: 1px solid #00d2ff; padding: 10px 25px; font-size: 1.6rem; border-radius: 5px; font-weight: bold;">
        GROUP: <?php echo htmlspecialchars($group); ?>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th style="width: 100px;">BIL</th>
                <th>NAMA PENUH</th>
                <th style="width: 350px;">NO. MATRIK</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($members)): ?>
                <tr>
                    <td colspan="3" style="text-align: center; color: #ff4444; padding: 40px; font-size: 1.4rem;">
                        Tiada data ahli kumpulan ditemui untuk kod group "<?php echo htmlspecialchars($group); ?>".
                        <br><small style="color: #666;">Sila pastikan terdapat data dalam table document atau metadata.</small>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($members as $index => $row): ?>
                    <tr>
                        <td class="bil-col"><?php echo $index + 1; ?></td>
                        <td class="text-break" style="text-transform: uppercase;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td class="matrix-code"><?php echo htmlspecialchars($row['matric_no'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($members)): ?>
<div class="total-members">
    Jumlah Ahli: <?php echo count($members); ?> orang
</div>
<?php endif; ?>

<!-- Button Container with both buttons -->
<div class="button-container">
    <a href="main.php?group=<?php echo urlencode($group); ?>" class="btn-project">🚀 GO TO YOUR PROJECT</a>
    <a href="../../dashboard.php?group=<?php echo urlencode($group); ?>" class="btn-back">⬅ BACK TO DASHBOARD</a>
</div>

</body>
</html>