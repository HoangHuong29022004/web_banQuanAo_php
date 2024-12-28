<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_fashion';

// Kết nối MySQL
$conn = mysqli_connect($host, $username, $password, $database);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Set charset utf8
mysqli_set_charset($conn, "utf8mb4");
?> 