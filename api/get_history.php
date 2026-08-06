<?php
require_once '../koneksi.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$isExport = isset($_GET['export']) && $_GET['export'] == 'true';

if ($page < 1) $page = 1;
if ($limit < 1) $limit = 20;

if ($isExport) {
    $limit = 50000; // Allow a large number for export
    $page = 1;
} else {
    if ($limit > 100) $limit = 100; // Cap at 100 per request
}

$offset = ($page - 1) * $limit;

// Construct WHERE clause if dates are provided
$whereClause = "";
$params = [];

if (!empty($startDate) && !empty($endDate)) {
    $whereClause = " WHERE DATE(timestamp) >= :start_date AND DATE(timestamp) <= :end_date";
    $params[':start_date'] = $startDate;
    $params[':end_date'] = $endDate;
} else if (!empty($startDate)) {
    $whereClause = " WHERE DATE(timestamp) >= :start_date";
    $params[':start_date'] = $startDate;
} else if (!empty($endDate)) {
    $whereClause = " WHERE DATE(timestamp) <= :end_date";
    $params[':end_date'] = $endDate;
}

try {
    // Get total rows for pagination info
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM energy_log" . $whereClause);
    foreach ($params as $key => $val) {
        $stmtTotal->bindValue($key, $val);
    }
    $stmtTotal->execute();
    $totalRows = $stmtTotal->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    // Get paginated data (newest first for history table)
    $stmt = $pdo->prepare("SELECT * FROM energy_log" . $whereClause . " ORDER BY id DESC LIMIT :limit OFFSET :offset");
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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
