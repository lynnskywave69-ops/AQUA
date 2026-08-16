<?php
session_start();
require __DIR__ . '/includes/demo-data.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    foreach (getUsers() as $user) {
        if (strtolower($user['email']) === strtolower($email) && password_verify($password, $user['password'])) {
            // Only allow admin users
            if ($user['role'] === 'admin') {
                $_SESSION['user'] = $user;
                header('Location: admin.php');
                exit;
            } else {
                $error = 'This account is not an administrator. Use Customer Login.';
                break;
            }
        }
    }

    if (!$error) {
        $error = 'Invalid email or password.';
    }
}

if (!empty($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: customer-login.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | AQUA INVEST</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-box">
        <div class="auth-header">
            <div class="brand small">
                <span class="brand-mark">A</span>
                <span>AQUA INVEST</span>
            </div>
            <h1>Admin Login</h1>
            <p>Access the admin panel to manage the system.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <label>
                Admin Email
                <input type="email" name="email" placeholder="admin@aquainvest.com" required>
            </label>
            <label>
                Password
                <input type="password" name="password" placeholder="Enter admin password" required>
            </label>
            <button class="btn btn-primary full" type="submit">Login as Admin</button>
        </form>

        <p class="auth-switch">
            Not an admin?
            <a href="customer-login.php">Customer Login</a>
        </p>

        <div style="margin-top: 20px; padding: 14px 16px; background: rgba(255, 165, 2, 0.1); border: 1px solid rgba(255, 165, 2, 0.3); border-radius: 10px; color: #ffa502; font-size: 0.9em;">
            <strong>⚠️ Admin Access Only:</strong> This page is restricted to administrator accounts only. If you don't have admin credentials, please contact your system administrator.
        </div>
    </div>
</body>
</html>
