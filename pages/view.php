<?php
session_start();
require_once '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// get post with author name
$stmt = $conn->prepare(
    'SELECT b.id, b.title, b.content, b.featured_image, b.created_at, b.updated_at, b.user_id, u.username
     FROM blogPost b
     JOIN user u ON b.user_id = u.id
     WHERE b.id = ?'
);
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

// is the logged-in user the one who wrote this
$isAuthor = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$post['user_id'];

$pageTitle = htmlspecialchars($post['title']) . ' - BlogSpace';
$metaDesc  = substr(strip_tags($post['content']), 0, 155);
require_once '../includes/header.php';
?>

<!-- Markdown Parser & Sanitizer CDN -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>

<a href="<?= BASE_URL ?>/index.php" class="back-link">&larr; Back to posts</a>

<div class="post-detail">

    <h2><?= htmlspecialchars($post['title']) ?></h2>

    <div class="post-detail-meta">
        By <strong><?= htmlspecialchars($post['username']) ?></strong>
        &nbsp;&middot;&nbsp;
        <?= date('M d, Y', strtotime($post['created_at'])) ?>
        <?php if ($post['updated_at'] !== $post['created_at']): ?>
            &nbsp;&middot;&nbsp; edited <?= date('M d, Y', strtotime($post['updated_at'])) ?>
        <?php endif; ?>
    </div>

    <!-- Featured Image if available -->
    <?php if (!empty($post['featured_image']) && file_exists(dirname(__DIR__) . '/uploads/' . $post['featured_image'])): ?>
        <div class="post-hero-img">
            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
        </div>
    <?php endif; ?>

    <!-- Rendered Markdown Container -->
    <div id="post-content" class="post-body"></div>

    <!-- Hidden Raw Markdown Data -->
    <textarea id="raw-markdown" style="display:none;"><?= htmlspecialchars($post['content']) ?></textarea>

    <?php if ($isAuthor): ?>
        <div class="post-controls">
            <a href="edit.php?id=<?= $post['id'] ?>" class="btn btn-dark">Edit</a>
            <form method="POST" action="delete.php" style="display:inline;"
                  onsubmit="return confirmDelete()">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <button type="submit" class="btn btn-red">Delete</button>
            </form>
        </div>
    <?php endif; ?>

</div>

<script>
    // render markdown safely
    var rawText = document.getElementById('raw-markdown').value;
    var parsedHtml = marked.parse(rawText);
    document.getElementById('post-content').innerHTML = DOMPurify.sanitize(parsedHtml);
</script>

<?php require_once '../includes/footer.php'; ?>
