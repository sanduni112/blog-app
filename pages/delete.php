<?php
session_start();
require_once '../includes/db.php';

// check session
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

// only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$user_id = (int)$_SESSION['user_id'];

if ($post_id <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// fetch featured image to delete file from disk
$stmt = $conn->prepare('SELECT featured_image FROM blogPost WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $imageFile = $row['featured_image'];
    $stmt->close();

    // delete post record
    $stmt = $conn->prepare('DELETE FROM blogPost WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $post_id, $user_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        // remove image file from disk if exists
        if (!empty($imageFile)) {
            $filePath = dirname(__DIR__) . '/uploads/' . $imageFile;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
} else {
    $stmt->close();
}

// if unauthorized or not found
http_response_code(403);
$pageTitle = 'Delete Failed - BlogSpace';
require_once '../includes/header.php';
echo '<div class="nothing-here">
      <p>You cannot delete this post or it does not exist.</p>
      <a href="' . BASE_URL . '/index.php" class="btn btn-plain">Back to Home</a></div>';
require_once '../includes/footer.php';
exit;
