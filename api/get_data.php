<?php
require_once '../koneksi.php';

header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

try {
    $stmt = $pdo->prepare("SELECT * FROM energy_log ORDER BY id DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetchAll();
    
    // Reverse the data so the oldest is first, typical for charts
    $data = array_reverse($data);
    
    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
