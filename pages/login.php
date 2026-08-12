<?php
session_start();
require_once '../includes/db.php';

// already logged in, send them home
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare('SELECT id, username, password, role FROM user WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                header('Location: ' . BASE_URL . '/index.php');
                exit;
            } else {
                $error = 'Incorrect username or password.';
            }
        } else {
            $error = 'Incorrect username or password.';
        }

        $stmt->close();
    }
}

$pageTitle = 'Login - BlogSpace';
$metaDesc  = 'Log in to your BlogSpace account.';
require_once '../includes/header.php';
?>

<div class="auth-box">
    <h2>Welcome Back</h2>
    <p class="auth-sub">Log in to your BlogSpace account.</p>

    <?php if ($error): ?>
        <div class="msg-err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" onsubmit="return validateLogin()">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               placeholder="Your username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Your password" required>

        <div class="form-actions">
            <button type="submit" class="btn btn-red">Log In</button>
        </div>
    </form>

    <p class="auth-alt">Don't have an account? <a href="register.php">Register</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>
