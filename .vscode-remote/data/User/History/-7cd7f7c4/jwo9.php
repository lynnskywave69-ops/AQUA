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
