<?php
require_once 'config/database.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin sản phẩm
$sql = "SELECT p.*, c.name as category_name, pi.image_path as main_image
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE p.id = $product_id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header('Location: products.php');
    exit();
}

// Lấy tất cả ảnh của sản phẩm
$sql = "SELECT image_path FROM product_images 
        WHERE product_id = $product_id
        ORDER BY is_main DESC";
$result = mysqli_query($conn, $sql);
$product_images = [];
while ($row = mysqli_fetch_assoc($result)) {
    $product_images[] = $row['image_path'];
}

// Lấy các variants của sản phẩm
$sql = "SELECT * FROM product_variants 
        WHERE product_id = $product_id
        ORDER BY size, color";
$result = mysqli_query($conn, $sql);
$variants = [];
while ($row = mysqli_fetch_assoc($result)) {
    $variants[] = $row;
}

// Lấy các size và màu sắc riêng biệt
$sizes = array_unique(array_column($variants, 'size'));
$colors = array_unique(array_column($variants, 'color'));

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="index.php">
                    <i class="bi bi-house-door me-1"></i>Trang chủ
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="products.php">
                    <i class="bi bi-grid me-1"></i>Sản phẩm
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="products.php?category=<?php echo $product['category_id']; ?>">
                    <?php echo $product['category_name']; ?>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo $product['name']; ?>
            </li>
        </ol>
    </nav>

    <div class="product-detail-wrapper">
        <div class="row">
            <!-- Gallery ảnh -->
            <div class="col-md-6 mb-4">
                <div class="product-gallery">
                    <div class="main-image-wrapper">
                        <img src="assets/images/products/<?php echo $product['main_image']; ?>" 
                             class="main-image" 
                             id="mainImage"
                             alt="<?php echo $product['name']; ?>">
                        <?php if ($product['sale_price']): ?>
                            <div class="product-badge">Sale</div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($product_images) > 1): ?>
                        <div class="thumbnail-list">
                            <?php foreach ($product_images as $image): ?>
                                <div class="thumbnail-item">
                                    <img src="assets/images/products/<?php echo $image; ?>" 
                                         class="thumbnail" 
                                         onclick="changeMainImage(this.src)"
                                         alt="<?php echo $product['name']; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thông tin sản phẩm -->
            <div class="col-md-6">
                <div class="product-info-wrapper">
                    <div class="product-category mb-2">
                        <a href="products.php?category=<?php echo $product['category_id']; ?>">
                            <?php echo $product['category_name']; ?>
                        </a>
                    </div>

                    <h1 class="product-title mb-3"><?php echo $product['name']; ?></h1>

                    <div class="product-price mb-4">
                        <?php if ($product['sale_price']): ?>
                            <span class="original-price">
                                <?php echo number_format($product['price']); ?>đ
                            </span>
                            <span class="sale-price">
                                <?php echo number_format($product['sale_price']); ?>đ
                            </span>
                            <span class="discount-badge">
                                -<?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>%
                            </span>
                        <?php else: ?>
                            <span class="regular-price">
                                <?php echo number_format($product['price']); ?>đ
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Form chọn variant và thêm vào giỏ -->
                    <form id="add-to-cart-form" class="mb-4">
                        <input type="hidden" id="variants-data" value="<?php echo htmlspecialchars(json_encode($variants)); ?>">
                        
                        <!-- Chọn size -->
                        <div class="form-group mb-3">
                            <label class="form-label">Kích thước:</label>
                            <div class="size-options">
                                <?php foreach ($sizes as $size): ?>
                                    <input type="radio" class="btn-check" name="size" 
                                           id="size_<?php echo $size; ?>" value="<?php echo $size; ?>" required>
                                    <label class="btn btn-outline-primary size-btn" 
                                           for="size_<?php echo $size; ?>"><?php echo $size; ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Chọn màu -->
                        <div class="form-group mb-3">
                            <label class="form-label">Màu sắc:</label>
                            <div class="color-options">
                                <?php foreach ($colors as $color): ?>
                                    <input type="radio" class="btn-check" name="color" 
                                           id="color_<?php echo $color; ?>" value="<?php echo $color; ?>" required>
                                    <label class="btn btn-outline-primary color-btn" 
                                           for="color_<?php echo $color; ?>"><?php echo $color; ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Số lượng -->
                        <div class="form-group mb-4">
                            <label class="form-label">Số lượng:</label>
                            <div class="quantity-input">
                                <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(this, -1)">-</button>
                                <input type="number" name="quantity" value="1" min="1" class="form-control" id="quantityInput">
                                <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(this, 1)">+</button>
                            </div>
                            <small id="stock-info" class="text-muted"></small>
                        </div>

                        <!-- Nút thêm vào giỏ -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="bi bi-cart-plus me-2"></i>Thêm vào giỏ hàng
                        </button>
                    </form>

                    <!-- Mô tả sản phẩm -->
                    <div class="product-description">
                        <h4 class="mb-3">Mô tả sản phẩm</h4>
                        <div class="description-content">
                            <?php echo nl2br($product['description']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast thông báo -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="addToCartToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="bi bi-check-circle text-success me-2"></i>
            <strong class="me-auto">Thành công</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Đã thêm sản phẩm vào giỏ hàng
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 