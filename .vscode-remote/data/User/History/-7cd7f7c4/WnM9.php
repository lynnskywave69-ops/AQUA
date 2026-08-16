<?php
require __DIR__ . '/includes/demo-data.php';
requireAuth();

$currentUser = $_SESSION['user'];
if ($currentUser['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $action = $_POST['admin_action'];

    if ($action === 'complete_withdrawal') {
        $transactionId = (int) ($_POST['transaction_id'] ?? 0);
        $withdrawal = null;
        foreach (getAllTransactions() as $entry) {
            if ((int) $entry['id'] === $transactionId && ($entry['type'] ?? '') === 'withdrawal') {
                $withdrawal = $entry;
                break;
            }
        }

        if ($withdrawal && (($withdrawal['status'] ?? 'completed') === 'pending')) {
            $user = null;
            foreach (getUsers() as $entry) {
                if ((int) $entry['id'] === (int) $withdrawal['user_id']) {
                    $user = $entry;
                    break;
                }
            }

            if ($user) {
                $user['available_withdrawal'] = max(0, (float) $user['available_withdrawal'] - (float) $withdrawal['amount']);
                $user['balance'] = max(0, (float) $user['balance'] - (float) $withdrawal['amount']);
                updateUserInList($user);
                updateTransactionStatus($transactionId, 'completed', 'Withdrawal completed by admin after fund confirmation');
            }
        }
    }

    if ($action === 'update_user_values') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $users = getUsers();
        foreach ($users as &$user) {
            if ((int) $user['id'] === $userId) {
                $invested = (float) ($_POST['invested'] ?? $user['invested']);
                $balance = (float) ($_POST['balance'] ?? $user['balance']);
                $profit = max(0, $invested * 0.25);
                $availableWithdrawal = (float) ($_POST['available_withdrawal'] ?? max(0, $balance - $invested + $profit));

                $user['balance'] = $balance;
                $user['invested'] = $invested;
                $user['profit'] = $profit;
                $user['available_withdrawal'] = $availableWithdrawal;
                saveUsers($users);
                break;
            }
        }
    }
}

$users = getUsers();
$allTransactions = getAllTransactions();
$allInvestments = getAllInvestments();

