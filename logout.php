<?php

require_once 'includes/config.php';

unset($_SESSION['user_id'], $_SESSION['user_name']);
session_regenerate_id(true);

header('Location: ' . SITE_URL . '/login.php');
exit;
