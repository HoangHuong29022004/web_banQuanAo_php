<?php
require_once 'config/database.php';

// Xử lý tìm kiếm và lọc sản phẩm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_id = isset($_GET['category']) ? $_GET['category'] : '';

// Lấy danh sách danh mục
$sql = "SELECT * FROM categories WHERE parent_id IS NULL";
$result = mysqli_query($conn, $sql);
$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

// Query lấy sản phẩm
$sql = "SELECT p.*, c.name as category_name, pi.image_path as image
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE 1=1";

// Thêm điều kiện tìm kiếm
if ($search) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND p.name LIKE '%$search%'";
}

if ($category_id) {
    $category_id = (int)$category_id;
    $sql .= " AND (p.category_id = $category_id OR c.parent_id = $category_id)";
}

$sql .= " ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $sql);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar lọc danh mục -->
        <div class="col-md-3">
            <div class="category-sidebar">
                <h4 class="category-title mb-3">Danh mục</h4>
                <div class="category-list">
                    <a href="products.php" 
                       class="category-item <?php echo !$category_id ? 'active' : ''; ?>">
                        <i class="bi bi-grid-fill me-2"></i>
                        Tất cả sản phẩm
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="products.php?category=<?php echo $category['id']; ?>"
                           class="category-item <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                            <i class="bi bi-folder me-2"></i>
                            <?php echo $category['name']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="col-md-9">
            <!-- Phần header -->
            <div class="products-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Sản phẩm</h2>
                    <?php if ($search): ?>
                        <p class="mb-0">
                            Kết quả tìm kiếm cho: "<?php echo htmlspecialchars($search); ?>"
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Grid sản phẩm -->
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
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
                                <?php if ($product['sale_price']): ?>
                                    <div class="product-badge">Sale</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-category"><?php echo $product['category_name']; ?></div>
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
            <?php else: ?>
                <div class="alert alert-info">
                    Không tìm thấy sản phẩm nào.
                    <a href="products.php" class="alert-link">Xem tất cả sản phẩm</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>