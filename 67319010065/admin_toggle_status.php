<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: admin.php'); exit; }

// หาสตถานะปัจจุบัน
$stmt = $conn->prepare("SELECT status FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
if (!$res) { header('Location: admin.php'); exit; }

$newStatus = $res['status'] ? 0 : 1;
$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? LIMIT 1");
$stmt->bind_param("ii", $newStatus, $id);
$stmt->execute();

header('Location: admin.php');
exit;
