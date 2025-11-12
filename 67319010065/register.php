<?php
include 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // ตรวจสอบชื่อผู้ใช้หรืออีเมลซ้ำ
    $check = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้ว";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $email, $username, $password);

        if ($stmt->execute()) {
            header("Location: success.php");
            exit;
        } else {
            $message = "เกิดข้อผิดพลาดในการสมัครสมาชิก";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>สมัครสมาชิก</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="register-container">
    <h2>สมัครสมาชิก</h2>
    <form action="" method="post">
        <input type="text" name="fullname" placeholder="ชื่อ-นามสกุล" required>
        <input type="email" name="email" placeholder="อีเมล" required>
        <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
        <input type="password" name="password" placeholder="รหัสผ่าน" required>
        <button type="submit">สมัครสมาชิก</button>
    </form>
    <p style="color:red;"><?php echo $message; ?></p>
</div>
</body>
</html>
