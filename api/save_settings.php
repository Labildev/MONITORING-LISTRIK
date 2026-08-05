<?php
session_start();
require_once '../koneksi.php';

header('Content-Type: application/json');

// Pastikan yang mengubah setting adalah admin
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
$names = $input['names'] ?? [];

if (!empty($names)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($names as $id => $name) {
            $key = "relay{$id}_name";
            $stmt->execute([$key, $name]);
        }
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan pengaturan']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Data tidak valid']);
}
?>
