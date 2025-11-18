<?php
session_start();
include 'db.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// ค่าค้นหา/หน้า
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// สร้าง SQL (search by fullname/username/email)
$params = [];
$where = "1";
if ($search !== '') {
    $where = "(fullname LIKE ? OR username LIKE ? OR email LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
}

$sql = "SELECT SQL_CALC_FOUND_ROWS id, fullname, username, email, role, status, created_at FROM users WHERE $where ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if ($search !== '') {
    // bind search + limit + offset
    $stmt->bind_param("sssss", $params[0], $params[1], $params[2], $perPage, $offset);
} else {
    $stmt = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS id, fullname, username, email, role, status, created_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $perPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$totalRes = $conn->query("SELECT FOUND_ROWS() as total")->fetch_assoc();
$total = intval($totalRes['total']);
$pages = ceil($total / $perPage);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Admin - จัดการผู้ใช้</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="register-container" style="width:95%;max-width:1000px;">
    <h2>หน้าผู้ดูแลระบบ — จัดการผู้ใช้</h2>
    <p>ผู้ใช้งาน: <?php echo htmlspecialchars($_SESSION['fullname']); ?> (<a href="dashboard.php">กลับ</a>) | <a href="logout.php">ออกจากระบบ</a></p>

    <form method="get" style="margin-bottom:12px;">
        <input type="text" name="q" placeholder="ค้นหา ชื่อ, ชื่อผู้ใช้ หรือ อีเมล" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">ค้นหา</button>
    </form>

    <table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
        <thead>
            <tr>
                <th>#</th>
                <th>ชื่อ-นามสกุล</th>
                <th>ชื่อผู้ใช้</th>
                <th>อีเมล</th>
                <th>บทบาท</th>
                <th>สถานะ</th>
                <th>สร้างเมื่อ</th>
                <th>การจัดการ</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                <td><?php echo $row['status'] ? 'active' : 'inactive'; ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td>
                    <a href="admin_edit_user.php?id=<?php echo $row['id']; ?>">แก้ไข</a> |
                    <?php if ($row['id'] != $_SESSION['user_id']): // ป้องกันลบตัวเอง ?>
                        <a href="admin_delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('ลบผู้ใช้นี้จริงหรือไม่?')">ลบ</a> |
                    <?php else: ?>
                        ลบ(ไม่อนุญาต)
                    <?php endif; ?>
                    |
                    <a href="admin_toggle_status.php?id=<?php echo $row['id']; ?>"><?php echo $row['status'] ? 'ปิดใช้งาน' : 'เปิดใช้งาน'; ?></a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <div style="margin-top:12px;">
        <?php for ($p=1; $p<=$pages; $p++): ?>
            <a href="?q=<?php echo urlencode($search); ?>&page=<?php echo $p; ?>" style="margin-right:6px;<?php echo $p==$page ? 'font-weight:bold;' : ''; ?>"><?php echo $p; ?></a>
        <?php endfor; ?>
    </div>
</div>
</body>
</html>
