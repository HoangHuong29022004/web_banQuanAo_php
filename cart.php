<?php
session_start();
require_once 'config/database.php';

// Xử lý cập nhật số lượng sản phẩm trong giỏ
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $variant_id => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$variant_id]['quantity'] = min($quantity, $_SESSION['cart'][$variant_id]['stock']);
        } else {
            unset($_SESSION['cart'][$variant_id]);
        }
    }
}

// Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['remove'])) {
    $variant_id = $_GET['remove'];
    unset($_SESSION['cart'][$variant_id]);
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <div class="cart-wrapper">
        <div class="cart-header mb-4">
            <h1 class="cart-title">Giỏ hàng của bạn</h1>
            <p class="cart-count">
                <?php 
                $total_items = 0;
                if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $total_items += $item['quantity'];
                    }
                }
                echo $total_items . ' sản phẩm';
                ?>
            </p>
        </div>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart text-center py-5">
                <i class="bi bi-cart-x empty-cart-icon"></i>
                <h3 class="mt-3">Giỏ hàng trống</h3>
                <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng của bạn</p>
                <a href="products.php" class="btn btn-primary mt-3">
                    <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
                </a>
            </div>
        <?php else: ?>
            <form method="post">
                <div class="row">
                    <!-- Danh sách sản phẩm -->
                    <div class="col-lg-8">
                        <div class="cart-items">
                            <?php 
                            $total = 0;
                            foreach ($_SESSION['cart'] as $variant_id => $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                            ?>
                                <div class="cart-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="assets/images/products/<?php echo $item['image']; ?>" 
                                                 class="cart-item-image" 
                                                 alt="<?php echo $item['name']; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <h5 class="cart-item-title"><?php echo $item['name']; ?></h5>
                                            <p class="cart-item-price">
                                                <?php echo number_format($item['price']); ?>đ
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="quantity-input">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="updateQuantity(this, -1)">-</button>
                                                <input type="number" 
                                                       name="quantity[<?php echo $variant_id; ?>]" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="0"
                                                       max="<?php echo $item['stock']; ?>"
                                                       class="form-control">
                                                <button type="button" class="btn btn-outline-secondary"
                                                        onclick="updateQuantity(this, 1)">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <p class="cart-item-subtotal">
                                                <?php echo number_format($subtotal); ?>đ
                                            </p>
                                        </div>
                                        <div class="col-md-1">
                                            <a href="cart.php?remove=<?php echo $variant_id; ?>" 
                                               class="btn btn-remove"
                                               onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tổng tiền và thanh toán -->
                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h4 class="summary-title mb-4">Tổng giỏ hàng</h4>
                            
                            <div class="summary-item">
                                <span>Tạm tính:</span>
                                <span><?php echo number_format($total); ?>đ</span>
                            </div>
                            
                            <div class="summary-item">
                                <span>Phí vận chuyển:</span>
                                <span>Miễn phí</span>
                            </div>
                            
                            <hr>
                            
                            <div class="summary-total">
                                <span>Tổng cộng:</span>
                                <span class="total-amount"><?php echo number_format($total); ?>đ</span>
                            </div>

                            <div class="summary-buttons mt-4">
                                <button type="submit" name="update_cart" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Cập nhật giỏ hàng
                                </button>
                                
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="checkout.php" class="btn btn-primary w-100">
                                        Thanh toán <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary w-100" 
                                       onclick="alert('Vui lòng đăng nhập để thanh toán!')">
                                        Thanh toán <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function updateQuantity(button, change) {
    const input = button.parentElement.querySelector('input');
    const currentValue = parseInt(input.value);
    const maxValue = parseInt(input.max);
    const newValue = currentValue + change;
    
    if (newValue >= 0 && newValue <= maxValue) {
        input.value = newValue;
    }
}
</script>

<?php include 'includes/footer.php'; ?> 