<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Cột 1: Giới thiệu -->
            <div class="footer-col">
                <h3>Du lịch Vân Hồ</h3>
                <p>Khám phá vẻ đẹp thiên nhiên hoang sơ và văn hóa đặc sắc của đồng bào các dân tộc tại xã Vân Hồ, tỉnh Sơn La.</p>
                <div class="social-links">
                    <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            <!-- Cột 2: Liên kết nhanh -->
            <div class="footer-col">
                <h3>Liên kết nhanh</h3>
                <ul>
                    <li><a href="<?= SITE_URL ?>"><i class="fas fa-chevron-right"></i> Trang chủ</a></li>
                    <li><a href="<?= SITE_URL ?>/places.php"><i class="fas fa-chevron-right"></i> Địa điểm du lịch</a></li>
                    <li><a href="<?= SITE_URL ?>/foods.php"><i class="fas fa-chevron-right"></i> Ẩm thực đặc trưng</a></li>
                    <li><a href="<?= SITE_URL ?>/homestays.php"><i class="fas fa-chevron-right"></i> Homestay</a></li>
                    <li><a href="<?= SITE_URL ?>/map.php"><i class="fas fa-chevron-right"></i> Bản đồ du lịch</a></li>
                    <li><a href="<?= SITE_URL ?>/news.php"><i class="fas fa-chevron-right"></i> Tin tức</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php"><i class="fas fa-chevron-right"></i> Liên hệ</a></li>
                </ul>
            </div>

            <!-- Cột 3: Thông tin liên hệ -->
            <div class="footer-col">
                <h3>Liên hệ</h3>
                <div class="footer-contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>UBND xã Vân Hồ, tỉnh Sơn La</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fas fa-phone"></i>
                    <span>0212 223 478</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>dulich@vanho.sonla.gov.vn</span>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; 2026 Du lịch Vân Hồ - Sơn La. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" aria-label="Về đầu trang">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close">&times;</button>
    <img id="lightbox-img" src="" alt="Ảnh phóng to">
</div>

<!-- SwiperJS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Main JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>

<?php
$footerCurrentPage = basename($_SERVER['PHP_SELF'], '.php');
$footerSafePageName = preg_replace('/[^a-z0-9\-]/i', '', $footerCurrentPage);
$publicPageJsRel = '/assets/js/pages/' . $footerSafePageName . '.js';
$publicPageJsAbs = __DIR__ . '/../assets/js/pages/' . $footerSafePageName . '.js';
?>

<?php
if (isset($pageScripts) && is_array($pageScripts)):
    foreach ($pageScripts as $scriptPath):
        $scriptPath = (string)$scriptPath;
        if ($scriptPath !== '' && (str_starts_with($scriptPath, '/') || preg_match('#^https?://#i', $scriptPath))):
            $scriptSrc = str_starts_with($scriptPath, '/') ? (SITE_URL . $scriptPath) : $scriptPath;
?>
            <script src="<?= $scriptSrc ?>"></script>
<?php
        endif;
    endforeach;
endif;
?>

<?php if ($footerSafePageName !== '' && file_exists($publicPageJsAbs)): ?>
    <script src="<?= SITE_URL . $publicPageJsRel ?>"></script>
<?php endif; ?>

</body>

</html>