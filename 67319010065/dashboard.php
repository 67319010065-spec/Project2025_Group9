<?php
session_start();

// ถ้ายังไม่ล็อกอิน ให้ไปหน้า login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ปลอดภัย: ใช้ htmlspecialchars ก่อนแสดงข้อมูลจาก session
$fullname = htmlspecialchars($_SESSION['fullname']);
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="register-container">
    <h2>ยินดีต้อนรับ, <?php echo $fullname ?: $username; ?>!</h2>
    <p>คุณล็อกอินสำเร็จแล้ว</p>
    <p><a href="logout.php">ออกจากระบบ</a></p>
</div>
</body>
</html>
