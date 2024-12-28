<?php
session_start();
require_once 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng
$sql = "SELECT o.*, 
               COUNT(od.id) as total_items,
               (SELECT image_path 
                FROM product_images pi 
                JOIN product_variants pv ON pv.product_id = pi.product_id
                JOIN order_details od2 ON od2.product_variant_id = pv.id
                WHERE od2.order_id = o.id AND pi.is_main = 1
                LIMIT 1) as first_product_image
        FROM orders o
        LEFT JOIN order_details od ON o.id = od.order_id
        WHERE o.user_id = $user_id
        GROUP BY o.id
        ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $sql);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Đơn hàng của tôi</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row">
            <?php while ($order = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Đơn hàng #<?php echo $order['id']; ?></h5>
                                <span class="badge bg-<?php 
                                    echo match($order['status']) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'shipping' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php 
                                    echo match($order['status']) {
                                        'pending' => 'Chờ xác nhận',
                                        'processing' => 'Đang xử lý',
                                        'shipping' => 'Đang giao',
                                        'completed' => 'Đã giao',
                                        'cancelled' => 'Đã hủy',
                                        default => $order['status']
                                    };
                                    ?>
                                </span>
                            </div>

                            <div class="row g-0">
                                <?php if ($order['first_product_image']): ?>
                                    <div class="col-4">
                                        <img src="assets/images/products/<?php echo $order['first_product_image']; ?>" 
                                             class="img-fluid rounded" 
                                             alt="Product image">
                                    </div>
                                <?php endif; ?>
                                <div class="col">
                                    <div class="card-body">
                                        <p class="card-text">
                                            <small class="text-muted">Ngày đặt: 
                                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                            </small>
                                        </p>
                                        <p class="card-text">
                                            <strong>Tổng tiền:</strong> 
                                            <?php echo number_format($order['total_amount']); ?>đ
                                        </p>
                                        <p class="card-text">
                                            <strong>Số sản phẩm:</strong> 
                                            <?php echo $order['total_items']; ?>
                                        </p>
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Bạn chưa có đơn hàng nào.
            <a href="products.php" class="alert-link">Mua sắm ngay</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?> 