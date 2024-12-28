<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- hiển thị navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-shop me-2"></i>Fashion Shop
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Menu chính -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house-door me-1"></i>Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">
                        <i class="bi bi-grid me-1"></i>Sản phẩm
                    </a>
                </li>
            </ul>

            <!-- Tìm kiếm -->
            <form class="d-flex me-3" action="products.php" method="GET">
                <div class="input-group">
                    <input class="form-control" type="search" name="search" placeholder="Tìm kiếm..."
                        aria-label="Search">
                    <button class="btn btn-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <!-- Giỏ hàng -->
            <a href="cart.php" class="btn btn-outline-light position-relative me-3">
                <i class="bi bi-cart3"></i> Giỏ hàng
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php 
                    $cart_count = 0;
                    if (isset($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            $cart_count += $item['quantity'];
                        }
                    }
                    echo $cart_count;
                    ?>
                </span>
            </a>

            <!-- Menu tài khoản -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo $_SESSION['user_name']; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="orders.php">
                            <i class="bi bi-bag me-2"></i>Đơn hàng của tôi
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                        </a></li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="d-flex">
                    <a href="login.php" class="btn btn-outline-light me-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập
                    </a>
                    <a href="register.php" class="btn btn-light">
                        <i class="bi bi-person-plus me-1"></i>Đăng ký
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>