<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/profile.php';

$prefillAddressSpecific = '';
$prefillProv = '';
$prefillDist = '';
$prefillWard = '';

if (!empty($user['address'])) {
    $parts = array_map('trim', explode(',', $user['address']));
    if (count($parts) >= 4) {
        $prefillProv = array_pop($parts);
        $prefillDist = array_pop($parts);
        $prefillWard = array_pop($parts);
        $prefillAddressSpecific = implode(', ', $parts);
    } elseif (count($parts) === 3) {
        $prefillProv = $parts[2];
        $prefillDist = $parts[1];
        $prefillWard = $parts[0];
    } else {
        $prefillAddressSpecific = $user['address'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .profile-view .info-group {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .profile-view .info-group label {
            display: block;
            font-size: 14px;
            color: #888;
            margin-bottom: 5px;
        }

        .profile-view .info-group p {
            font-size: 16px;
            color: #333;
            font-weight: 600;
            margin: 0;
        }

        .btn-edit-mode {
            background-color: #ff6f61;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .btn-edit-mode:hover {
            background-color: #e05e50;
        }

        .select2-container .select2-selection--single {
            height: 48px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
        }
    </style>
</head>

<body>

    <?php require_once __DIR__ . '/../layout/header.php'; ?>

    <?php $showEditForm = ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error)) ? true : false; ?>

    <main class="auth-container">
        <div class="auth-box profile-container">
            <h2 style="text-align: center; margin-bottom: 10px;">Hồ sơ cá nhân</h2>

            <div class="profile-avatar-container" style="text-align: center; margin-bottom: 25px;">
                <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #ff6f61; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            </div>

            <?php if (!empty($error)): ?>
                <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- CHẾ ĐỘ XEM THÔNG TIN (VIEW) -->
            <div id="profile-view" class="profile-view" style="display: <?php echo $showEditForm ? 'none' : 'block'; ?>;">
                <div class="info-group">
                    <label>Tên đăng nhập</label>
                    <p><?php echo htmlspecialchars($user['username'] ?? ''); ?></p>
                </div>
                <div class="info-group">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                </div>
                <div class="info-group">
                    <label>Họ và tên</label>
                    <p><?php echo htmlspecialchars($user['fullname'] ?? 'Chưa thiết lập'); ?></p>
                </div>
                <div class="info-group">
                    <label>Số điện thoại</label>
                    <p><?php echo htmlspecialchars(!empty($user['phone']) ? $user['phone'] : 'Chưa thiết lập'); ?></p>
                </div>
                <div class="info-group">
                    <label>Địa chỉ</label>
                    <p><?php echo htmlspecialchars(!empty($user['address']) ? $user['address'] : 'Chưa thiết lập'); ?></p>
                </div>

                <button type="button" class="btn-edit-mode" onclick="toggleProfileMode('edit')">Chỉnh sửa</button>
                <p class="index-link" style="margin-top: 15px; text-align: center;">
                    <a href="/PetsAccessories/frontend/public/index.php">Quay về trang chủ</a>
                </p>
            </div>

            <!-- CHẾ ĐỘ CHỈNH SỬA (EDIT) -->
            <div id="profile-edit" style="display: <?php echo $showEditForm ? 'block' : 'none'; ?>;">
                <form action="profile.php" method="POST" class="auth-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="avatar">Ảnh đại diện (Avatar)</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="username">Tên đăng nhập</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>

                    <div class="form-group">
                        <label for="fullname">Họ và tên</label>
                        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="province">Tỉnh/Thành phố</label>
                        <select name="province" id="province" required style="width: 100%;">
                            <option value="">Chọn Tỉnh/Thành phố</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="district">Quận/Huyện</label>
                        <select name="district" id="district" required style="width: 100%;">
                            <option value="">Chọn Quận/Huyện</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ward">Phường/Xã</label>
                        <select name="ward" id="ward" required style="width: 100%;">
                            <option value="">Chọn Phường/Xã</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address">Địa chỉ cụ thể</label>
                        <input type="text" id="address" name="address" required value="<?php echo htmlspecialchars($prefillAddressSpecific); ?>">
                    </div>

                    <button type="submit" class="btn-auth">Cập nhật hồ sơ</button>

                    <p class="index-link" style="margin-top: 15px; text-align: center;">
                        <a href="javascript:void(0)" onclick="toggleProfileMode('view')">Quay lại thông tin cá nhân</a>
                    </p>
                </form>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function toggleProfileMode(mode) {
            const viewSection = document.getElementById('profile-view');
            const editSection = document.getElementById('profile-edit');

            if (mode === 'edit') {
                viewSection.style.display = 'none';
                editSection.style.display = 'block';
            } else {
                viewSection.style.display = 'block';
                editSection.style.display = 'none';
            }
        }

        $(document).ready(function() {
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('district');
            const wardSelect = document.getElementById('ward');

            const prefillProv = <?php echo json_encode($prefillProv ?? ''); ?>;
            const prefillDist = <?php echo json_encode($prefillDist ?? ''); ?>;
            const prefillWard = <?php echo json_encode($prefillWard ?? ''); ?>;

            $('#province').select2({ placeholder: "Chọn Tỉnh/Thành phố" });
            $('#district').select2({ placeholder: "Chọn Quận/Huyện" });
            $('#ward').select2({ placeholder: "Chọn Phường/Xã" });

            fetch('https://provinces.open-api.vn/api/?depth=3')
                .then(res => res.json())
                .then(data => {
                    let provinces = data;
                    provinces.forEach(p => {
                        let option = document.createElement('option');
                        option.value = p.name;
                        option.textContent = p.name;
                        if (p.name === prefillProv) option.selected = true;
                        provinceSelect.appendChild(option);
                    });

                    if (prefillProv) {
                        const selectedProvince = provinces.find(p => p.name === prefillProv);
                        if (selectedProvince && selectedProvince.districts) {
                            selectedProvince.districts.forEach(d => {
                                let option = document.createElement('option');
                                option.value = d.name;
                                option.textContent = d.name;
                                if (d.name === prefillDist) option.selected = true;
                                districtSelect.appendChild(option);
                            });

                            if (prefillDist) {
                                const selectedDistrict = selectedProvince.districts.find(d => d.name === prefillDist);
                                if (selectedDistrict && selectedDistrict.wards) {
                                    selectedDistrict.wards.forEach(w => {
                                        let option = document.createElement('option');
                                        option.value = w.name;
                                        option.textContent = w.name;
                                        if (w.name === prefillWard) option.selected = true;
                                        wardSelect.appendChild(option);
                                    });
                                }
                            }
                        }
                    }

                    $('#province').trigger('change.select2');
                    $('#district').trigger('change.select2');
                    $('#ward').trigger('change.select2');

                    $('#province').on('change', function() {
                        districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                        
                        const selectedProvince = provinces.find(p => p.name === this.value);
                        if (selectedProvince && selectedProvince.districts) {
                            selectedProvince.districts.forEach(d => {
                                let option = document.createElement('option');
                                option.value = d.name;
                                option.textContent = d.name;
                                districtSelect.appendChild(option);
                            });
                        }
                        $('#district').trigger('change.select2');
                        $('#ward').trigger('change.select2');
                    });

                    $('#district').on('change', function() {
                        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                        
                        const selectedProvince = provinces.find(p => p.name === provinceSelect.value);
                        if (selectedProvince) {
                            const selectedDistrict = selectedProvince.districts.find(d => d.name === this.value);
                            if (selectedDistrict && selectedDistrict.wards) {
                                selectedDistrict.wards.forEach(w => {
                                    let option = document.createElement('option');
                                    option.value = w.name;
                                    option.textContent = w.name;
                                    wardSelect.appendChild(option);
                                });
                            }
                        }
                        $('#ward').trigger('change.select2');
                    });
                })
                .catch(err => console.error('Error fetching provinces:', err));
        });
    </script>

    <?php require_once __DIR__ . '/../layout/footer.php'; ?>

</body>

</html>