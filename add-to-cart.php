<?php
session_start();
require_once 'config/database.php';

// Nhận dữ liệu JSON từ request
$data = json_decode(file_get_contents('php://input'), true);
$product_variant_id = $data['variant_id'] ?? 0;
$quantity = $data['quantity'] ?? 1;

// Kiểm tra variant tồn tại và còn hàng
$sql = "SELECT pv.*, p.name, p.sale_price, p.price, pi.image_path as image
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE pv.id = $product_variant_id AND pv.stock >= $quantity";
$result = mysqli_query($conn, $sql);
$variant = mysqli_fetch_assoc($result);

if ($variant) {
    // Thêm vào session giỏ hàng
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Cập nhật số lượng nếu đã có trong giỏ
    if (isset($_SESSION['cart'][$product_variant_id])) {
        $_SESSION['cart'][$product_variant_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_variant_id] = [
            'name' => $variant['name'] . ' (' . $variant['size'] . ', ' . $variant['color'] . ')',
            'price' => $variant['sale_price'] ?? $variant['price'],
            'image' => $variant['image'],
            'quantity' => $quantity,
            'stock' => $variant['stock']
        ];
    }
}

// Tính tổng số sản phẩm trong giỏ hàng
$cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));

echo json_encode([
    'success' => true,
    'cart_count' => $cart_count
]); 