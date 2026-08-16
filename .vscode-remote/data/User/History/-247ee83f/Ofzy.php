<?php
session_start();
require __DIR__ . '/includes/demo-data.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    foreach (getUsers() as $user) {
        if (strtolower($user['email']) === strtolower($email) && password_verify($password, $user['password'])) {
            // Only allow customers (role = 'user')
            if ($user['role'] === 'user') {
                $_SESSION['user'] = $user;
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Admin accounts cannot login here. Use Admin Login.';
                break;
            }
        }
    }

    if (!$error) {
        $error = 'Invalid email or password.';
    }
}

if (!empty($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'user') {
        header('Location: dashboard.php');
    } else {
        header('Location: admin-login.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login | AQUA INVEST</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-box">
        <div class="auth-header">
            <div class="brand small">
                <span class="brand-mark">A</span>
                <span>AQUA INVEST</span>
            </div>
            <h1>Customer Login</h1>
            <p>Access your investment dashboard and manage your portfolio.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <label>
                Email address
                <input type="email" name="email" placeholder="you@example.com" required>
            </label>
            <label>
                Password
                <input type="password" name="password" placeholder="Enter password" required>
            </label>
            <button class="btn btn-primary full" type="submit">Login</button>
        </form>

        <p class="auth-switch">
            Don't have an account?
            <a href="register.php">Create one</a>
        </p>

        <p class="auth-switch" style="margin-top: 16px; border-top: 1px solid rgba(0, 212, 255, 0.1); padding-top: 16px;">
            Looking for admin login?
            <a href="admin-login.php">Admin Login</a>
        </p>
    </div>
</body>
</html>
