<?php
session_start();
require __DIR__ . '/includes/demo-data.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (empty($fullName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } else {
        $users = getUsers();
        foreach ($users as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                $error = 'An account with this email already exists.';
                break;
            }
        }

        if (!$error) {
            $newUser = [
                'id' => 0,
                'name' => $fullName,
                'email' => strtolower($email),
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
                'balance' => 0,
                'invested' => 0,
                'profit' => 0,
                'available_withdrawal' => 0,
                'country' => 'Kenya',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $users[] = $newUser;
            saveUsers($users);
            $latestUsers = getUsers();
            $registeredUser = end($latestUsers);
            $_SESSION['user'] = normalizeUserRow([
                'id' => $registeredUser['id'] ?? 0,
                'full_name' => $registeredUser['name'] ?? $fullName,
                'email' => $registeredUser['email'] ?? strtolower($email),
                'phone' => $registeredUser['phone'] ?? $phone,
                'password' => $registeredUser['password'] ?? password_hash($password, PASSWORD_DEFAULT),
                'role' => $registeredUser['role'] ?? 'user',
                'balance' => $registeredUser['balance'] ?? 0,
                'invested' => $registeredUser['invested'] ?? 0,
                'profit' => $registeredUser['profit'] ?? 0,
                'available_withdrawal' => $registeredUser['available_withdrawal'] ?? 0,
                'country' => $registeredUser['country'] ?? 'Kenya',
                'created_at' => $registeredUser['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
            $success = 'Account created successfully.';
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | AQUA INVEST</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-box wide">
        <div class="auth-header">
            <div class="brand small">
                <span class="brand-mark">A</span>
                <span>AQUA INVEST</span>
            </div>
            <h1>Create account</h1>
            <p>Register to start investing with a simulated dashboard.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form multi-column">
            <label>
                Full name
                <input type="text" name="full_name" placeholder="Jane Doe" required>
            </label>
            <label>
                Email address
                <input type="email" name="email" placeholder="you@example.com" required>
            </label>
            <label>
                Phone number
                <input type="tel" name="phone" placeholder="+254 700 000000">
            </label>
            <label>
                Country
                <input type="text" name="country" value="Kenya" readonly>
            </label>
            <label>
                Password
                <input type="password" name="password" placeholder="Create password" required>
            </label>
            <label>
                Confirm password
                <input type="password" name="confirm_password" placeholder="Repeat password" required>
            </label>
            <button class="btn btn-primary full" type="submit">Create Account</button>
        </form>

        <p class="auth-switch">
            Already registered?
            <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>