// Calculate database statistics
$totalUsers = count($users);
$totalTransactions = count($allTransactions);
$totalInvestments = count($allInvestments);
$totalDeposited = array_sum(array_map(function($t) { return (float) $t['amount']; }, array_filter($allTransactions, function($t) { return ($t['type'] ?? '') === 'deposit'; })));
$totalInvested = array_sum(array_map(function($u) { return (float) $u['invested']; }, $users));
$totalProfit = array_sum(array_map(function($u) { return (float) $u['profit']; }, $users));
$pendingWithdrawals = array_values(array_filter($allTransactions, function($t) { return ($t['type'] ?? '') === 'withdrawal' && (($t['status'] ?? 'completed') === 'pending'); }));
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
                    <span>Registered Users</span>
                    <strong><?php echo $totalUsers; ?></strong>
                    <small>Total accounts in database</small>
                </article>
                <article class="stat-card">
                    <span>Total Investments</span>
                    <strong><?php echo $totalInvestments; ?></strong>
                    <small>Investment records</small>
                </article>
                <article class="stat-card">
                    <span>Total Deposited</span>
                    <strong>KSh <?php echo number_format($totalDeposited, 2); ?></strong>
                    <small>Combined deposits</small>
                </article>
                <article class="stat-card">
                    <span>Total Invested</span>
                    <strong>KSh <?php echo number_format($totalInvested, 2); ?></strong>
                    <small>Active investments</small>
                </article>
            </section>

            <section id="users" class="panel">
                <div class="panel-header">
                    <h2>👥 All Registered Users</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">Complete user registry with account details</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Balance</th>
                            <th>Invested</th>
                            <th>Expected Profit</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>KSh <?php echo number_format((float) $user['balance'], 2); ?></td>
                                <td>KSh <?php echo number_format((float) $user['invested'], 2); ?></td>
                                <td>KSh <?php echo number_format((float) $user['profit'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section id="withdrawals" class="panel">
                <div class="panel-header">
                    <h2>🏦 Withdrawal Requests</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">Confirm incoming funds and complete withdrawals after approval</p>
                </div>
                <?php if (empty($pendingWithdrawals)): ?>
                    <p class="empty-state">No pending withdrawals.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingWithdrawals as $entry): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($entry['user_name'] ?? 'Unknown'); ?></td>
                                    <td>KSh <?php echo number_format((float) $entry['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($entry['status'] ?? 'pending')); ?></td>
                                    <td><?php echo htmlspecialchars($entry['date']); ?></td>
                                    <td>
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="admin_action" value="complete_withdrawal">
                                            <input type="hidden" name="transaction_id" value="<?php echo (int) $entry['id']; ?>">
                                            <button class="btn btn-primary" type="submit">Confirm & Complete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section id="edit-user-values" class="panel">
                <div class="panel-header">
                    <h2>✏️ Edit Customer Investment Values</h2>
                    <p style="margin-top: 8px; color: #a8b8d0; font-size: 0.9em;">Changing the invested amount automatically recalculates expected profit and keeps the record consistent.</p>
                </div>

                <form method="POST" class="stack-form">
                    <input type="hidden" name="admin_action" value="update_user_values">
                    <label>
                        Select User
                        <select name="user_id" id="adminUserSelect" required>
                            <option value="">-- Choose a user --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo (int) $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Balance (KSh)
                        <input type="number" name="balance" id="balanceInput" step="0.01" min="0" placeholder="0.00" required>
                    </label>
                    <label>
                        Invested Amount (KSh)
                        <input type="number" name="invested" id="investedInput" step="0.01" min="0" placeholder="0.00" required>
                    </label>
                    <label>
                        Expected Profit (KSh)
                        <input type="number" name="profit" id="profitInput" step="0.01" min="0" placeholder="0.00" readonly>
                    </label>
                    <label>
                        Available Withdrawal (KSh)
                        <input type="number" name="available_withdrawal" id="availableWithdrawalInput" step="0.01" min="0" placeholder="0.00">
                    </label>
                    <button class="btn btn-primary full" type="submit">Update User Data</button>
                </form>

                <script>
                    const adminUserSelect = document.getElementById('adminUserSelect');
                    const balanceInput = document.getElementById('balanceInput');
                    const investedInput = document.getElementById('investedInput');
                    const profitInput = document.getElementById('profitInput');
                    const availableWithdrawalInput = document.getElementById('availableWithdrawalInput');

                    const adminUserData = {
                        <?php foreach ($users as $user): ?>
                        <?php echo (int) $user['id']; ?>: {
                            balance: <?php echo (float) $user['balance']; ?>,
                            invested: <?php echo (float) $user['invested']; ?>,
                            profit: <?php echo (float) $user['profit']; ?>,
                            available_withdrawal: <?php echo (float) $user['available_withdrawal']; ?>
                        },
                        <?php endforeach; ?>
                    };

                    function recalculateExpectedProfit() {
                        const invested = parseFloat(investedInput.value || 0);
                        const profit = invested * 0.25;
                        profitInput.value = profit.toFixed(2);

                        if (!availableWithdrawalInput.dataset.manual) {
                            const balance = parseFloat(balanceInput.value || 0);
                            availableWithdrawalInput.value = Math.max(0, balance - invested + profit).toFixed(2);
                        }
                    }

                    adminUserSelect.addEventListener('change', function() {
                        const selectedId = parseInt(this.value);
                        if (selectedId && adminUserData[selectedId]) {
                            const data = adminUserData[selectedId];
                            balanceInput.value = data.balance;
                            investedInput.value = data.invested;
                            profitInput.value = data.profit;
                            availableWithdrawalInput.value = data.available_withdrawal;
                            availableWithdrawalInput.dataset.manual = '0';
                            recalculateExpectedProfit();
                        } else {
                            balanceInput.value = '';
                            investedInput.value = '';
                            profitInput.value = '';
                            availableWithdrawalInput.value = '';
                            availableWithdrawalInput.dataset.manual = '0';
                        }
                    });

                    investedInput.addEventListener('input', function() {
                        availableWithdrawalInput.dataset.manual = '0';
                        recalculateExpectedProfit();
                    });

                    balanceInput.addEventListener('input', function() {
                        availableWithdrawalInput.dataset.manual = '0';
                        recalculateExpectedProfit();
                    });

                    availableWithdrawalInput.addEventListener('input', function() {
                        availableWithdrawalInput.dataset.manual = '1';
                    });
                </script>
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
                    <h3 style="margin-top: 0; color: #00d4ff;">Users Data Summary</h3>
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
