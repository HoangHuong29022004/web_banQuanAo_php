<?php
session_start();
require_once 'config/database.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Kiểm tra mật khẩu xác nhận
    if ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Kiểm tra email đã tồn tại chưa
        $sql = "SELECT id FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'Email này đã được sử dụng!';
        } else {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Thêm user mới
            $sql = "INSERT INTO users (fullname, email, password, phone, address, role) 
                    VALUES ('$fullname', '$email', '$hashed_password', '$phone', '$address', 'user')";
            
            if (mysqli_query($conn, $sql)) {
                $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Đăng ký tài khoản</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                            <a href="login.php">Đăng nhập ngay</a>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fullname" class="form-label">Họ tên</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Đăng ký</button>
                    </form>

                    <hr>
                    <p class="mb-0">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 