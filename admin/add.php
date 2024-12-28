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

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Xử lý thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $stock = (int)$_POST['stock'];

    // Thêm sản phẩm
    $sql = "INSERT INTO products (category_id, name, description, price, sale_price, stock) 
            VALUES ($category_id, '$name', '$description', $price, " . ($sale_price ? $sale_price : "NULL") . ", $stock)";
    
    if (mysqli_query($conn, $sql)) {
        $product_id = mysqli_insert_id($conn);

        // Xử lý upload ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = $_FILES['image'];
            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $image_name = 'sp' . time() . '.' . $ext;
            
            if (move_uploaded_file($image['tmp_name'], "../assets/images/products/" . $image_name)) {
                $sql = "INSERT INTO product_images (product_id, image_path, is_main) 
                        VALUES ($product_id, '$image_name', 1)";
                mysqli_query($conn, $sql);
            }
        }

        header('Location: index.php');
        exit();
    }
}

// Lấy danh sách danh mục
$sql = "SELECT * FROM categories";
$categories = mysqli_query($conn, $sql);
?>

<div class="col-md-9 col-lg-10 content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Thêm sản phẩm mới</h2>
        <a href="index.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Tên sản phẩm</label>
                    <input type="text" class="form-control" name="name" required>
                </div>

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

                <div class="mb-3">
                    <label class="form-label">Số lượng</label>
                    <input type="number" class="form-control" name="stock" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ảnh sản phẩm</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Thêm sản phẩm
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 