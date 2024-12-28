<?php
session_start();
require_once '../config/database.php';

// Kiểm tra quyền admin
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

// Xử lý thêm sản phẩm khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy và làm sạch dữ liệu từ form
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $stock = (int)$_POST['stock'];

    // Thêm sản phẩm vào database
    $sql = "INSERT INTO products (category_id, name, description, price, sale_price, stock) 
            VALUES ($category_id, '$name', '$description', $price, " . ($sale_price ? $sale_price : "NULL") . ", $stock)";
    
    if (mysqli_query($conn, $sql)) {
        $product_id = mysqli_insert_id($conn);

        // Xử lý upload nhiều ảnh
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $image = $_FILES['images'];
                    $ext = pathinfo($image['name'][$key], PATHINFO_EXTENSION);
                    $image_name = 'sp' . time() . '_' . $key . '.' . $ext;
                    
                    // Upload ảnh và lưu thông tin vào database
                    if (move_uploaded_file($tmp_name, "../assets/images/products/" . $image_name)) {
                        $is_main = ($key == 0) ? 1 : 0; // Ảnh đầu tiên là ảnh chính
                        $sql = "INSERT INTO product_images (product_id, image_path, is_main) 
                                VALUES ($product_id, '$image_name', $is_main)";
                        mysqli_query($conn, $sql);
                    }
                }
            }
        }

        // Thêm các biến thể sản phẩm
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

// Lấy danh sách danh mục cho dropdown
$sql = "SELECT * FROM categories WHERE parent_id IS NOT NULL";
$categories = mysqli_query($conn, $sql);

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!-- Form thêm sản phẩm -->
<div class="col-md-9 col-lg-10 content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Thêm sản phẩm mới</h2>
        <a href="index.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Form -->
    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <!-- Các trường thông tin cơ bản -->
                <div class="mb-3">
                    <label class="form-label">Tên sản phẩm</label>
                    <input type="text" class="form-control" name="name" required>
                </div>

                <!-- Dropdown danh mục -->
                <div class="mb-3">
                    <label class="form-label">Danh mục</label>
                    <select class="form-select" name="category_id" required>
                        <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Giá và giá khuyến mãi -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Giá</label>
                        <input type="number" class="form-control" name="price" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" class="form-control" name="sale_price">
                    </div>
                </div>

                <!-- Upload nhiều ảnh -->
                <div class="mb-3">
                    <label class="form-label">Ảnh sản phẩm (có thể chọn nhiều ảnh)</label>
                    <input type="file" class="form-control" name="images[]" accept="image/*" multiple required>
                    <small class="text-muted">Ảnh đầu tiên sẽ là ảnh chính</small>
                </div>

                <!-- Phần variants -->
                <div class="mb-3">
                    <label class="form-label">Biến thể sản phẩm</label>
                    <div id="variants-container">
                        <!-- Template cho variant -->
                        <div class="variant-item border rounded p-3 mb-2">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Size</label>
                                    <input type="text" class="form-control" name="variants[0][size]">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Màu sắc</label>
                                    <input type="text" class="form-control" name="variants[0][color]">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Số lượng</label>
                                    <input type="number" class="form-control" name="variants[0][stock]">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-sm remove-variant">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-variant">
                        <i class="bi bi-plus"></i> Thêm biến thể
                    </button>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Thêm sản phẩm
                </button>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript xử lý thêm/xóa variant -->
<script>
// Thêm variant mới
document.getElementById('add-variant').addEventListener('click', function() {
    const container = document.getElementById('variants-container');
    const variantCount = container.children.length;
    
    // Template HTML cho variant mới
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
    
    // Thêm variant vào container
    container.insertAdjacentHTML('beforeend', variantHtml);
});

// Xóa variant
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-variant')) {
        e.target.closest('.variant-item').remove();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?> 