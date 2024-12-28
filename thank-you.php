<?php
session_start();
require_once 'config/database.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Lấy thông tin đơn hàng
$sql = "SELECT o.*, u.fullname, u.email, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = $order_id";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header('Location: index.php');
    exit();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <div class="text-center">
        <h1 class="mb-4">Cảm ơn bạn đã đặt hàng!</h1>
        <p>Mã đơn hàng của bạn là: <strong>#<?php echo $order_id; ?></strong></p>
        <p>Chúng tôi sẽ liên hệ với bạn qua số điện thoại <?php echo $order['phone']; ?> để xác nhận đơn hàng.</p>
        
        <!-- Hiển thị thông tin đơn hàng -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Thông tin đơn hàng</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 text-start">
                        <p><strong>Người nhận:</strong> <?php echo $order['fullname']; ?></p>
                        <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
                        <p><strong>Số điện thoại:</strong> <?php echo $order['phone']; ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo $order['shipping_address']; ?></p>
                    </div>
                    <div class="col-md-6 text-start">
                        <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                        <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total_amount']); ?>đ</p>
                        <p><strong>Phương thức thanh toán:</strong> <?php echo $order['payment_method']; ?></p>
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
                                    'shipping' => 'Đang giao hàng',
                                    'completed' => 'Đã giao hàng',
                                    'cancelled' => 'Đã hủy',
                                    default => $order['status']
                                };
                                ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chi tiết đơn hàng -->
        <?php
        $sql = "SELECT od.*, pv.size, pv.color, p.name as product_name
                FROM order_details od
                JOIN product_variants pv ON od.product_variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                WHERE od.order_id = $order_id";
        $result = mysqli_query($conn, $sql);
        ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Chi tiết đơn hàng</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Size</th>
                                <th>Màu</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($detail = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $detail['product_name']; ?></td>
                                    <td><?php echo $detail['size']; ?></td>
                                    <td><?php echo $detail['color']; ?></td>
                                    <td><?php echo $detail['quantity']; ?></td>
                                    <td><?php echo number_format($detail['price']); ?>đ</td>
                                    <td><?php echo number_format($detail['price'] * $detail['quantity']); ?>đ</td>
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

        <a href="index.php" class="btn btn-primary mt-4">Tiếp tục mua sắm</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 