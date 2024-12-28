<?php
session_start();
require_once 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Lấy thông tin đơn hàng
$sql = "SELECT o.*, u.fullname, u.email, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = $order_id AND o.user_id = $user_id";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Lấy chi tiết đơn hàng
$sql = "SELECT od.*, p.name as product_name, pv.size, pv.color, pi.image_path
        FROM order_details od
        JOIN product_variants pv ON od.product_variant_id = pv.id
        JOIN products p ON pv.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE od.order_id = $order_id";
$result = mysqli_query($conn, $sql);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Chi tiết đơn hàng #<?php echo $order_id; ?></h2>
        <a href="orders.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Thông tin đơn hàng -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Thông tin đơn hàng</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Người nhận:</strong> <?php echo $order['fullname']; ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo $order['shipping_phone']; ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo $order['shipping_address']; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    <p><strong>Trạng thái:</strong> 
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
                    </p>
                    <p><strong>Phương thức thanh toán:</strong> <?php echo $order['payment_method']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chi tiết sản phẩm -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Sản phẩm đã đặt</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Size</th>
                            <th>Màu</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['image_path']): ?>
                                            <img src="assets/images/products/<?php echo $item['image_path']; ?>" 
                                                 alt="<?php echo $item['product_name']; ?>"
                                                 class="me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php endif; ?>
                                        <?php echo $item['product_name']; ?>
                                    </div>
                                </td>
                                <td><?php echo $item['size']; ?></td>
                                <td><?php echo $item['color']; ?></td>
                                <td><?php echo number_format($item['price']); ?>đ</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['price'] * $item['quantity']); ?>đ</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end"><strong>Tổng cộng:</strong></td>
                            <td><strong><?php echo number_format($order['total_amount']); ?>đ</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 