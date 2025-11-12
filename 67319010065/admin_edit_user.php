<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ไม่พบผู้ใช้');
}

// โหลดข้อมูลผู้ใช้
$stmt = $conn->prepare("SELECT id, fullname, username, email, role, status FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) die('ไม่พบผู้ใช้');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = ($_POST['role'] === 'admin') ? 'admin' : 'user';
    $status = isset($_POST['status']) ? 1 : 0;

    // ถ้ามีการรีเซ็ตรหัสผ่าน
    $newpass = $_POST['password'] ?? '';
    if ($newpass !== '') {
        $hash = password_hash($newpass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET fullname=?, username=?, email=?, role=?, status=?, password=? WHERE id=?");
        $stmt->bind_param("sssisii", $fullname, $username, $email, $role, $status, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET fullname=?, username=?, email=?, role=?, status=? WHERE id=?");
        $stmt->bind_param("sssiii", $fullname, $username, $email, $role, $status, $id);
    }

    if ($stmt->execute()) {
        $message = 'บันทึกเรียบร้อย';
        // refresh user data
        $stmt2 = $conn->prepare("SELECT id, fullname, username, email, role, status FROM users WHERE id = ? LIMIT 1");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $user = $stmt2->get_result()->fetch_assoc();
    } else {
        $message = 'เกิดข้อผิดพลาด: ' . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>แก้ไขผู้ใช้</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="register-container" style="width:500px;">
    <h2>แก้ไขผู้ใช้ #<?php echo $user['id']; ?></h2>
    <form method="post">
        <input type="text" name="fullname" placeholder="ชื่อ-นามสกุล" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
        <input type="text" name="username" placeholder="ชื่อผู้ใช้" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <input type="email" name="email" placeholder="อีเมล" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>บทบาท:
            <select name="role">
                <option value="user" <?php echo ($user['role']=='user') ? 'selected' : ''; ?>>user</option>
                <option value="admin" <?php echo ($user['role']=='admin') ? 'selected' : ''; ?>>admin</option>
            </select>
        </label>
        <br><br>
        <label><input type="checkbox" name="status" <?php echo $user['status'] ? 'checked' : ''; ?>> เปิดใช้งาน</label>

        <p>ถ้าต้องการเปลี่ยนรหัสผ่าน ให้กรอกด้านล่าง (เว้นว่าง = ไม่เปลี่ยน)</p>
        <input type="password" name="password" placeholder="รหัสผ่านใหม่">

        <button type="submit">บันทึก</button>
    </form>
    <p style="color:green;"><?php echo htmlspecialchars($message); ?></p>
    <p><a href="admin.php">กลับไปหน้าจัดการผู้ใช้</a></p>
</div>
</body>
</html>
