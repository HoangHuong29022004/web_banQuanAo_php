<?php
session_start();
require_once 'config/database.php';

// Xử lý tìm kiếm và lọc sản phẩm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Lấy danh sách danh mục cha
$sql = "SELECT * FROM categories WHERE parent_id IS NULL";
$parent_categories = mysqli_query($conn, $sql);

// Lấy danh sách tất cả danh mục con
$sql = "SELECT c.*, pc.name as parent_name 
        FROM categories c
        LEFT JOIN categories pc ON c.parent_id = pc.id
        WHERE c.parent_id IS NOT NULL
        ORDER BY pc.name, c.name";
$sub_categories = mysqli_query($conn, $sql);

// Query lấy sản phẩm với điều kiện lọc
$sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name, pi.image_path 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN categories pc ON c.parent_id = pc.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE 1=1";

// Thêm điều kiện tìm kiếm
if ($search) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (p.name LIKE '%$search%' OR c.name LIKE '%$search%')";
}

// Lọc theo danh mục
if ($category_id) {
    // Kiểm tra xem có phải danh mục cha không
    $check_sql = "SELECT parent_id FROM categories WHERE id = $category_id";
    $check_result = mysqli_query($conn, $check_sql);
    $category = mysqli_fetch_assoc($check_result);
    
    if ($category['parent_id'] === NULL) {
        // Nếu là danh mục cha, lấy tất cả sản phẩm thuộc danh mục con
        $sql .= " AND c.parent_id = $category_id";
    } else {
        // Nếu là danh mục con, chỉ lấy sản phẩm của danh mục đó
        $sql .= " AND p.category_id = $category_id";
    }
}

$sql .= " ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $sql);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar lọc danh mục -->
        <div class="col-md-3">
            <div class="category-sidebar">
                <h4 class="category-title mb-3">Danh mục sản phẩm</h4>
                <div class="category-list">
                    <a href="products.php" 
                       class="category-item <?php echo !$category_id ? 'active' : ''; ?>">
                        <i class="bi bi-grid-fill me-2"></i>
                        Tất cả sản phẩm
                    </a>

                    <?php while ($parent = mysqli_fetch_assoc($parent_categories)): ?>
                        <div class="category-group mb-2">
                            <a href="products.php?category=<?php echo $parent['id']; ?>"
                               class="category-parent <?php echo $category_id == $parent['id'] ? 'active' : ''; ?>">
                                <i class="bi bi-folder me-2"></i>
                                <?php echo $parent['name']; ?>
                            </a>
                            
                            <?php 
                            // Reset con trỏ về đầu
                            mysqli_data_seek($sub_categories, 0);
                            while ($sub = mysqli_fetch_assoc($sub_categories)):
                                if ($sub['parent_id'] == $parent['id']):
                            ?>
                                <a href="products.php?category=<?php echo $sub['id']; ?>"
                                   class="category-child <?php echo $category_id == $sub['id'] ? 'active' : ''; ?>">
                                    <i class="bi bi-dash me-2"></i>
                                    <?php echo $sub['name']; ?>
                                </a>
                            <?php 
                                endif;
                            endwhile; 
                            ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="col-md-9">
            <!-- Phần header -->
            <div class="products-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <?php
                        if ($search) {
                            echo 'Kết quả tìm kiếm: "' . htmlspecialchars($search) . '"';
                        } elseif ($category_id) {
                            $current_category = mysqli_fetch_assoc(mysqli_query($conn, 
                                "SELECT c.*, pc.name as parent_name 
                                 FROM categories c
                                 LEFT JOIN categories pc ON c.parent_id = pc.id
                                 WHERE c.id = $category_id"));
                            echo $current_category['parent_id'] 
                                ? $current_category['parent_name'] . ' - ' . $current_category['name']
                                : $current_category['name'];
                        } else {
                            echo 'Tất cả sản phẩm';
                        }
                        ?>
                    </h2>
                </div>
            </div>

            <!-- Grid sản phẩm -->
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="products-grid">
                    <?php while ($product = mysqli_fetch_assoc($result)): ?>
                        <div class="product-card fade-in">
                            <div class="product-image-wrapper">
                                <?php if ($product['image_path']): ?>
                                    <img src="assets/images/products/<?php echo $product['image_path']; ?>" 
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
                                <div class="product-category">
                                    <?php echo $product['parent_category_name'] . ' - ' . $product['category_name']; ?>
                                </div>
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
                    <?php endwhile; ?>
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