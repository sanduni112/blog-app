<?php
session_start();
require_once '../includes/db.php';

// check session
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// fetch the post
$stmt = $conn->prepare('SELECT id, user_id, title, content, featured_image FROM blogPost WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $pageTitle = 'Not Found - BlogSpace';
    require_once '../includes/header.php';
    echo '<div class="nothing-here"><p>Post not found.</p>
          <a href="' . BASE_URL . '/index.php" class="btn btn-plain">Back to Home</a></div>';
    require_once '../includes/footer.php';
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();

// only post author can edit
if ((int)$post['user_id'] !== (int)$_SESSION['user_id']) {
    http_response_code(403);
    $pageTitle = 'Access Denied - BlogSpace';
    require_once '../includes/header.php';
    echo '<div class="nothing-here">
          <p>You do not have permission to edit this post.</p>
          <a href="' . BASE_URL . '/index.php" class="btn btn-plain">Back to Home</a></div>';
    require_once '../includes/footer.php';
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] == '1';
    $featured_image = $post['featured_image'];
    $uploadDir = dirname(__DIR__) . '/uploads/';

    if (empty($title) || empty($content)) {
        $error = 'Title and content cannot be empty.';
    } elseif (strlen($title) > 255) {
        $error = 'Title is too long (max 255 characters).';
    } else {
        // check if new image was uploaded
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmp  = $_FILES['featured_image']['tmp_name'];
            $fileName = $_FILES['featured_image']['name'];
            $fileSize = $_FILES['featured_image']['size'];
            $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($fileExt, $allowed)) {
                $error = 'Only JPG, JPEG, PNG, GIF, and WEBP image files are allowed.';
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $error = 'Image file size cannot exceed 5MB.';
            } else {
                $newFileName = uniqid('post_', true) . '.' . $fileExt;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    // remove old image file if exists
                    if (!empty($post['featured_image']) && file_exists($uploadDir . $post['featured_image'])) {
                        unlink($uploadDir . $post['featured_image']);
                    }
                    $featured_image = $newFileName;
                } else {
                    $error = 'Failed to upload image. Please try again.';
                }
            }
        } elseif ($remove_image) {
            // remove existing image
            if (!empty($post['featured_image']) && file_exists($uploadDir . $post['featured_image'])) {
                unlink($uploadDir . $post['featured_image']);
            }
            $featured_image = null;
        }

        if (empty($error)) {
            // update with prepared statement including ownership check
            $stmt = $conn->prepare('UPDATE blogPost SET title = ?, content = ?, featured_image = ? WHERE id = ? AND user_id = ?');
            $stmt->bind_param('sssii', $title, $content, $featured_image, $id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $stmt->close();
                header('Location: ' . BASE_URL . '/pages/view.php?id=' . $id);
                exit;
            } else {
                $error = 'Failed to update the post. Please try again.';
                $stmt->close();
            }
        }
    }

    $post['title']   = $title;
    $post['content'] = $content;
    $post['featured_image'] = $featured_image;
}

$pageTitle = 'Edit Post - BlogSpace';
require_once '../includes/header.php';
?>

<!-- EasyMDE Markdown Editor -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>

<a href="<?= BASE_URL ?>/pages/view.php?id=<?= $id ?>" class="back-link">&larr; Back to post</a>

<div class="post-form-box">
    <h2>Edit Post</h2>

    <?php if ($error): ?>
        <div class="msg-err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data" onsubmit="return validatePost()">
        <label for="title">Title</label>
        <input type="text" id="title" name="title"
               value="<?= htmlspecialchars($post['title']) ?>" required>

        <label for="featured_image">Featured Image</label>
        <?php if (!empty($post['featured_image']) && file_exists(dirname(__DIR__) . '/uploads/' . $post['featured_image'])): ?>
            <div class="current-img-preview">
                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($post['featured_image']) ?>" alt="Current featured image">
                <label class="checkbox-label">
                    <input type="checkbox" name="remove_image" value="1"> Remove current image
                </label>
            </div>
        <?php endif; ?>

        <input type="file" id="featured_image" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp">
        <span class="field-hint">Upload a new image to replace current one (Max 5MB)</span>

        <label for="content">Content (Markdown Supported)</label>
        <textarea id="content" name="content"><?= htmlspecialchars($post['content']) ?></textarea>

        <div class="form-actions">
            <button type="submit" class="btn btn-red">Save Changes</button>
            <a href="<?= BASE_URL ?>/pages/view.php?id=<?= $id ?>" class="btn btn-plain">Cancel</a>
        </div>
    </form>
</div>

<script>
    // initialize markdown editor
    var easyMDE = new EasyMDE({
        element: document.getElementById('content'),
        spellChecker: false,
        placeholder: 'Write your post in Markdown... (e.g. # Heading, **bold**, *italic*, - list, ```code```)',
        status: false,
        toolbar: ['bold', 'italic', 'heading', '|', 'quote', 'unordered-list', 'ordered-list', '|', 'link', 'code', '|', 'preview', 'side-by-side', 'fullscreen']
    });
</script>

<?php require_once '../includes/footer.php'; ?>
