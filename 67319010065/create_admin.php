<?php
// create_admin.php (รันครั้งเดียว)
include 'db.php';

$adminUsername = 'admin';
$adminEmail = 'admin@example.com';
$adminFullname = 'Administrator';
$adminPasswordPlain = 'Admin@123'; // เปลี่ยนก่อนรันจริง

$hash = password_hash($adminPasswordPlain, PASSWORD_DEFAULT);

// ตรวจสอบว่ามี admin อยู่แล้วหรือไม่
$stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
$stmt->bind_param("ss", $adminUsername, $adminEmail);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    echo "ผู้ใช้ admin มีอยู่แล้ว\n";
    exit;
}

$stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password, role, status) VALUES (?, ?, ?, ?, 'admin', 1)");
$stmt->bind_param("ssss", $adminFullname, $adminEmail, $adminUsername, $hash);
if ($stmt->execute()) {
    echo "สร้าง admin เรียบร้อย: username={$adminUsername}, password={$adminPasswordPlain}\n";
} else {
    echo "เกิดข้อผิดพลาด: " . $stmt->error;
}
