<?php
session_start();
require __DIR__ . '/includes/demo-data.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $adminEmail = 'lynnskywave69@gmail.com';
    $adminPassword = '6E9l7i6sha@';

    if (strtolower($email) === strtolower($adminEmail) && $password === $adminPassword) {
        $adminUser = null;
        foreach (getUsers() as $user) {
            if (strtolower($user['email']) === strtolower($adminEmail)) {
                $adminUser = $user;
                break;
            }
        }

        if (!$adminUser) {
            $adminUser = normalizeUserRow([
                'id' => 1,
                'full_name' => 'Admin',
                'email' => $adminEmail,
                'phone' => '+254700000000',
                'password' => password_hash($adminPassword, PASSWORD_DEFAULT),
                'role' => 'admin',
                'balance' => 0,
                'invested' => 0,
                'profit' => 0,
                'available_withdrawal' => 0,
                'country' => 'Kenya',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $users = getUsers();
            $users[] = $adminUser;
            saveUsers($users);
        }

        $_SESSION['user'] = $adminUser;
        $_SESSION['user']['role'] = 'admin';
        header('Location: admin.php');
        exit;
    }

    foreach (getUsers() as $user) {
        if (strtolower($user['email']) === strtolower($email) && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: dashboard.php');
            exit;
        }
    }

    $error = 'Invalid email or password.';
}

if (!empty($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: dashboard.php');
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
            Admin access uses the configured admin credentials.
        </p>
    </div>
</body>
</html>
