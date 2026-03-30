<?php

require_once 'includes/config.php';

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['admin_id'], $_SESSION['admin_username']);
session_regenerate_id(true);

header('Location: ' . SITE_URL . '/login.php');
exit;
