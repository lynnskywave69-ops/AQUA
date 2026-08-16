<?php
session_start();
require __DIR__ . '/includes/demo-data.php';
requireAuth();

$currentUser = $_SESSION['user'];
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'deposit') {
        $amount = (float) ($_POST['amount'] ?? 0);
        if ($amount > 0) {
            $currentUser['balance'] += $amount;
            $currentUser['available_withdrawal'] = $currentUser['balance'] - $currentUser['invested'];
            $currentUser['profit'] = max(0, $currentUser['profit']);
            addTransaction($currentUser['id'], 'deposit', $amount, 'Payment received');
            $successMessage = 'Deposit recorded successfully.';
        } else {
            $errorMessage = 'Enter a valid deposit amount.';
        }
    }

    if ($action === 'invest') {
        $plan = $_POST['plan'] ?? 'Plan 1';
        $amount = (float) ($_POST['amount'] ?? 0);
        
        // Define investment plan parameters
        $investmentPlans = [
            'Plan 1' => ['min' => 300, 'profit' => 20, 'days' => 25],
            'Plan 2' => ['min' => 700, 'profit' => 40, 'days' => 30],
            'Plan 3' => ['min' => 1200, 'profit' => 50, 'days' => 60],
            'Plan 4' => ['min' => 2500, 'profit' => 100, 'days' => 90],
            'Plan 5' => ['min' => 4800, 'profit' => 200, 'days' => 130],
            'Plan 6' => ['min' => 9000, 'profit' => 390, 'days' => 180],
            'Plan 7' => ['min' => 18000, 'profit' => 800, 'days' => 210]
        ];
        
        $planDetails = $investmentPlans[$plan] ?? null;
        
        if (!$planDetails) {
            $errorMessage = 'Invalid investment plan selected.';
        } elseif ($amount < $planDetails['min']) {
            $errorMessage = 'Minimum investment for this plan is KSh ' . number_format($planDetails['min'], 2) . '.';
        } elseif ($amount > $currentUser['balance']) {
            $errorMessage = 'Insufficient balance.';
        } else {
            $currentUser['balance'] -= $amount;
            $currentUser['invested'] += $amount;
            $currentUser['profit'] += $planDetails['profit'];
            $currentUser['available_withdrawal'] = max(0, $currentUser['balance']);
            // Store rate as percentage for display (profit/amount * 100)
            $ratePercent = ($planDetails['profit'] / $amount) * 100;
            addInvestment($currentUser['id'], $plan, $amount, $ratePercent);
            addTransaction($currentUser['id'], 'investment', $amount, 'Invested in ' . $plan . ' - KSh ' . $planDetails['profit'] . ' profit for ' . $planDetails['days'] . ' days');
            $successMessage = 'Investment successful! Expected profit: KSh ' . number_format($planDetails['profit'], 2) . ' in ' . $planDetails['days'] . ' days.';
        }
    }

    if ($action === 'withdraw') {
        $amount = (float) ($_POST['amount'] ?? 0);
        if ($amount > 0 && $amount <= $currentUser['available_withdrawal']) {
            $currentUser['balance'] -= $amount;
            $currentUser['available_withdrawal'] -= $amount;
            addTransaction($currentUser['id'], 'withdrawal', $amount, 'Withdrawal request processed');
            $successMessage = 'Withdrawal request processed successfully.';
        } else {
            $errorMessage = 'Withdrawal amount exceeds the available balance.';
        }
    }

    if ($action === 'profile') {
        $currentUser['name'] = trim($_POST['name'] ?? $currentUser['name']);
        $currentUser['phone'] = trim($_POST['phone'] ?? $currentUser['phone']);
        $currentUser['country'] = trim($_POST['country'] ?? $currentUser['country']);
        $successMessage = 'Profile updated successfully.';
    }

    updateUserInList($currentUser);
    $_SESSION['user'] = $currentUser;
}

$plans = [
    ['id' => 'Plan 1', 'min' => 300, 'profit' => 20, 'days' => 25, 'description' => 'Invest KSh 300 → Earn KSh 20'],
    ['id' => 'Plan 2', 'min' => 700, 'profit' => 40, 'days' => 30, 'description' => 'Invest KSh 700 → Earn KSh 40'],
    ['id' => 'Plan 3', 'min' => 1200, 'profit' => 50, 'days' => 60, 'description' => 'Invest KSh 1,200 → Earn KSh 50'],
    ['id' => 'Plan 4', 'min' => 2500, 'profit' => 100, 'days' => 90, 'description' => 'Invest KSh 2,500 → Earn KSh 100'],
    ['id' => 'Plan 5', 'min' => 4800, 'profit' => 200, 'days' => 130, 'description' => 'Invest KSh 4,800 → Earn KSh 200'],
    ['id' => 'Plan 6', 'min' => 9000, 'profit' => 390, 'days' => 180, 'description' => 'Invest KSh 9,000 → Earn KSh 390'],
    ['id' => 'Plan 7', 'min' => 18000, 'profit' => 800, 'days' => 210, 'description' => 'Invest KSh 18,000 → Earn KSh 800']
];

