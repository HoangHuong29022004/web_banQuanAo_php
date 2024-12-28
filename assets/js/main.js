// Hàm cập nhật số lượng giỏ hàng trên navbar
function updateCartCount(count) {
    const badge = document.querySelector('.badge');
    if (badge) {
        badge.textContent = count;
    }
}

// Hàm cập nhật thông tin tồn kho khi chọn size và màu
function updateStockInfo() {
    const size = document.querySelector('input[name="size"]:checked')?.value;
    const color = document.querySelector('input[name="color"]:checked')?.value;
    const stockInfo = document.getElementById('stock-info');
    const quantityInput = document.querySelector('input[name="quantity"]');
    
    if (size && color) {
        const variants = JSON.parse(document.getElementById('variants-data').value);
        const variant = variants.find(v => v.size === size && v.color === color);
        
        if (variant) {
            stockInfo.textContent = `Còn ${variant.stock} sản phẩm`;
            quantityInput.max = variant.stock;
            quantityInput.value = Math.min(quantityInput.value, variant.stock);
        }
    }
}

// Thêm sự kiện cho các nút chọn size và màu
document.querySelectorAll('input[name="size"], input[name="color"]').forEach(input => {
    input.addEventListener('change', updateStockInfo);
});

// Xử lý form thêm vào giỏ hàng
document.getElementById('add-to-cart-form').onsubmit = async function(e) {
    e.preventDefault();
    
    const size = document.querySelector('input[name="size"]:checked')?.value;
    const color = document.querySelector('input[name="color"]:checked')?.value;
    
    if (!size || !color) {
        alert('Vui lòng chọn size và màu sắc');
        return;
    }
    
    const variants = JSON.parse(document.getElementById('variants-data').value);
    const variant = variants.find(v => v.size === size && v.color === color);
    
    if (!variant) {
        alert('Phiên bản sản phẩm không tồn tại');
        return;
    }
    
    const quantity = parseInt(document.querySelector('input[name="quantity"]').value);
    
    if (quantity > variant.stock) {
        alert('Số lượng trong kho không đủ');
        return;
    }
    
    try {
        const response = await fetch('add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                variant_id: variant.id,
                quantity: quantity
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateCartCount(data.cart_count);
            
            // Hiển thị toast thông báo
            const toast = new bootstrap.Toast(document.getElementById('addToCartToast'));
            toast.show();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
    }
};

// Hàm đổi ảnh chính
function changeMainImage(src) {
    document.querySelector('.main-image').src = src;
}

// Thêm hiệu ứng fade-in cho sản phẩm
const products = document.querySelectorAll('.card');
products.forEach(product => {
    product.classList.add('fade-in');
});

// Tạo nút cuộn lên đầu trang
const scrollTopBtn = document.createElement('button');
scrollTopBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
scrollTopBtn.className = 'btn btn-primary scroll-top-btn';
document.body.appendChild(scrollTopBtn);

// Xử lý sự kiện click nút cuộn lên
scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'  // Cuộn mượt
    });
});

// Hiển thị/ẩn nút cuộn lên khi scroll
window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {  // Khi cuộn xuống 300px
        scrollTopBtn.style.display = 'block';
    } else {
        scrollTopBtn.style.display = 'none';
    }
});

// Thêm hiệu ứng loading khi thêm vào giỏ
addToCartButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Lưu text gốc của nút
        const originalText = this.innerHTML;
        // Thay thế bằng spinner
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang thêm...';
        this.disabled = true;

        // Sau 1 giây, khôi phục trạng thái nút
        setTimeout(() => {
            this.innerHTML = originalText;
            this.disabled = false;
        }, 1000);
    });
});

// Hiệu ứng zoom ảnh trong trang chi tiết sản phẩm
const productImage = document.querySelector('.product-image');
if (productImage) {
    // Xử lý sự kiện di chuột
    productImage.addEventListener('mousemove', function(e) {
        // Tính toán vị trí con trỏ chuột
        const x = e.clientX - this.offsetLeft;
        const y = e.clientY - this.offsetTop;
        
        // Áp dụng hiệu ứng zoom
        this.style.transformOrigin = `${x}px ${y}px`;
        this.style.transform = 'scale(1.5)';
    });

    // Khôi phục khi rời chuột
    productImage.addEventListener('mouseleave', function() {
        this.style.transformOrigin = 'center center';
        this.style.transform = 'scale(1)';
    });
}

// Thêm CSS cho nút cuộn lên đầu trang
const style = document.createElement('style');
style.textContent = `
    .scroll-top-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: none;
        z-index: 99;
        padding: 10px 15px;
        border-radius: 50%;
        opacity: 0.8;
    }
    .scroll-top-btn:hover {
        opacity: 1;
    }
`;
document.head.appendChild(style); 

// CSS cho gallery ảnh
const galleryStyle = `
    .gallery-thumb {
        cursor: pointer;
        transition: opacity 0.3s ease;
    }
    .gallery-thumb:hover {
        opacity: 0.8;
    }
    .main-image {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
`;

// Thêm CSS gallery vào head
const styleSheet = document.createElement("style");
styleSheet.textContent = galleryStyle;
document.head.appendChild(styleSheet); 