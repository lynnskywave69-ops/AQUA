<?php
session_start();
require __DIR__ . '/includes/demo-data.php';

$message = '';
$messageType = '';

// Check if any admin exists
$users = getUsers();
$adminExists = false;
foreach ($users as $user) {
    if ($user['role'] === 'admin') {
        $adminExists = true;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminKey = trim($_POST['admin_key'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Simple admin key for first admin setup
    $VALID_ADMIN_KEY = 'AQUA_ADMIN_2024';

    if ($adminKey !== $VALID_ADMIN_KEY) {
        $message = '❌ Invalid admin key.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = '❌ Passwords do not match.';
        $messageType = 'error';
    } elseif (empty($fullName) || empty($email) || empty($password)) {
        $message = '❌ Please fill in all required fields.';
        $messageType = 'error';
    } else {
        // Check if email already exists
        $users = getUsers();
        $emailExists = false;
        foreach ($users as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                $emailExists = true;
                break;
            }
        }

        if ($emailExists) {
            $message = '❌ An account with this email already exists.';
            $messageType = 'error';
        } else {
            // Create admin account
            $newAdmin = [
                'id' => count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1,
                'name' => $fullName,
                'email' => strtolower($email),
                'phone' => '',
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'admin',
                'balance' => 0,
                'invested' => 0,
                'profit' => 0,
                'available_withdrawal' => 0,
                'country' => 'Kenya',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $users[] = $newAdmin;
            saveUsers($users);

            $message = '✅ Admin account created successfully! You can now <a href="admin-login.php">login as admin</a>.';
            $messageType = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account | AQUA INVEST</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-box wide">
        <div class="auth-header">
            <div class="brand small">
                <span class="brand-mark">A</span>
                <span>AQUA INVEST</span>
            </div>
            <h1>Create Admin Account</h1>
            <p>Set up an administrator account for system management.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!$adminExists): ?>
            <form method="POST" class="auth-form multi-column">
                <label style="grid-column: 1 / -1;">
                    Admin Authorization Key
                    <input type="password" name="admin_key" placeholder="Enter admin key" required>
                </label>
                <label>
                    Full name
                    <input type="text" name="full_name" placeholder="Admin Name" required>
                </label>
                <label>
                    Email address
                    <input type="email" name="email" placeholder="admin@example.com" required>
                </label>
                <label>
                    Password
                    <input type="password" name="password" placeholder="Create password" required>
                </label>
                <label>
                    Confirm password
                    <input type="password" name="confirm_password" placeholder="Repeat password" required>
                </label>
                <button class="btn btn-primary full" type="submit" style="grid-column: 1 / -1;">Create Admin Account</button>
            </form>

            <div style="margin-top: 20px; background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.2); border-radius: 10px; padding: 14px; color: #a8b8d0; font-size: 0.9em;">
                <strong style="color: #00d4ff;">ℹ️ Admin Key:</strong> You need the admin authorization key to create an admin account. This is provided during system setup.
            </div>
        <?php else: ?>
            <div style="background: rgba(75, 227, 163, 0.1); border: 1px solid rgba(75, 227, 163, 0.3); border-radius: 12px; padding: 24px; text-align: center;">
                <p style="color: #4be3a3; font-size: 1.1rem; margin: 0;">
                    ✅ An admin account already exists.
                </p>
                <p style="color: #a8b8d0; margin: 12px 0 0;">
                    <a href="admin-login.php" class="btn btn-primary" style="display: inline-flex; margin-top: 12px;">Go to Admin Login</a>
                </p>
            </div>
        <?php endif; ?>

        <p class="auth-switch">
            <a href="index.html">← Back to Home</a>
        </p>
    </div>
</body>
</html>
