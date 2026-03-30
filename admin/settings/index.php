<?php
// admin/settings/index.php

require_once '../../includes/config.php';
require_once dirname(__DIR__, 2) . '/functions.php';
requireUserLogin();

$currentUser = getCurrentUser($pdo);
if ($currentUser['role'] !== 'admin') {
    setFlash('error', 'Bạn không có quyền truy cập trang này.');
    header('Location: ' . SITE_URL);
    exit;
}

$activeTab = $_GET['tab'] ?? 'general';
$tabs = [
    'general' => 'Thông tin chung',
    'contact' => 'Liên hệ',
    'social'  => 'Mạng xã hội',
    'email'   => 'Cấu hình Email',
    'about'   => 'Trang Giới thiệu'
];

if (!array_key_exists($activeTab, $tabs)) {
    $activeTab = 'general';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlash('error', 'Phiên làm việc không hợp lệ.');
    } else {
        $settings = $_POST['settings'] ?? [];
        $success = true;

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?');
            foreach ($settings as $key => $value) {
                $stmt->execute([$value, $key]);
            }
            $pdo->commit();
            setFlash('success', 'Đã lưu cấu hình thành công.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            setFlash('error', 'Có lỗi xảy ra khi lưu: ' . $e->getMessage());
            $success = false;
        }

        if ($success) {
            header('Location: index.php?tab=' . $activeTab);
            exit;
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM site_settings WHERE setting_group = ? ORDER BY id ASC');
$stmt->execute([$activeTab]);
$settingsList = $stmt->fetchAll();

$pageTitle = 'Cài đặt hệ thống';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-cog" style="color:var(--admin-primary)"></i> Cài đặt hệ thống</h1>
</div>

<div class="fade-in">
    <!-- Tabs -->
    <div class="settings-tabs" style="margin-bottom: 20px; border-bottom: 2px solid #e8ecf4; display:flex; gap: 15px;">
        <?php foreach ($tabs as $key => $label): ?>
            <a href="index.php?tab=<?= $key ?>" 
                style="padding: 10px 16px; font-weight: 600; color: <?= $key === $activeTab ? 'var(--primary)' : '#666' ?>; border-bottom: <?= $key === $activeTab ? '3px solid var(--primary)' : '3px solid transparent' ?>; text-decoration: none; margin-bottom: -2px;">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="form-card">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="tab" value="<?= $activeTab ?>">

                    <?php foreach ($settingsList as $setting): ?>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 600; display:block; margin-bottom: 6px;">
                                <?= sanitize($setting['description'] ?: $setting['setting_key']) ?>
                            </label>
                            
                            <?php if ($setting['setting_key'] === 'about_content' || $setting['setting_key'] === 'site_working_hours_detail'): ?>
                                <textarea name="settings[<?= $setting['setting_key'] ?>]" class="form-control" rows="5" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;"><?= sanitize($setting['setting_value']) ?></textarea>
                            <?php elseif ($setting['setting_key'] === 'smtp_encryption'): ?>
                                <select name="settings[<?= $setting['setting_key'] ?>]" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                                    <option value="tls" <?= $setting['setting_value'] === 'tls' ? 'selected' : '' ?>>TLS (Khuyên dùng)</option>
                                    <option value="ssl" <?= $setting['setting_value'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= $setting['setting_value'] === 'none' ? 'selected' : '' ?>>Không mã hóa</option>
                                </select>
                            <?php elseif (strpos($setting['setting_key'], 'password') !== false): ?>
                                <input type="password" name="settings[<?= $setting['setting_key'] ?>]" class="form-control" value="<?= sanitize($setting['setting_value']) ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                            <?php else: ?>
                                <input type="text" name="settings[<?= $setting['setting_key'] ?>]" class="form-control" value="<?= sanitize($setting['setting_value']) ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                <?php if (empty($settingsList)): ?>
                    <p style="color: #666;">Chưa có cài đặt nào trong nhóm này.</p>
                <?php else: ?>
                    <div class="form-actions">
                        <button type="submit" class="btn-admin btn-add"><i class="fas fa-save"></i> Lưu cài đặt</button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

<?php require_once '../includes/footer.php'; ?>

