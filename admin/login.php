<?php

require_once dirname(__DIR__) . '/includes/config.php';
header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode(ADMIN_URL . '/index.php'));
exit;
