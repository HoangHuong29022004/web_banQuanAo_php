<?php
session_start();
require_once 'config/database.php';

// Kiểm tra giỏ hàng có trống không
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php');
    exit();
}

// Lấy thông tin user
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        mysqli_begin_transaction($conn);

        // Cập nhật thông tin giao hàng
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $note = mysqli_real_escape_string($conn, $_POST['note'] ?? '');

        // Thêm đơn hàng
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        $sql = "INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, shipping_phone, note)
                VALUES ($user_id, $total_amount, 'COD', '$address', '$phone', '$note')";
        mysqli_query($conn, $sql);
        $order_id = mysqli_insert_id($conn);

        // Thêm chi tiết đơn hàng
        foreach ($_SESSION['cart'] as $variant_id => $item) {
            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];
            
            $sql = "INSERT INTO order_details (order_id, product_variant_id, quantity, price)
                    VALUES ($order_id, $variant_id, $quantity, $price)";
            mysqli_query($conn, $sql);

            // Cập nhật số lượng tồn
            $sql = "UPDATE product_variants 
                    SET stock = stock - $quantity 
                    WHERE id = $variant_id";
            mysqli_query($conn, $sql);
        }

        mysqli_commit($conn);
        unset($_SESSION['cart']);
        
        header('Location: thank-you.php?order_id=' . $order_id);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Có lỗi xảy ra, vui lòng thử lại!";
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <h1>Thanh toán</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Form thông tin giao hàng -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Họ tên</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" 
                                   value="<?php echo $user['fullname']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo $user['phone']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ giao hàng</label>
                            <textarea class="form-control" id="address" name="address" 
                                     rows="3" required><?php echo $user['address']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Đặt hàng</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Thông tin đơn hàng -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Đơn hàng của bạn</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['cart'] as $variant_id => $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <img src="assets/images/products/<?php echo $item['image']; ?>" 
                                 alt="<?php echo $item['name']; ?>"
                                 class="me-2" style="width: 50px; height: 50px; object-fit: cover;">
                            <span><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></span>
                        </div>
                        <div><?php echo number_format($subtotal); ?>đ</div>
                    </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Tổng cộng:</strong>
                        <strong><?php echo number_format($total); ?>đ</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 