$transactions = getTransactionsForUser($currentUser['id']);
$investments = getInvestmentsForUser($currentUser['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | AQUA INVEST</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script defer src="assets/js/script.js"></script>
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand sidebar-brand">
                <span class="brand-mark">A</span>
                <span>AQUA INVEST</span>
            </div>

            <nav class="side-nav">
                <a href="#overview" class="active">💰 Balance</a>
                <a href="#plans">📈 Plans</a>
                <a href="#account">👤 Account</a>
                <a href="#history">🧾 History</a>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="admin.php" class="admin-link">⚙️ Admin</a>
                <?php endif; ?>
                <a href="logout.php">🚪 Logout</a>
            </nav>
        </aside>

        <main class="dashboard-main">
            <header class="top-header">
                <div>
                    <p class="eyebrow">Dashboard</p>
                    <h1>Welcome, <?php echo htmlspecialchars($currentUser['name']); ?></h1>
                </div>
                <div class="header-actions">
                    <span class="status-pill">Live Account</span>
                </div>
            </header>

            <?php if ($successMessage): ?>
                <div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <section id="overview" class="stats-grid">
                <article class="stat-card highlight">
                    <span>Total Balance</span>
                    <strong>KSh <?php echo number_format((float) $currentUser['balance'], 2); ?></strong>
                    <small>Available cash</small>
                </article>
                <article class="stat-card">
                    <span>Total Invested</span>
                    <strong>KSh <?php echo number_format((float) $currentUser['invested'], 2); ?></strong>
                    <small>Capital allocated</small>
                </article>
                <article class="stat-card">
                    <span>Total Profit</span>
                    <strong>KSh <?php echo number_format((float) $currentUser['profit'], 2); ?></strong>
                    <small>Projected return</small>
                </article>
                <article class="stat-card">
                    <span>Available Withdrawal</span>
                    <strong>KSh <?php echo number_format((float) $currentUser['available_withdrawal'], 2); ?></strong>
                    <small>Withdrawable amount</small>
                </article>
            </section>

            <section id="plans" class="content-grid two-columns">
                <div class="panel">
                    <div class="panel-header">
                        <h2>Investment Plans</h2>
                    </div>
                    <div class="plan-grid">
                        <?php foreach ($plans as $plan): ?>
                            <article class="plan-card">
                                <div class="plan-top">
                                    <h3><?php echo htmlspecialchars($plan['id']); ?></h3>
                                    <span>KSh <?php echo number_format($plan['profit'], 0); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($plan['description']); ?></p>
                                <div style="color: #a8b8d0; font-size: 0.85em; margin: 8px 0;">
                                    <span>Profit in <?php echo $plan['days']; ?> days</span>
                                </div>
                                <button type="button" class="btn btn-secondary plan-select" data-plan="<?php echo htmlspecialchars($plan['id']); ?>" data-min="<?php echo $plan['min']; ?>">Select</button>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h2>Choose a plan</h2>
                    </div>
                    <form method="POST" class="stack-form">
                        <input type="hidden" name="action" value="invest">
                        <label>
                            Investment plan
                            <select name="plan" id="planSelect">
                                <?php foreach ($plans as $plan): ?>
                                    <option value="<?php echo htmlspecialchars($plan['id']); ?>">Min KSh <?php echo number_format($plan['min'], 0); ?> - Earn KSh <?php echo number_format($plan['profit'], 0); ?> (<?php echo $plan['days']; ?> days)</option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            Amount to invest
                            <input type="number" name="amount" min="1" step="1" placeholder="5000" required>
                        </label>
                        <div class="return-preview">
                            <span>Expected return</span>
                            <strong id="expectedReturn">KSh 0.00</strong>
                        </div>
                        <button class="btn btn-primary full" type="submit">Invest</button>
                    </form>
                </div>
            </section>

            <section id="account" class="content-grid two-columns">
                <div class="panel">
                    <div class="panel-header">
                        <h2>Deposit</h2>
                    </div>
                    <form method="POST" class="stack-form">
                        <input type="hidden" name="action" value="deposit">
                        <label>
                            Deposit amount
                            <input type="number" name="amount" min="1" step="1" placeholder="2500" required>
                        </label>
                        <button class="btn btn-primary full" type="submit">Deposit Funds</button>
                    </form>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h2>Withdraw</h2>
                    </div>
                    <form method="POST" class="stack-form">
                        <input type="hidden" name="action" value="withdraw">
                        <label>
                            Amount to withdraw
                            <input type="number" name="amount" min="1" step="1" placeholder="1000" required>
                        </label>
                        <button class="btn btn-secondary full" type="submit">Request Withdrawal</button>
                    </form>
                </div>
            </section>

            <section class="content-grid two-columns">
                <div class="panel">
                    <div class="panel-header">
                        <h2>Profile</h2>
                    </div>
                    <form method="POST" class="stack-form">
                        <input type="hidden" name="action" value="profile">
                        <label>
                            Full name
                            <input type="text" name="name" value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
                        </label>
                        <label>
                            Phone number
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                        </label>
                        <label>
                            Country
                            <input type="text" name="country" value="<?php echo htmlspecialchars($currentUser['country'] ?? 'Kenya'); ?>">
                        </label>
                        <button class="btn btn-secondary full" type="submit">Update Profile</button>
                    </form>
                </div>

                <div id="history" class="panel">
                    <div class="panel-header">
                        <h2>Investment History</h2>
                    </div>
                    <?php if (empty($investments)): ?>
                        <p class="empty-state">No investment activity yet.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>ROI</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($investments as $investment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($investment['plan']); ?></td>
                                        <td>KSh <?php echo number_format((float) $investment['amount'], 2); ?></td>
                                        <td><?php echo number_format((float) $investment['rate'], 2); ?>%</td>
                                        <td><?php echo htmlspecialchars($investment['date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <h2>Transaction History</h2>
                </div>
                <?php if (empty($transactions)): ?>
                    <p class="empty-state">No transactions recorded.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Note</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(ucfirst($transaction['type'])); ?></td>
                                    <td>KSh <?php echo number_format((float) $transaction['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['note']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
