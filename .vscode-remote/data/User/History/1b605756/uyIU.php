<?php
session_start();
require __DIR__ . '/includes/demo-data.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    foreach (getUsers() as $user) {
        if (strtolower($user['email']) === strtolower($email) && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $redirect = $user['role'] === 'admin' ? 'admin.php' : 'dashboard.php';
            header('Location: ' . $redirect);
            exit;
        }
    }

    $error = 'Invalid email or password.';
}

if (!empty($_SESSION['user'])) {
    $redirect = $_SESSION['user']['role'] === 'admin' ? 'admin.php' : 'dashboard.php';
    header('Location: ' . $redirect);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AQUA INVEST</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-box">
        <div class="auth-header">
            <div class="brand small">
                <span class="brand-mark">A</span>
                <span>AQUA INVEST</span>
            </div>
            <h1>Welcome back</h1>
            <p>Login to continue to your dashboard.</p>
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


    </div>
</body>
</html>
