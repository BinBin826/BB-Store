<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
session_destroy();
redirect(SITE_URL . '/auth/login.php');
