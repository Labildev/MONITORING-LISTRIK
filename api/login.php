<?php
session_start();
require_once '../koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Username dan password wajib diisi']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            
            echo json_encode(['success' => true, 'message' => 'Login berhasil']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Username atau password salah']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>
