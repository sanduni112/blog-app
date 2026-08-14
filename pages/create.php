<?php
session_start();
require_once '../includes/db.php';

// must be logged in to write
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $user_id = (int)$_SESSION['user_id'];
    $featured_image = null;

    if (empty($title) || empty($content)) {
        $error = 'Title and content are both required.';
    } elseif (strlen($title) > 255) {
        $error = 'Title is too long (max 255 characters).';
    } else {
        // handle featured image upload
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
                $uploadDir   = dirname(__DIR__) . '/uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $destPath = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmp, $destPath)) {
                    $featured_image = $newFileName;
                } else {
                    $error = 'Failed to upload image. Please try again.';
                }
            }
        }

        // if no upload errors, insert post
        if (empty($error)) {
            $stmt = $conn->prepare('INSERT INTO blogPost (user_id, title, content, featured_image) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('isss', $user_id, $title, $content, $featured_image);

            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                $stmt->close();
                header('Location: ' . BASE_URL . '/pages/view.php?id=' . $new_id);
                exit;
            } else {
                $error = 'Could not save the post. Please try again.';
                $stmt->close();
            }
        }
    }
}

$pageTitle = 'New Post - BlogSpace';
require_once '../includes/header.php';
?>

<!-- EasyMDE Markdown Editor -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>

<a href="<?= BASE_URL ?>/index.php" class="back-link">&larr; Back to posts</a>

<div class="post-form-box">
    <h2>Write a New Post</h2>

    <?php if ($error): ?>
        <div class="msg-err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="create.php" enctype="multipart/form-data" onsubmit="return validatePost()">
        <label for="title">Title</label>
        <input type="text" id="title" name="title"
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
               placeholder="Give your post a title" required>

        <label for="featured_image">Featured Image (Optional)</label>
        <input type="file" id="featured_image" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp">
        <span class="field-hint">Supported formats: JPG, PNG, GIF, WEBP (Max 5MB)</span>

        <label for="content">Content (Markdown Supported)</label>
        <textarea id="content" name="content"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>

        <div class="form-actions">
            <button type="submit" class="btn btn-red">Publish</button>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-plain">Cancel</a>
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
