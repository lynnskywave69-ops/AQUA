<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AQUA INVEST</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0e27;
            --bg-soft: #1a1f3a;
            --panel: rgba(26, 31, 58, 0.95);
            --panel-alt: rgba(35, 42, 75, 0.98);
            --border: rgba(99, 179, 255, 0.25);
            --primary: #00d4ff;
            --primary-strong: #00a8ff;
            --secondary: #00ff88;
            --text: #f0f4ff;
            --muted: #a8b8d0;
            --shadow: 0 25px 80px rgba(0, 0, 0, 0.45);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1a3f 25%, #2a0845 50%, #1a1a3f 75%, #0a0e27 100%);
            background-attachment: fixed;
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 30px 20px;
        }

        .login-choice-container {
            width: min(100%, 800px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #00d4ff, #00ff88, #ff006e);
            color: #0a0e27;
            font-weight: 800;
            box-shadow: 0 5px 20px rgba(0, 212, 255, 0.4);
        }

        .login-header h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            margin: 0 0 12px;
            background: linear-gradient(135deg, #00d4ff, #00ff88, #ff006e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-header p {
            color: var(--muted);
            font-size: 1.1rem;
        }

        .login-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .login-card {
            background: linear-gradient(135deg, rgba(26, 31, 58, 0.8), rgba(35, 42, 75, 0.7));
            border: 1px solid rgba(0, 212, 255, 0.25);
            border-radius: 24px;
            padding: 32px 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .login-card:hover {
            border-color: rgba(0, 212, 255, 0.5);
            box-shadow: 0 0 40px rgba(0, 212, 255, 0.2);
            transform: translateY(-8px);
        }

        .login-card.admin {
            border-color: rgba(255, 71, 87, 0.3);
        }

        .login-card.admin:hover {
            border-color: rgba(255, 71, 87, 0.6);
            box-shadow: 0 0 40px rgba(255, 71, 87, 0.2);
        }

        .login-icon {
            font-size: 3.5rem;
            margin-bottom: 16px;
        }

        .login-card h2 {
            margin: 0 0 12px;
            font-size: 1.6rem;
        }

        .login-card p {
            color: var(--muted);
            margin: 0 0 24px;
            line-height: 1.6;
            flex-grow: 1;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-customer {
            background: linear-gradient(135deg, #00d4ff, #00ff88);
            color: #0a0e27;
            box-shadow: 0 12px 35px rgba(0, 212, 255, 0.5);
        }

        .btn-customer:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 45px rgba(0, 212, 255, 0.6);
        }

        .btn-admin {
            background: linear-gradient(135deg, #ff4757, #ff006e);
            color: #fff;
            box-shadow: 0 12px 35px rgba(255, 71, 87, 0.5);
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 45px rgba(255, 71, 87, 0.6);
        }

        .footer-links {
            text-align: center;
            color: var(--muted);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
            margin: 0 12px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="login-choice-container">
        <div class="login-header">
            <div class="brand">
                <span class="brand-mark">A</span>
                <span>AQUA INVEST</span>
            </div>
            <h1>Choose Login Type</h1>
            <p>Select whether you're a customer or an administrator</p>
        </div>

        <div class="login-options">
            <a href="customer-login.php" class="login-card">
                <div class="login-icon">👤</div>
                <h2>Customer Login</h2>
                <p>Access your investment dashboard, manage your portfolio, and track your returns.</p>
                <button class="btn btn-customer">Login as Customer</button>
            </a>

            <a href="admin-login.php" class="login-card admin">
                <div class="login-icon">⚙️</div>
                <h2>Admin Login</h2>
                <p>Manage users, monitor investments, track transactions, and oversee the system.</p>
                <button class="btn btn-admin">Login as Admin</button>
            </a>
        </div>

        <div class="footer-links">
            <p>
                Don't have an account? <a href="register.php">Create one</a>
                <br>
                <a href="index.html">← Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>
