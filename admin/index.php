<?php
session_start();
require_once '../config/database.php';

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Kiểm tra role admin
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

// Lấy danh sách sản phẩm
$sql = "SELECT p.*, c.name as category_name, pi.image_path 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
        ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="col-md-9 col-lg-10 content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý sản phẩm</h2>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Thêm sản phẩm
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Giá sale</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <?php if ($product['image_path']): ?>
                                        <img src="../assets/images/products/<?php echo $product['image_path']; ?>" 
                                             alt="<?php echo $product['name']; ?>"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $product['name']; ?></td>
                                <td><?php echo $product['category_name']; ?></td>
                                <td><?php echo number_format($product['price']); ?>đ</td>
                                <td>
                                    <?php echo $product['sale_price'] ? number_format($product['sale_price']) . 'đ' : '-'; ?>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 