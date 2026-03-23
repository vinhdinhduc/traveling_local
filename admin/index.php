<?php
$adminTitle = 'Dashboard';
require_once 'includes/header.php';

// Lấy thống kê
$totalPlaces = countRecords($pdo, 'places');
$totalNews = countRecords($pdo, 'news');
$totalContacts = countRecords($pdo, 'contacts');
$totalFoods = countRecords($pdo, 'foods');
$totalHomestays = countRecords($pdo, 'homestays');
$totalUsers = countRecords($pdo, 'users');
$totalReviews = countRecords($pdo, 'reviews');

// Tổng lượt xem
$stmtViews = $pdo->query("SELECT COALESCE(SUM(views), 0) as total FROM (
    SELECT views FROM places UNION ALL SELECT views FROM news
) as combined");
$totalViews = $stmtViews->fetch()['total'];

// 5 liên hệ mới nhất
$stmtRecentContacts = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
$recentContacts = $stmtRecentContacts->fetchAll();
?>

<!-- Thống kê -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon places">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalPlaces ?></h3>
            <p>Địa điểm du lịch</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon news">
            <i class="fas fa-newspaper"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalNews ?></h3>
            <p>Bài viết tin tức</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon contacts">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalContacts ?></h3>
            <p>Liên hệ nhận được</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon views">
            <i class="fas fa-eye"></i>
        </div>
        <div class="stat-info">
            <h3><?= number_format($totalViews) ?></h3>
            <p>Tổng lượt xem</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon news">
            <i class="fas fa-utensils"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalFoods ?></h3>
            <p>Món ăn ẩm thực</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon places">
            <i class="fas fa-house"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalHomestays ?></h3>
            <p>Homestay</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon contacts">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalUsers ?></h3>
            <p>Người dùng</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon views">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalReviews ?></h3>
            <p>Đánh giá</p>
        </div>
    </div>
</div>

<!-- Liên hệ gần đây -->
<div class="table-wrapper">
    <div class="table-header">
        <h3><i class="fas fa-envelope" style="color:var(--admin-primary)"></i> Liên hệ gần đây</h3>
        <a href="<?= ADMIN_URL ?>/contacts.php" class="btn-admin btn-add">Xem tất cả</a>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Nội dung</th>
                <th>Ngày gửi</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($recentContacts) > 0): ?>
                <?php foreach ($recentContacts as $contact): ?>
                    <tr>
                        <td><strong><?= sanitize($contact['name']) ?></strong></td>
                        <td><?= sanitize($contact['email']) ?></td>
                        <td><?= excerpt(sanitize($contact['message']), 60) ?></td>
                        <td><?= formatDateTime($contact['created_at']) ?></td>
                        <td>
                            <?php if ($contact['is_read']): ?>
                                <span style="color:var(--admin-primary)"><i class="fas fa-check-circle"></i> Đã đọc</span>
                            <?php else: ?>
                                <span style="color:#e74c3c"><i class="fas fa-circle"></i> Chưa đọc</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;padding:30px;color:#999">Chưa có liên hệ nào</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>