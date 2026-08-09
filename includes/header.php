<?php
$title = isset($pageTitle) ? $pageTitle : 'BlogSpace';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= isset($metaDesc) ? htmlspecialchars($metaDesc) : 'A simple blog built with PHP and MySQL.' ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="nav-wrap">
        <a href="<?= BASE_URL ?>/index.php" class="site-logo">Blog<span>Space</span></a>
        <nav class="nav-links">
            <a href="<?= BASE_URL ?>/index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= BASE_URL ?>/pages/create.php" class="write-link">New Post</a>
                <span class="nav-user"><?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="<?= BASE_URL ?>/pages/logout.php">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/pages/login.php">Login</a>
                <a href="<?= BASE_URL ?>/pages/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="wrap">
