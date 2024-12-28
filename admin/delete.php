<?php
session_start();
require_once '../config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT role FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id) {
    // Xóa các ảnh sản phẩm từ thư mục
    $sql = "SELECT image_path FROM product_images WHERE product_id = $product_id";
    $result = mysqli_query($conn, $sql);
    while ($image = mysqli_fetch_assoc($result)) {
        if ($image['image_path']) {
            unlink("../assets/images/products/" . $image['image_path']);
        }
    }

    // Xóa sản phẩm từ database
    // Các bảng liên quan sẽ tự động xóa do có ràng buộc CASCADE
    $sql = "DELETE FROM products WHERE id = $product_id";
    mysqli_query($conn, $sql);
}

// Chuyển về trang danh sách
header('Location: index.php'); 