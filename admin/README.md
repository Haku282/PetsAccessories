C# 🔐 Hệ Thống Xác Thực Và Phân Quyền

## 📋 Tổng Quan

Hệ thống login đã được cập nhật để hỗ trợ phân quyền dựa trên vai trò (role):

### Các vai trò:
- **admin** - Người quản trị hệ thống (truy cập admin dashboard)
- **customer** - Khách hàng (truy cập trang web bán hàng)

---

## 🔑 Tài Khoản Test

**Admin Account:**
- Username: `admin`
- Email: `admin@gmail.com`
- Password: `1234` 
- Vai trò: Admin

---

## 🔄 Quy Trình Đăng Nhập

### 1. Khi User Đăng Nhập:
```php
// Thông tin được lưu vào session
$_SESSION['user_id'] = user_id;
$_SESSION['user_name'] = fullname;
$_SESSION['role'] = 'admin' hoặc 'customer';
```

### 2. Chuyển Hướng Tự Động:
- Nếu **role = admin** → Chuyển tới `/admin/frontend/index_admin.php`
- Nếu **role = customer** → Chuyển tới `/frontend/public/index.php`

### 3. Truy Cập Trang Admin:
- Trang admin sử dụng middleware `check_admin.php` để kiểm tra:
  - User phải đã đăng nhập
  - User phải có role = 'admin'
  - Nếu không, sẽ bị redirect về trang login hoặc trang chủ

---

## 📁 Cấu Trúc File

```
admin/
├── backend/
│   └── middleware/
│       ├── check_admin.php      ← Kiểm tra quyền admin
│       └── check_customer.php   ← Kiểm tra quyền customer
├── frontend/
│   ├── index_admin.php          ← Dashboard admin (dùng check_admin.php)
│   └── pages/
│       ├── products.php         ← Quản lý sản phẩm
│       ├── orders.php           ← Quản lý đơn hàng
│       └── ...
```

---

## 🛡️ Cách Sử Dụng Middleware

### Để Bảo Vệ Trang Admin:
Thêm dòng này **ở đầu** file:
```php
<?php
require_once __DIR__ . '/../backend/middleware/check_admin.php';
require_once __DIR__ . '/../../backend/config/database.php';

// Các code khác của bạn...
?>
```

### Để Bảo Vệ Trang Customer:
```php
<?php
require_once __DIR__ . '/../backend/middleware/check_customer.php';
require_once __DIR__ . '/../../backend/config/database.php';

// Các code khác của bạn...
?>
```

---

## 📊 Dashboard Admin

File: `/admin/frontend/index_admin.php`

**Các thông tin hiển thị:**
- ✅ Tổng số đơn hàng
- ✅ Tổng số sản phẩm
- ✅ Tổng số khách hàng
- ✅ Tổng doanh thu
- ✅ Số đơn hàng chưa xác nhận
- ✅ Số sản phẩm hết hàng

**Menu quản lý:**
- 📊 Dashboard
- 📦 Sản Phẩm
- 🛒 Đơn Hàng
- 📁 Danh Mục
- 👥 Người Dùng
- 🎟️ Mã Giảm Giá
- 🖼️ Banner

---

## 🔗 Links Quan Trọng

- **Login**: `/frontend/components/login.php`
- **Logout**: `/frontend/components/logout.php`
- **Frontend Home**: `/frontend/public/index.php`
- **Admin Dashboard**: `/admin/frontend/index_admin.php`

---

## 🧪 Test Chức Năng

### Test 1: Đăng nhập với Admin
1. Truy cập `/frontend/components/login.php`
2. Nhập: `admin` / `1234`
3. Kết quả: Chuyển tới `/admin/frontend/index_admin.php` ✅

### Test 2: Đăng nhập với Customer
1. Tạo user mới với role = 'customer'
2. Nhập credentials
3. Kết quả: Chuyển tới `/frontend/public/index.php` ✅

### Test 3: Truy cập Admin khi là Customer
1. Đăng nhập với customer
2. Cố truy cập `/admin/frontend/index_admin.php`
3. Kết quả: Chuyển về trang chủ hoặc login ✅

---

## 🚀 Các Bước Tiếp Theo

Chúng ta cần tạo các trang quản lý:
1. Quản lý sản phẩm (`products.php`)
2. Quản lý đơn hàng (`orders.php`)
3. Quản lý danh mục (`categories.php`)
4. Quản lý người dùng (`users.php`)
5. Quản lý mã giảm giá (`coupons.php`)
6. Quản lý banner (`banners.php`)

---

**Đã sẵn sàng phát triển tiếp theo!** 🎉
