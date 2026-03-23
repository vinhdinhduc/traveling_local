<?php

$pageTitle = 'Bản đồ du lịch Vân Hồ';
$pageDescription = 'Bản đồ các điểm du lịch và homestay nổi bật tại xã Vân Hồ, Sơn La';
$pageStyles = [
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
];
$pageScripts = [
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
];

require_once 'includes/header.php';

$stmtPlaces = $pdo->query('SELECT id, name, latitude, longitude, location FROM places WHERE latitude IS NOT NULL AND longitude IS NOT NULL');
$placeMarkers = $stmtPlaces->fetchAll();

$stmtHomestays = $pdo->query('SELECT id, name, latitude, longitude, address FROM homestays WHERE latitude IS NOT NULL AND longitude IS NOT NULL');
$homestayMarkers = $stmtHomestays->fetchAll();

$markers = [];
foreach ($placeMarkers as $place) {
    $markers[] = [
        'type' => 'place',
        'name' => $place['name'],
        'lat' => (float)$place['latitude'],
        'lng' => (float)$place['longitude'],
        'address' => $place['location'],
        'url' => SITE_URL . '/place-detail.php?id=' . $place['id']
    ];
}
foreach ($homestayMarkers as $homestay) {
    $markers[] = [
        'type' => 'homestay',
        'name' => $homestay['name'],
        'lat' => (float)$homestay['latitude'],
        'lng' => (float)$homestay['longitude'],
        'address' => $homestay['address'],
        'url' => SITE_URL . '/homestay-detail.php?id=' . $homestay['id']
    ];
}
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Bản đồ du lịch</li>
        </ul>
    </div>
</div>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-map" style="color:var(--secondary)"></i> Bản đồ du lịch Vân Hồ</h1>
        <p>Theo dõi vị trí địa điểm du lịch và homestay trên bản đồ mở OpenStreetMap</p>
    </div>
</div>

<section class="section" style="padding-top:20px">
    <div class="container">
        <div class="map-card">
            <div class="map-legend">
                <span><i class="fas fa-map-marker-alt" style="color:#e53935"></i> Địa điểm du lịch</span>
                <span><i class="fas fa-house" style="color:#1e88e5"></i> Homestay</span>
            </div>
            <div class="map-tools" aria-label="Bộ lọc bản đồ">
                <label class="map-filter-item">
                    <input type="checkbox" id="filter-place" checked>
                    <span>Hiển thị địa điểm</span>
                </label>
                <label class="map-filter-item">
                    <input type="checkbox" id="filter-homestay" checked>
                    <span>Hiển thị homestay</span>
                </label>
            </div>
            <div id="tourism-map" class="tourism-map"></div>
            <p class="map-note">Dữ liệu bản đồ sử dụng OpenStreetMap và Leaflet, không cần API key.</p>
        </div>
    </div>
</section>

<script>
    window.tourismMarkers = <?= json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<?php require_once 'includes/footer.php'; ?>