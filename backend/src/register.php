<?php
// frontend/components/register.php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

// Import các class của PHPMailer vào không gian tên toàn cục
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load thư viện Composer (Điều chỉnh lại đường dẫn cho đúng với cấu trúc thư mục của bạn)
require __DIR__ . '/../../vendor/autoload.php'; 

$error = '';
$success = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    // Xử lý hủy đăng ký (xóa session OTP)
    if (isset($_GET['cancel'])) {
        unset($_SESSION['otp_pending'], $_SESSION['otp'], $_SESSION['registration_data']);
        header('Location: register.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'register') {
            $fullname = trim($_POST['fullname'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm-password'] ?? '';

            // Kiểm tra dữ liệu hợp lệ cơ bản
            if (empty($fullname) || empty($username) || empty($email) || empty($phone) || empty($password)) {
                $error = "Vui lòng nhập đầy đủ thông tin.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Định dạng email không hợp lệ.";
            } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
                $error = "Số điện thoại không hợp lệ (phải từ 10-11 số).";
            } elseif ($password !== $confirm_password) {
                $error = "Mật khẩu nhập lại không khớp.";
            } else {
                // Kiểm tra xem Username hoặc Email đã tồn tại chưa
                $sqlCheck = "SELECT user_id FROM users WHERE username = ? OR email = ?";
                $stmtCheck = $db->prepare($sqlCheck);
                $stmtCheck->execute([$username, $email]);

                if ($stmtCheck->fetch()) {
                    $error = "Tên đăng nhập hoặc Email này đã tồn tại trong hệ thống.";
                } else {
                    // Tạo mã OTP (6 chữ số)
                    $otp = rand(100000, 999999);
                    
                    // Lưu dữ liệu vào session
                    $_SESSION['registration_data'] = [
                        'fullname' => $fullname,
                        'username' => $username,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $password
                    ];
                    $_SESSION['otp'] = $otp;
                    $_SESSION['otp_pending'] = true;

                    // ==========================================
                    // BẮT ĐẦU PHẦN GỬI EMAIL BẰNG PHPMAILER
                    // ==========================================
                    $mail = new PHPMailer(true);

                    try {
                        // Cấu hình Server SMTP
                        $mail->isSMTP();                                            
                        $mail->Host       = 'smtp.gmail.com';                     
                        $mail->SMTPAuth   = true;                                   
                        $mail->Username   = 'gaming12882000@gmail.com'; // ĐIỀN EMAIL CỦA BẠN VÀO ĐÂY
                        $mail->Password   = 'obkz gjkr zqtt rasr'; // ĐIỀN MẬT KHẨU ỨNG DỤNG VÀO ĐÂY
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
                        $mail->Port       = 587;                                    

                        // Thiết lập mã hóa tiếng Việt
                        $mail->CharSet = 'UTF-8';

                        // Người gửi và người nhận
                        $mail->setFrom('gaming12882000@gmail.com', 'Pets Accessories');
                        $mail->addAddress($email, $fullname);     

                        // Nội dung Email
                        $mail->isHTML(true);                                  
                        $mail->Subject = 'Mã xác thực OTP - Pets Accessories';
                        $mail->Body    = "Chào <b>$fullname</b>,<br><br>Mã OTP xác thực đăng ký tài khoản của bạn là: <h2 style='color:blue;'>$otp</h2><br>Vui lòng không chia sẻ mã này cho bất kỳ ai.";
                        $mail->AltBody = "Mã OTP xác thực đăng ký của bạn là: $otp \n\nVui lòng không chia sẻ mã này cho bất kỳ ai.";

                        // Thực thi gửi
                        $mail->send();
                        $success = "Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư đến (hoặc thư mục Spam).";
                    } catch (Exception $e) {
                        // Nếu lỗi do mạng lưới gửi mail, bạn có thể in ra dòng dưới để test dễ dàng trên local
                        $error = "Không thể gửi email OTP lúc này. Lỗi hệ thống: {$mail->ErrorInfo}";
                        $success = "Mã OTP Test Local (Fallback): $otp"; // Xóa dòng này khi đưa lên server thật
                    }
                    // ==========================================
                    // KẾT THÚC PHẦN GỬI EMAIL
                    // ==========================================
                }
            }
        } elseif ($action === 'verify_otp') {
            // ... (Đoạn code xác thực OTP giữ nguyên như cũ) ...
            $entered_otp = trim($_POST['otp'] ?? '');
            
            if (!isset($_SESSION['otp_pending']) || !$_SESSION['otp_pending']) {
                $error = "Phiên đăng ký không hợp lệ hoặc đã hết hạn.";
            } elseif ($entered_otp == $_SESSION['otp']) {
                $data = $_SESSION['registration_data'];
                $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

                $sqlInsert = "INSERT INTO users (fullname, username, email, phone, password) VALUES (?, ?, ?, ?, ?)";
                $stmtInsert = $db->prepare($sqlInsert);

                try {
                    if ($stmtInsert->execute([$data['fullname'], $data['username'], $data['email'], $data['phone'], $hashed_password])) {
                        $success = "Đăng ký thành công! Hãy đăng nhập để tiếp tục.";
                        unset($_SESSION['otp_pending'], $_SESSION['otp'], $_SESSION['registration_data']);
                    } else {
                        $error = "Đã xảy ra lỗi khi tạo tài khoản, vui lòng thử lại sau.";
                    }
                } catch (PDOException $e) {
                    $error = "Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage();
                }
            } else {
                $error = "Mã OTP không chính xác. Vui lòng kiểm tra lại.";
            }
        }
    }
}
?>