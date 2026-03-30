<?php

/**
 * includes/settings.php
 *
 * Hàm tiện ích đọc cấu hình website từ bảng site_settings,
 * slider, gallery, tiện nghi homestay.
 * Cache trong static variable để tránh query lặp trong 1 request.
 */

/**
 * Lấy 1 giá trị setting theo key.
 */
function getSetting(PDO $pdo, string $key, string $default = ''): string
{
    static $cache = [];

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();

    $cache[$key] = ($value !== false && $value !== null) ? $value : $default;
    return $cache[$key];
}

/**
 * Lấy nhiều settings theo nhóm.
 * Trả về mảng key => value.
 */
function getSettingsByGroup(PDO $pdo, string $group): array
{
    static $groupCache = [];

    if (isset($groupCache[$group])) {
        return $groupCache[$group];
    }

    $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM site_settings WHERE setting_group = ?');
    $stmt->execute([$group]);
    $rows = $stmt->fetchAll();

    $result = [];
    foreach ($rows as $row) {
        $result[$row['setting_key']] = $row['setting_value'] ?? '';
    }

    $groupCache[$group] = $result;
    return $result;
}

/**
 * Cập nhật 1 setting.
 */
function updateSetting(PDO $pdo, string $key, string $value): bool
{
    $stmt = $pdo->prepare('UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?');
    return $stmt->execute([$value, $key]);
}

/**
 * Lấy danh sách slider đang active, sắp xếp theo sort_order.
 */
function getSliders(PDO $pdo): array
{
    static $sliders = null;
    if ($sliders !== null) return $sliders;

    $stmt = $pdo->query('SELECT * FROM sliders WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
    $sliders = $stmt->fetchAll();
    return $sliders;
}

/**
 * Lấy danh sách ảnh gallery đang active.
 */
function getGalleryImages(PDO $pdo): array
{
    static $gallery = null;
    if ($gallery !== null) return $gallery;

    $stmt = $pdo->query('SELECT * FROM gallery WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
    $gallery = $stmt->fetchAll();
    return $gallery;
}

/**
 * Lấy danh sách tiện nghi của 1 homestay.
 * Nếu homestay chưa được gán tiện nghi nào → trả về tất cả tiện nghi (fallback).
 */
function getHomestayAmenities(PDO $pdo, int $homestayId): array
{
    $stmt = $pdo->prepare('
        SELECT a.* FROM amenities a
        JOIN homestay_amenities ha ON ha.amenity_id = a.id
        WHERE ha.homestay_id = ?
        ORDER BY a.sort_order ASC, a.id ASC
    ');
    $stmt->execute([$homestayId]);
    $amenities = $stmt->fetchAll();

    // Fallback: nếu chưa gán → lấy tất cả
    if (empty($amenities)) {
        $stmt2 = $pdo->query('SELECT * FROM amenities ORDER BY sort_order ASC, id ASC');
        $amenities = $stmt2->fetchAll();
    }

    return $amenities;
}

/**
 * Lấy tất cả tiện nghi.
 */
function getAllAmenities(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM amenities ORDER BY sort_order ASC, id ASC');
    return $stmt->fetchAll();
}

/**
 * Lấy danh sách amenity_id đã gán cho 1 homestay.
 */
function getHomestayAmenityIds(PDO $pdo, int $homestayId): array
{
    $stmt = $pdo->prepare('SELECT amenity_id FROM homestay_amenities WHERE homestay_id = ?');
    $stmt->execute([$homestayId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Đồng bộ tiện nghi cho homestay (xóa cũ, thêm mới).
 */
function syncHomestayAmenities(PDO $pdo, int $homestayId, array $amenityIds): void
{
    $pdo->prepare('DELETE FROM homestay_amenities WHERE homestay_id = ?')->execute([$homestayId]);

    if (!empty($amenityIds)) {
        $stmt = $pdo->prepare('INSERT INTO homestay_amenities (homestay_id, amenity_id) VALUES (?, ?)');
        foreach ($amenityIds as $amenityId) {
            $stmt->execute([$homestayId, (int)$amenityId]);
        }
    }
}
