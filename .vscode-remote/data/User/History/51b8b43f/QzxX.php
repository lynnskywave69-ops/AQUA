<?php
session_start();
$activeUser = $_SESSION['user'] ?? null;
if ($activeUser) {
    $target = $activeUser['role'] === 'admin' ? 'admin.php' : 'dashboard.php';
    header('Location: ' . $target);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AQUA INVEST</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="landing-page">
    <div class="page-shell">
        <header class="topbar">
            <div class="brand">
                <span class="brand-mark">A</span>
                <span>AQUA INVEST</span>
            </div>
            <nav class="nav-actions">
                <a href="login.php">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            </nav>
        </header>

        <main class="hero">
            <section class="hero-copy">
                <p class="eyebrow">Smart capital growth</p>
                <h1>Build wealth with disciplined, transparent investment plans.</h1>
                <p class="lead">
                    AQUA INVEST helps users track balances, manage deposits, plan returns, and monitor portfolio growth.
                    Secure investment platform with real-time tracking and transparent portfolio management.
                </p>
                <div class="hero-actions">
                    <a href="register.php" class="btn btn-primary">Open Account</a>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                </div>
                <ul class="mini-stats">
                    <li><strong>7</strong><span>Plans</span></li>
                    <li><strong>24/7</strong><span>Tracking</span></li>
                    <li><strong>Secure</strong><span>Bank-grade security</span></li>
                </ul>
            </section>

            <section class="hero-card">
                <div class="glass-panel">
                    <p class="card-label">Sample dashboard</p>
                    <div class="metric-row">
                        <div>
                            <span>Total Balance</span>
                            <strong>KSh 248,400</strong>
                        </div>
                        <div class="trend up">+18.4%</div>
                    </div>
                    <div class="mini-chart">
                        <span style="height: 30%"></span>
                        <span style="height: 45%"></span>
                        <span style="height: 55%"></span>
                        <span style="height: 75%"></span>
                        <span style="height: 90%"></span>
                        <span style="height: 100%"></span>
                    </div>
                    <div class="plan-list">
                        <div><span>KSh 300 → KSh 20</span><strong>25 days</strong></div>
                        <div><span>KSh 700 → KSh 40</span><strong>30 days</strong></div>
                        <div><span>KSh 1.2K → KSh 50</span><strong>60 days</strong></div>
                        <div><span>KSh 2.5K → KSh 100</span><strong>90 days</strong></div>
                        <div><span>KSh 4.8K → KSh 200</span><strong>130 days</strong></div>
                        <div><span>KSh 9K → KSh 390</span><strong>180 days</strong></div>
                        <div><span>KSh 18K → KSh 800</span><strong>210 days</strong></div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
