<?php
require_once '../../includes/config.php';
require_once dirname(__DIR__, 2) . '/functions.php';
requireUserLogin();

$currentUser = getCurrentUser($pdo);
if ($currentUser['role'] !== 'admin') {
    setFlash('error', 'Bạn không có quyền.');
    header('Location: ' . SITE_URL);
    exit;
}

$stmt = $pdo->query('SELECT * FROM email_templates ORDER BY id ASC');
$templates = $stmt->fetchAll();

$pageTitle = 'Quản lý Email Templates';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-envelope-open-text" style="color:var(--admin-primary)"></i> Quản lý Email Templates</h1>
</div>

<div class="fade-in">
    <div class="alert alert-info" style="margin-bottom:20px; background:#e3f2fd; color:#0c5460; padding:15px; border-radius:6px; border-left:4px solid #17a2b8;">
        <i class="fas fa-info-circle"></i> Các biến nội dung (ví dụ: <code>{{customer_name}}</code>) sẽ được hệ thống tự động thay thế bằng dữ liệu thực tế khi gửi email. Vui lòng không thay đổi tên các biến trong ngoặc nhọn.
    </div>

    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Nhận diện (Key)</th>
                    <th>Mô tả / Ngữ cảnh gửi</th>
                    <th>Cập nhật lần cuối</th>
                    <th>Hành động</th>
                </tr>
            </thead>
                        <tbody>
                            <?php foreach ($templates as $t): ?>
                                <tr>
                                    <td><?= (int)$t['id'] ?></td>
                                    <td>
                                        <code style="background:#f4f6fb; padding:3px 6px; border-radius:4px; font-size:0.9rem; color:#d63384;"><?= sanitize($t['template_key']) ?></code>
                                    </td>
                                    <td>
                                        <?= sanitize($t['description']) ?>
                                    </td>
                                    <td style="color:#777; font-size:0.9rem;">
                                        <?= $t['updated_at'] ? date('d/m/Y H:i', strtotime($t['updated_at'])) : 'Chưa cập nhật' ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="edit.php?id=<?= $t['id'] ?>" class="btn-admin btn-edit"><i class="fas fa-edit"></i> Chỉnh sửa nội dung</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
</div>

<?php require_once '../includes/footer.php'; ?>

