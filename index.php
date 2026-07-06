<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===============================
// GROUP HANDLING (FIXED ONLY)
// ===============================

// Ambil group dari URL sahaja (JANGAN guna folder auto detect)
$group = $_GET['group'] ?? 'GW05';

// clean input (jangan ubah format query DB)
$group = trim($group);
$group = preg_replace('/[^A-Za-z0-9]/', '', $group);

// OPTIONAL: debug kalau nak check value
// echo "DEBUG GROUP = " . $group; exit;

// ===============================
// DB CONNECTION
// ===============================
include '../../db_connection.php';

// ===============================
// QUERY (TIDAK DIUBAH LANGSUNG)
// ===============================
$members = [];

$sql = "SELECT S.full_name, S.matric_no FROM stu S 
        JOIN groupdb G ON S.group_no = G.groupID 
        WHERE G.groupID = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $group);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }

    $stmt->close();
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }

        .table-container {
            border: 1px solid #444;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(255,255,255,0.02);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 22px 30px;
            border-bottom: 1px solid #333;
            font-size: 1.3rem;
        }

        th {
            background: #161616;
            color: #00d2ff;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:last-child td { border-bottom: none; }

        tr:hover {
            background: rgba(255,255,255,0.04);
            transition: 0.2s;
        }

        .text-break {
            word-break: break-all;
            line-height: 1.5;
            font-size: 1.35rem;
            font-weight: 500;
        }

        .matrix-code {
            color: #00d2ff;
            font-weight: bold;
            font-family: monospace;
            font-size: 1.4rem;
        }

        .bil-col {
            font-size: 1.3rem;
            font-weight: bold;
        }

        .button-container {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }

        .btn-back, .btn-project {
            display: inline-block;
            padding: 14px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
            font-size: 1.1rem;
            text-align: center;
        }

        .btn-project {
            background: #00d2ff;
            color: #0f0f0f;
        }

        .btn-project:hover {
            background: #00b4db;
        }

        .btn-back {
            background: #333;
            color: white;
            border: 1px solid #444;
        }

        .btn-back:hover {
            background: #444;
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
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($members as $index => $row): ?>
                    <tr>
                        <td class="bil-col"><?php echo $index + 1; ?></td>
                        <td class="text-break" style="text-transform: uppercase;">
                            <?php echo htmlspecialchars($row['full_name']); ?>
                        </td>
                        <td class="matrix-code">
                            <?php echo htmlspecialchars($row['matric_no'] ?? '-'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="button-container">
    <a href="main.php?group=<?php echo urlencode($group); ?>" class="btn-project">
        🚀 GO TO YOUR PROJECT
    </a>

    <a href="../../main.php?group=<?php echo urlencode($group); ?>" class="btn-back">
        ⬅ BACK TO DASHBOARD
    </a>
</div>

</body>
</html>
