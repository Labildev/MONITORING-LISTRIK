<?php
require_once '../koneksi.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Format response untuk nama relay: [1 => "Name", 2 => "Name", ...]
    $relayNames = [
        1 => $settings['relay1_name'] ?? 'Beban 1',
        2 => $settings['relay2_name'] ?? 'Beban 2',
        3 => $settings['relay3_name'] ?? 'Beban 3',
        4 => $settings['relay4_name'] ?? 'Beban 4',
    ];
    
    echo json_encode($relayNames);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil pengaturan']);
}
?>
