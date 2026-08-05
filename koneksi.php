<?php
$host = "localhost";
$user = "root"; // Sesuaikan dengan user MySQL Anda di hosting (misal: u123456_volt)
$pass = "";     // Sesuaikan dengan password MySQL Anda
$db   = "izazmonitor"; // Sesuaikan dengan nama database Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Setting default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
