<?php
session_start();
require_once '../includes/db.php';

// destroy session and go to login
session_unset();
session_destroy();

header('Location: ' . BASE_URL . '/pages/login.php');
exit;
