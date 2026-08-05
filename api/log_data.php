<?php
require_once '../koneksi.php';

// Mendukung raw JSON atau Form Data
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if ($input) {
    $_POST = $input;
}

$api_key = isset($_GET['api_key']) ? $_GET['api_key'] : '';
if ($api_key !== 'labil_secret_123') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized API Key']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tegangan = isset($_POST['tegangan']) ? (float)$_POST['tegangan'] : 0;
    $arus = isset($_POST['arus']) ? (float)$_POST['arus'] : 0;
    $daya = isset($_POST['daya']) ? (float)$_POST['daya'] : 0;
    $energi = isset($_POST['energi']) ? (float)$_POST['energi'] : 0;
    $relay1 = isset($_POST['relay1']) ? (int)$_POST['relay1'] : 0;
    $relay2 = isset($_POST['relay2']) ? (int)$_POST['relay2'] : 0;
    $relay3 = isset($_POST['relay3']) ? (int)$_POST['relay3'] : 0;
    $relay4 = isset($_POST['relay4']) ? (int)$_POST['relay4'] : 0;

    $stmt = $pdo->prepare("INSERT INTO energy_log (tegangan, arus, daya, energi, relay1, relay2, relay3, relay4) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$tegangan, $arus, $daya, $energi, $relay1, $relay2, $relay3, $relay4])) {
        echo json_encode(['status' => 'success', 'message' => 'Data logged successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to log data']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>
