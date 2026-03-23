<?php

$adminTitle = 'Quản lý tin tức';
require_once dirname(__DIR__) . '/includes/header.php';

$currentPageNum = getCurrentPage();
$perPage = 10;
$totalNews = countRecords($pdo, 'news');
$totalPages = ceil($totalNews / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM news ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$newsList = $stmt->fetchAll();
?>

<div class="content-header">
    <h1><i class="fas fa-newspaper" style="color:var(--admin-secondary)"></i> Quản lý tin tức</h1>
    <a href="<?= ADMIN_URL ?>/news/add.php" class="btn-admin btn-add">
        <i class="fas fa-plus"></i> Thêm bài viết
    </a>
</div>

<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tiêu đề</th>
                <th>Lượt xem</th>
                <th>Ngày đăng</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($newsList) > 0): ?>
                <?php foreach ($newsList as $news): ?>
                    <tr>
                        <td><?= $news['id'] ?></td>
                        <td>
                            <?php if (!empty($news['image'])): ?>
                                <img src="<?= getImageUrl($news['image'], 'news') ?>" class="thumb" alt="">
                            <?php else: ?>
                                <div class="thumb" style="background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#ccc;width:60px;height:45px;border-radius:4px">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= sanitize(excerpt($news['title'], 50)) ?></strong></td>
                        <td><?= $news['views'] ?></td>
                        <td><?= formatDate($news['created_at']) ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= SITE_URL ?>/news-detail.php?id=<?= $news['id'] ?>"
                                    class="btn-admin btn-view" target="_blank" title="Xem">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= ADMIN_URL ?>/news/edit.php?id=<?= $news['id'] ?>"
                                    class="btn-admin btn-edit" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= ADMIN_URL ?>/news/delete.php?id=<?= $news['id'] ?>"
                                    class="btn-admin btn-delete" title="Xóa"
                                    data-confirm="Bạn có chắc chắn muốn xóa bài viết này?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:30px;color:#999">Chưa có bài viết nào</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPageNum > 1): ?>
                <a href="?page=<?= $currentPageNum - 1 ?>">&laquo; Trước</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i === $currentPageNum ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($currentPageNum < $totalPages): ?>
                <a href="?page=<?= $currentPageNum + 1 ?>">Tiếp &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>