<?php
session_start();
require_once 'includes/db.php';

// get all posts with author and featured image, newest first
$sql = 'SELECT b.id, b.title, b.content, b.featured_image, b.created_at, u.username
        FROM blogPost b
        JOIN user u ON b.user_id = u.id
        ORDER BY b.created_at DESC';

$result = $conn->query($sql);

$pageTitle = 'BlogSpace';
$metaDesc  = 'Read and share ideas on BlogSpace.';
require_once 'includes/header.php';
?>

<div class="site-banner">
    <h1>Thoughts Worth Reading.</h1>
    <p>A space to write and share ideas with others.</p>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="<?= BASE_URL ?>/pages/register.php" class="btn btn-red">Start Writing</a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>/pages/create.php" class="btn btn-red">New Post</a>
    <?php endif; ?>
</div>

<div class="posts-label">All Posts</div>

<?php if ($result && $result->num_rows > 0): ?>

    <?php while ($post = $result->fetch_assoc()): ?>
        <?php
            $hasImage = !empty($post['featured_image']) && file_exists('uploads/' . $post['featured_image']);
            // strip markdown formatting characters for clean plain text preview
            $cleanPreview = strip_tags($post['content']);
            $cleanPreview = preg_replace('/[#*_`~\[\]\(\)]/', '', $cleanPreview);
        ?>
        <div class="post-item <?= $hasImage ? 'has-thumb' : '' ?>">
            <div class="post-text-content">
                <h3>
                    <a href="<?= BASE_URL ?>/pages/view.php?id=<?= $post['id'] ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h3>
                <div class="post-meta">
                    By <strong><?= htmlspecialchars($post['username']) ?></strong>
                    &nbsp;&middot;&nbsp;
                    <?= date('M d, Y', strtotime($post['created_at'])) ?>
                </div>
                <p class="post-excerpt">
                    <?= htmlspecialchars(substr($cleanPreview, 0, 180)) ?><?= strlen($cleanPreview) > 180 ? '...' : '' ?>
                </p>
                <a href="<?= BASE_URL ?>/pages/view.php?id=<?= $post['id'] ?>" class="read-link">Read more &rarr;</a>
            </div>

            <?php if ($hasImage): ?>
                <div class="post-thumbnail">
                    <a href="<?= BASE_URL ?>/pages/view.php?id=<?= $post['id'] ?>">
                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

<?php else: ?>
    <div class="nothing-here">
        <p>No posts here yet.</p>
        <a href="<?= BASE_URL ?>/pages/<?= isset($_SESSION['user_id']) ? 'create' : 'login' ?>.php" class="btn btn-red">
            <?= isset($_SESSION['user_id']) ? 'Write the first one' : 'Login to post' ?>
        </a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
