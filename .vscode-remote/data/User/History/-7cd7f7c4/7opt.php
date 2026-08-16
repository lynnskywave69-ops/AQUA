<?php
require __DIR__ . '/includes/demo-data.php';
requireAuth();

$currentUser = $_SESSION['user'];
if ($currentUser['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$users = getUsers();
$allTransactions = getAllTransactions();
$allInvestments = getAllInvestments();

// Calculate investor statistics
$investorStats = [];
foreach ($users as $user) {
    if ((float) $user['invested'] > 0) {
        $userInvestments = array_filter($allInvestments, function($inv) use ($user) {
            return $inv['user_name'] === $user['name'];
        });
        $investorStats[] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'invested_amount' => (float) $user['invested'],
            'profit' => (float) $user['profit'],
            'investment_count' => count($userInvestments),
            'plans' => array_unique(array_column($userInvestments, 'plan')),
            'created_at' => $user['created_at']
        ];
    }
}
// Sort by invested amount (descending)
usort($investorStats, function($a, $b) {
    return $b['invested_amount'] <=> $a['invested_amount'];
});

$totalInvested = array_sum(array_column($investorStats, 'invested_amount'));
$totalProfit = array_sum(array_column($investorStats, 'profit'));
$totalInvestors = count($investorStats);
$avgInvestment = $totalInvestors > 0 ? $totalInvested / $totalInvestors : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | AQUA INVEST</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand sidebar-brand">
                <span class="brand-mark">A</span>
                <span>AQUA INVEST</span>
            </div>
            <nav class="side-nav">
                <a href="dashboard.php">🏠 User Dashboard</a>
                <a href="#investors" class="active">📊 Investors</a>
                <a href="#users">👥 Users</a>
                <a href="#deposits">💳 Deposits</a>
                <a href="#investments">📈 Investments</a>
                <a href="#withdrawals">🏦 Withdrawals</a>
                <a href="#database">🗄️ Database</a>
                <a href="logout.php">🚪 Logout</a>
            </nav>
        </aside>

        <main class="dashboard-main">
            <header class="top-header">
                <div>
                    <p class="eyebrow">Admin Panel</p>
                    <h1>Operations overview</h1>
                </div>
            </header>

            <section class="stats-grid">
                <article class="stat-card highlight">
                    <span>Active Investors</span>
                    <strong><?php echo $totalInvestors; ?></strong>
                    <small>Users with active investments</small>
                </article>
                <article class="stat-card">
                    <span>Total Invested</span>
                    <strong>KSh <?php echo number_format($totalInvested, 2); ?></strong>
                    <small>All portfolios combined</small>
                </article>
                <article class="stat-card">
                    <span>Total Profit</span>
                    <strong>KSh <?php echo number_format($totalProfit, 2); ?></strong>
                    <small>Expected returns</small>
                </article>
                <article class="stat-card">
                    <span>Avg Investment</span>
                    <strong>KSh <?php echo number_format($avgInvestment, 2); ?></strong>
                    <small>Per investor</small>
                </article>
            </section>

            <section id="investors" class="panel">
                <div class="panel-header">
                    <h2>💼 Investment Monitoring - Investors Database</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">Track all users who have invested, their investment amounts, plans, and expected returns</p>
                </div>
                <?php if (empty($investorStats)): ?>
                    <p class="empty-state" style="padding: 20px; text-align: center; color: #999;">No active investors yet. Users will appear here once they make their first investment.</p>
                <?php else: ?>
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Investor Name</th>
                                <th style="text-align: left;">Email</th>
                                <th style="text-align: center;">Investments</th>
                                <th style="text-align: right;">Total Invested</th>
                                <th style="text-align: right;">Expected Profit</th>
                                <th style="text-align: center;">Plans</th>
                                <th style="text-align: left;">Since</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($investorStats as $investor): ?>
                                <tr style="border-bottom: 1px solid rgba(0, 212, 255, 0.1);">
                                    <td style="padding: 12px; font-weight: 500;"><?php echo htmlspecialchars($investor['name']); ?></td>
                                    <td style="padding: 12px; color: #00d4ff;"><?php echo htmlspecialchars($investor['email']); ?></td>
                                    <td style="padding: 12px; text-align: center;">
                                        <span style="background: rgba(0, 212, 255, 0.15); padding: 4px 8px; border-radius: 4px;">
                                            <?php echo $investor['investment_count']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px; text-align: right; font-weight: 600; color: #00ff88;">KSh <?php echo number_format($investor['invested_amount'], 2); ?></td>
                                    <td style="padding: 12px; text-align: right; color: #4be3a3;">KSh <?php echo number_format($investor['profit'], 2); ?></td>
                                    <td style="padding: 12px; text-align: center;">
                                        <?php 
                                        $planBadges = '';
                                        foreach ($investor['plans'] as $plan) {
                                            $planBadges .= '<span style="display: inline-block; background: rgba(0, 212, 255, 0.1); color: #00d4ff; padding: 3px 8px; margin: 2px; border-radius: 3px; font-size: 0.85em;">' . htmlspecialchars($plan) . '</span>';
                                        }
                                        echo $planBadges;
                                        ?>
                                    </td>
                                    <td style="padding: 12px; color: #a8b8d0; font-size: 0.9em;"><?php echo date('M d, Y', strtotime($investor['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section id="users" class="panel">
                <div class="panel-header">
                    <h2>👥 All Users</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">Complete user registry with account balances and investment totals</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Balance</th>
                            <th>Invested</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>KSh <?php echo number_format((float) $user['balance'], 2); ?></td>
                                <td>KSh <?php echo number_format((float) $user['invested'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section id="deposits" class="panel">
                <div class="panel-header">
                    <h2>💳 Deposit Activity</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">Monitor all user deposits and fund inflows</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Note</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allTransactions as $entry): ?>
                            <?php if ($entry['type'] === 'deposit'): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($entry['user_name'] ?? 'Unknown'); ?></td>
                                    <td>KSh <?php echo number_format((float) $entry['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($entry['note']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['date']); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section id="investments" class="panel">
                <div class="panel-header">
                    <h2>📈 Investment Records</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">Detailed view of all investment transactions by plan type</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allInvestments as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['user_name'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($entry['plan']); ?></td>
                                <td>KSh <?php echo number_format((float) $entry['amount'], 2); ?></td>
                                <td><?php echo number_format((float) $entry['rate'], 2); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section id="withdrawals" class="panel">
                <div class="panel-header">
                    <h2>🏦 Withdrawal Requests</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">Track and manage pending withdrawal requests from users</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Note</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allTransactions as $entry): ?>
                            <?php if ($entry['type'] === 'withdrawal'): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($entry['user_name'] ?? 'Unknown'); ?></td>
                                    <td>KSh <?php echo number_format((float) $entry['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($entry['note']); ?></td>
                                    <td><?php echo htmlspecialchars($entry['date']); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
