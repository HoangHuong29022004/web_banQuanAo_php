<?php
session_start();
require_once '../config/database.php';

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT role FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user['role'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin sản phẩm
$sql = "SELECT p.*, pi.image_path 
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        WHERE p.id = $product_id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

// Lấy tất cả ảnh của sản phẩm
$sql = "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_main DESC";
$images = mysqli_query($conn, $sql);

// Lấy variants của sản phẩm
$sql = "SELECT * FROM product_variants WHERE product_id = $product_id";
$variants = mysqli_query($conn, $sql);

if (!$product) {
    header('Location: index.php');
    exit();
}

// Xử lý cập nhật sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $stock = (int)$_POST['stock'];

    // Cập nhật sản phẩm
    $sql = "UPDATE products 
            SET category_id = $category_id,
                name = '$name',
                description = '$description',
                price = $price,
                sale_price = " . ($sale_price ? $sale_price : "NULL") . ",
                stock = $stock
            WHERE id = $product_id";
    
    if (mysqli_query($conn, $sql)) {
        // Xử lý upload ảnh mới
        if (!empty($_FILES['images']['name'][0])) {
            // Xóa ảnh cũ
            $sql = "SELECT image_path FROM product_images WHERE product_id = $product_id";
            $old_images = mysqli_query($conn, $sql);
            while ($old_image = mysqli_fetch_assoc($old_images)) {
                unlink("../assets/images/products/" . $old_image['image_path']);
            }
            mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $product_id");

            // Thêm ảnh mới
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $image = $_FILES['images'];
                    $ext = pathinfo($image['name'][$key], PATHINFO_EXTENSION);
                    $image_name = 'sp' . time() . '_' . $key . '.' . $ext;
                    
                    if (move_uploaded_file($tmp_name, "../assets/images/products/" . $image_name)) {
                        $is_main = ($key == 0) ? 1 : 0;
                        $sql = "INSERT INTO product_images (product_id, image_path, is_main) 
                                VALUES ($product_id, '$image_name', $is_main)";
                        mysqli_query($conn, $sql);
                    }
                }
            }
        }

        // Cập nhật variants
        mysqli_query($conn, "DELETE FROM product_variants WHERE product_id = $product_id");
        if (isset($_POST['variants'])) {
            foreach ($_POST['variants'] as $variant) {
                if (!empty($variant['size']) && !empty($variant['color']) && !empty($variant['stock'])) {
                    $size = mysqli_real_escape_string($conn, $variant['size']);
                    $color = mysqli_real_escape_string($conn, $variant['color']);
                    $variant_stock = (int)$variant['stock'];
                    
                    $sql = "INSERT INTO product_variants (product_id, size, color, stock) 
                            VALUES ($product_id, '$size', '$color', $variant_stock)";
                    mysqli_query($conn, $sql);
                }
            }
        }

        header('Location: index.php');
        exit();
    }
}

// Lấy danh sách danh mục
$sql = "SELECT * FROM categories WHERE parent_id IS NOT NULL";
$categories = mysqli_query($conn, $sql);

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="col-md-9 col-lg-10 content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sửa sản phẩm</h2>
        <a href="index.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Tên sản phẩm</label>
                    <input type="text" class="form-control" name="name" 
                           value="<?php echo $product['name']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Danh mục</label>
                    <select class="form-select" name="category_id" required>
                        <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $category['id']; ?>"
                                    <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Giá</label>
                        <input type="number" class="form-control" name="price" 
                               value="<?php echo $product['price']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" class="form-control" name="sale_price"
                               value="<?php echo $product['sale_price']; ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Số lượng</label>
                    <input type="number" class="form-control" name="stock"
                           value="<?php echo $product['stock']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea class="form-control" name="description" rows="3"><?php echo $product['description']; ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ảnh hiện tại</label>
                    <div class="row">
                        <?php while ($image = mysqli_fetch_assoc($images)): ?>
                            <div class="col-md-2 mb-2">
                                <img src="../assets/images/products/<?php echo $image['image_path']; ?>" 
                                     class="img-thumbnail" alt="Product image">
                                <?php if ($image['is_main']): ?>
                                    <span class="badge bg-primary">Ảnh chính</span>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Thay đổi ảnh (có thể chọn nhiều ảnh)</label>
                    <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                    <small class="text-muted">Để trống nếu không muốn thay đổi ảnh. Ảnh đầu tiên sẽ là ảnh chính</small>
                </div>

                <!-- Variants -->
                <div class="mb-3">
                    <label class="form-label">Biến thể sản phẩm</label>
                    <div id="variants-container">
                        <?php 
                        $variant_count = 0;
                        while ($variant = mysqli_fetch_assoc($variants)): 
                        ?>
                            <div class="variant-item border rounded p-3 mb-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Size</label>
                                        <input type="text" class="form-control" 
                                               name="variants[<?php echo $variant_count; ?>][size]"
                                               value="<?php echo $variant['size']; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Màu sắc</label>
                                        <input type="text" class="form-control" 
                                               name="variants[<?php echo $variant_count; ?>][color]"
                                               value="<?php echo $variant['color']; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Số lượng</label>
                                        <input type="number" class="form-control" 
                                               name="variants[<?php echo $variant_count; ?>][stock]"
                                               value="<?php echo $variant['stock']; ?>">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm remove-variant">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            $variant_count++;
                        endwhile; 
                        ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-variant">
                        <i class="bi bi-plus"></i> Thêm biến thể
                    </button>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Cập nhật sản phẩm
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('add-variant').addEventListener('click', function() {
    const container = document.getElementById('variants-container');
    const variantCount = container.children.length;
    
    const variantHtml = `
        <div class="variant-item border rounded p-3 mb-2">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Size</label>
                    <input type="text" class="form-control" name="variants[${variantCount}][size]">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Màu sắc</label>
                    <input type="text" class="form-control" name="variants[${variantCount}][color]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Số lượng</label>
                    <input type="number" class="form-control" name="variants[${variantCount}][stock]">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-variant">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', variantHtml);
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-variant')) {
        e.target.closest('.variant-item').remove();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 