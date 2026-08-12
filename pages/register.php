<?php
session_start();
require_once '../includes/db.php';

// redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'That email address doesn\'t look right.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // check if username or email is already taken
        $stmt = $conn->prepare('SELECT id FROM user WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'That username or email is already registered.';
            $stmt->close();
        } else {
            $stmt->close();

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare('INSERT INTO user (username, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $username, $email, $hashed);

            if ($stmt->execute()) {
                $success = 'Account created. You can now <a href="login.php">log in</a>.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        }
    }
}

$pageTitle = 'Register - BlogSpace';
$metaDesc  = 'Create a BlogSpace account.';
require_once '../includes/header.php';
?>

<div class="auth-box">
    <h2>Create an Account</h2>
    <p class="auth-sub">Join BlogSpace and start writing.</p>

    <?php if ($error): ?>
        <div class="msg-err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="msg-ok"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" onsubmit="return validateRegister()">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               placeholder="Pick a username" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="your@email.com" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="At least 6 characters" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password"
               placeholder="Repeat password" required>

        <div class="form-actions">
            <button type="submit" class="btn btn-red">Create Account</button>
        </div>
    </form>

    <p class="auth-alt">Already have an account? <a href="login.php">Log in</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>
