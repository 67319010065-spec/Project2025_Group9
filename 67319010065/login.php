<?php
session_start();
include 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity']); // username หรือ email
    $password = $_POST['password'];

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $stmt = $conn->prepare("SELECT id, fullname, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $identity, $identity);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // สร้าง session
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role']; // <-- สำคัญมาก

            // ตรวจสิทธิ์และ redirect
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $message = "❌ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $message = "❌ ไม่พบชื่อผู้ใช้หรืออีเมลนี้";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เข้าสู่ระบบ</title>
<link rel="stylesheet" href="style.css">
<style>
body {
  font-family: 'Kanit', sans-serif;
  background-color: #f8f8f8;
}
.login-container {
  width: 400px;
  margin: 80px auto;
  background: #fff;
  padding: 25px 30px;
  border-radius: 10px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}
.login-container h2 {
  text-align: center;
  color: #a00;
}
.login-container input, .login-container button {
  width: 100%;
  margin: 8px 0;
  padding: 10px;
  font-size: 16px;
  border-radius: 5px;
  border: 1px solid #ccc;
}
.login-container button {
  background: #a00;
  color: #fff;
  cursor: pointer;
  transition: 0.2s;
}
.login-container button:hover {
  background: #800;
}
.message {
  color: red;
  text-align: center;
  margin-top: 10px;
}
</style>
</head>
<body>
<div class="login-container">
  <h2>เข้าสู่ระบบ</h2>
  <form method="POST">
    <input type="text" name="identity" placeholder="ชื่อผู้ใช้ หรือ อีเมล" required>
    <input type="password" name="password" placeholder="รหัสผ่าน" required>
    <button type="submit">เข้าสู่ระบบ</button>
  </form>
  <?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>
  <p style="text-align:center;">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
</div>
</body>
</html>