<?php
require __DIR__ . '/includes/demo-data.php';
requireAuth();

$currentUser = $_SESSION['user'];

$users = getUsers();
$allTransactions = getAllTransactions();
$allInvestments = getAllInvestments();

// Calculate database statistics
$totalUsers = count($users);
$totalTransactions = count($allTransactions);
$totalInvestments = count($allInvestments);
$totalDeposited = array_sum(array_map(function($t) { return (float) $t['amount']; }, array_filter($allTransactions, function($t) { return $t['type'] === 'deposit'; })));
$totalInvested = array_sum(array_map(function($u) { return (float) $u['invested']; }, $users));
$totalProfit = array_sum(array_map(function($u) { return (float) $u['profit']; }, $users));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Monitor | AQUA INVEST</title>
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
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="#database" class="active">🗄️ Database Monitor</a>
                <a href="logout.php">🚪 Logout</a>
            </nav>
        </aside>

        <main class="dashboard-main">
            <header class="top-header">
                <div>
                    <p class="eyebrow">System Database</p>
                    <h1>Registered Users Monitor</h1>
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

            <section id="manage-users" class="panel">
                <div class="panel-header">
                    <h2>✏️ Edit User Investment Data</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">Modify user balance, invested amount, profit, and available withdrawal</p>
                </div>

                <?php if ($editMessage): ?>
                    <div class="alert success"><?php echo htmlspecialchars($editMessage); ?></div>
                <?php endif; ?>

                <form method="POST" class="stack-form">
                    <input type="hidden" name="admin_action" value="edit_user">
                    <label>
                        Select User
                        <select name="user_id" id="userSelect" required>
                            <option value="">-- Choose a user --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Balance (KSh)
                        <input type="number" name="balance" id="balanceInput" step="0.01" placeholder="0.00" required>
                    </label>
                    <label>
                        Invested Amount (KSh)
                        <input type="number" name="invested" id="investedInput" step="0.01" placeholder="0.00" required>
                    </label>
                    <label>
                        Expected Profit (KSh)
                        <input type="number" name="profit" id="profitInput" step="0.01" placeholder="0.00" required>
                    </label>
                    <label>
                        Available Withdrawal (KSh)
                        <input type="number" name="available_withdrawal" id="withdrawalInput" step="0.01" placeholder="0.00" required>
                    </label>
                    <button class="btn btn-primary full" type="submit">Update User Data</button>
                </form>

                <script>
                    const userSelect = document.getElementById('userSelect');
                    const balanceInput = document.getElementById('balanceInput');
                    const investedInput = document.getElementById('investedInput');
                    const profitInput = document.getElementById('profitInput');
                    const withdrawalInput = document.getElementById('withdrawalInput');

                    const userData = {
                        <?php foreach ($users as $user): ?>
                        <?php echo $user['id']; ?>: { balance: <?php echo (float) $user['balance']; ?>, invested: <?php echo (float) $user['invested']; ?>, profit: <?php echo (float) $user['profit']; ?>, available_withdrawal: <?php echo (float) $user['available_withdrawal']; ?> },
                        <?php endforeach; ?>
                    };

                    userSelect.addEventListener('change', function() {
                        const selectedId = parseInt(this.value);
                        if (selectedId && userData[selectedId]) {
                            const data = userData[selectedId];
                            balanceInput.value = data.balance;
                            investedInput.value = data.invested;
                            profitInput.value = data.profit;
                            withdrawalInput.value = data.available_withdrawal;
                        } else {
                            balanceInput.value = '';
                            investedInput.value = '';
                            profitInput.value = '';
                            withdrawalInput.value = '';
                        }
                    });
                </script>
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

            <section id="database" class="panel">
                <div class="panel-header">
                    <h2>🗄️ Database Monitor</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">View and manage data storage files</p>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <h3 style="margin-top: 0; color: #00d4ff;">Data Files Status</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                        <?php
                        $dataDir = __DIR__ . '/data';
                        $files = ['users.json', 'transactions.json', 'investments.json'];
                        
                        foreach ($files as $filename):
                            $filepath = $dataDir . '/' . $filename;
                            $exists = file_exists($filepath);
                            $size = $exists ? filesize($filepath) : 0;
                            $modified = $exists ? filemtime($filepath) : 0;
                        ?>
                        <div style="background: rgba(0, 212, 255, 0.05); border: 1px solid rgba(0, 212, 255, 0.2); border-radius: 12px; padding: 16px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                <span style="font-size: 1.5rem;">📄</span>
                                <strong style="color: #00d4ff;"><?php echo $filename; ?></strong>
                            </div>
                            <div style="color: #a8b8d0; font-size: 0.9em; line-height: 1.6;">
                                <div>Status: <strong><?php echo $exists ? '✅ Exists' : '⚠️ Missing'; ?></strong></div>
                                <div>Size: <strong><?php echo number_format($size) . ' bytes'; ?></strong></div>
                                <?php if ($modified): ?>
                                    <div>Modified: <strong><?php echo date('Y-m-d H:i:s', $modified); ?></strong></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <h3 style="margin-top: 0; color: #00d4ff;">Users Data</h3>
                    <?php
                    $usersFile = $dataDir . '/users.json';
                    if (file_exists($usersFile)):
                        $userData = json_decode(file_get_contents($usersFile), true) ?? [];
                    ?>
                    <div style="background: rgba(26, 31, 58, 0.8); border: 1px solid rgba(0, 212, 255, 0.2); border-radius: 12px; padding: 16px; max-height: 400px; overflow-y: auto;">
                        <pre style="margin: 0; color: #00ff88; font-size: 0.85em; font-family: 'Courier New', monospace; white-space: pre-wrap; word-wrap: break-word;">Total Records: <?php echo count($userData); ?>

<?php echo json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?></pre>
                    </div>
                    <?php else: ?>
                    <p style="color: #a8b8d0;">No users data file found.</p>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 24px;">
                    <h3 style="margin-top: 0; color: #00d4ff;">Database Summary</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                        <div style="background: rgba(75, 227, 163, 0.1); border: 1px solid rgba(75, 227, 163, 0.3); border-radius: 10px; padding: 12px;">
                            <div style="color: #a8b8d0; font-size: 0.9em;">Total Users</div>
                            <div style="color: #4be3a3; font-size: 1.6rem; font-weight: bold;"><?php echo count($users); ?></div>
                        </div>
                        <div style="background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 10px; padding: 12px;">
                            <div style="color: #a8b8d0; font-size: 0.9em;">Total Transactions</div>
                            <div style="color: #00d4ff; font-size: 1.6rem; font-weight: bold;"><?php echo count($allTransactions); ?></div>
                        </div>
                        <div style="background: rgba(255, 165, 2, 0.1); border: 1px solid rgba(255, 165, 2, 0.3); border-radius: 10px; padding: 12px;">
                            <div style="color: #a8b8d0; font-size: 0.9em;">Total Investments</div>
                            <div style="color: #ffa502; font-size: 1.6rem; font-weight: bold;"><?php echo count($allInvestments); ?></div>
                        </div>
                        <div style="background: rgba(255, 71, 87, 0.1); border: 1px solid rgba(255, 71, 87, 0.3); border-radius: 10px; padding: 12px;">
                            <div style="color: #a8b8d0; font-size: 0.9em;">Active Investors</div>
                            <div style="color: #ff4757; font-size: 1.6rem; font-weight: bold;"><?php echo $totalInvestors; ?></div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
