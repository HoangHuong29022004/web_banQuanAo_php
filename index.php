<?php
// Kết nối đến cơ sở dữ liệu
require_once 'config/database.php';

// Truy vấn 8 sản phẩm mới nhất kèm theo ảnh chính
$sql = "SELECT p.*, pi.image_path as image 
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        ORDER BY p.created_at DESC 
        LIMIT 8";
$result = mysqli_query($conn, $sql);
$latest_products = [];
while($row = mysqli_fetch_assoc($result)) {
    $latest_products[] = $row;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <!-- Carousel/Slideshow -->
    <div id="carouselMain" class="carousel slide mb-4" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#carouselMain" data-bs-slide-to="1"></button>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/images/banners/banner_01.jpg" class="d-block w-100" alt="Fashion Banner 1">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Bộ sưu tập mới 2024</h5>
                    <p>Khám phá các xu hướng thời trang mới nhất</p>
                    <a href="products.php" class="btn btn-light">Xem thêm</a>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/banners/banner_01.jpg" class="d-block w-100" alt="Fashion Banner 2">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Thời trang công sở</h5>
                    <p>Phong cách thanh lịch và chuyên nghiệp</p>
                    <a href="products.php" class="btn btn-light">Xem thêm</a>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carouselMain" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselMain" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <h2 class="mb-4">Sản phẩm mới</h2>
    <div class="products-grid">
        <?php foreach ($latest_products as $product): ?>
            <div class="product-card fade-in">
                <div class="product-image-wrapper">
                    <?php if ($product['image']): ?>
                        <img src="assets/images/products/<?php echo $product['image']; ?>" 
                             class="product-image" 
                             alt="<?php echo $product['name']; ?>">
                    <?php else: ?>
                        <div class="product-image no-image">
                            <i class="bi bi-image"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <h3 class="product-title"><?php echo $product['name']; ?></h3>
                    
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="original-price">
                                <?php echo number_format($product['price']); ?>đ
                            </span>
                            <span class="sale-price">
                                <?php echo number_format($product['sale_price']); ?>đ
                            </span>
                        <?php else: ?>
                            <span class="sale-price">
                                <?php echo number_format($product['price']); ?>đ
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="product-buttons">
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Chi tiết
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>