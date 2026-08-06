<?php
require_once '../koneksi.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

if ($page < 1) $page = 1;
if ($limit < 1) $limit = 20;
if ($limit > 100) $limit = 100; // Cap at 100 per request

$offset = ($page - 1) * $limit;

try {
    // Get total rows for pagination info
    $stmtTotal = $pdo->query("SELECT COUNT(*) FROM energy_log");
    $totalRows = $stmtTotal->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    // Get paginated data (newest first for history table)
    $stmt = $pdo->prepare("SELECT * FROM energy_log ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'limit' => $limit,
            'total_rows' => $totalRows,
            'total_pages' => $totalPages
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
