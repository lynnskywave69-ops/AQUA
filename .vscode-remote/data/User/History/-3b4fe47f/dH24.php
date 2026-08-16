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
            addTransaction($currentUser['id'], 'withdrawal', $amount, 'Withdrawal pending admin approval', 'pending');
            $successMessage = 'Withdrawal request submitted successfully. It is pending until admin confirmation.';
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
$referralLink = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000') . '/register.php?ref=' . $currentUser['id'];
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
                        <button class="btn btn-primary full" type="button" onclick="showInvestmentInstructions(event)">Invest</button>
                    </form>
                </div>
            </section>

            <!-- M-Pesa Investment Instructions Modal -->
            <div id="investmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1000; place-items: center; grid-place-items: center;">
                <div style="background: linear-gradient(135deg, rgba(26, 31, 58, 0.95), rgba(35, 42, 75, 0.98)); border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 16px; padding: 32px; max-width: 500px; width: 90%; box-shadow: 0 25px 80px rgba(0, 0, 0, 0.6);">
                    <h2 style="margin: 0 0 16px; color: #00d4ff;">📝 Before You Invest</h2>
                    <p style="color: #a8b8d0; line-height: 1.6; margin-bottom: 20px;">
                        Make sure you have sufficient balance to complete this investment. If you need to deposit funds, follow these M-Pesa steps:
                    </p>
                    <div style="background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                        <ol style="margin: 0; padding-left: 20px; color: #a8b8d0; line-height: 1.8;">
                            <li><strong>Go to M-Pesa</strong> on your phone</li>
                            <li><strong>Select "Send Money"</strong></li>
                            <li><strong>Enter:</strong> <span style="background: rgba(0, 212, 255, 0.2); padding: 2px 6px; border-radius: 3px; color: #00d4ff; font-weight: bold;">0783797841</span></li>
                            <li><strong>Enter your deposit amount</strong></li>
                            <li><strong>Enter PIN and confirm</strong></li>
                        </ol>
                    </div>
                    <div style="background: rgba(75, 227, 163, 0.1); border-left: 3px solid #4be3a3; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                        <strong style="color: #4be3a3;">✓ Ready?</strong> Click "Proceed with Investment" to confirm your investment after depositing via M-Pesa.
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <button onclick="closeInvestmentModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                        <button onclick="proceedWithInvestment()" class="btn btn-primary" style="flex: 1;">Proceed with Investment</button>
                    </div>
                </div>
            </div>

            <script>
                let investmentForm = null;

                function showInvestmentInstructions(event) {
                    event.preventDefault();
                    const amountInput = document.querySelector('input[name="amount"]');
                    const amount = parseFloat(amountInput.value);
                    const planSelect = document.getElementById('planSelect');
                    const selectedPlan = planSelect.value;
                    
                    if (!amount || !selectedPlan) {
                        alert('Please select a plan and enter an amount');
                        return false;
                    }
                    
                    // Save the form reference for later submission
                    investmentForm = amountInput.closest('form');
                    
                    // Show modal with M-Pesa instructions
                    document.getElementById('investmentModal').style.display = 'grid';
                    return false;
                }

                function proceedWithInvestment() {
                    if (investmentForm) {
                        investmentForm.submit();
                    }
                    closeInvestmentModal();
                }

                function closeInvestmentModal() {
                    document.getElementById('investmentModal').style.display = 'none';
                }
            </script>

            <section id="account" class="content-grid two-columns">
                <div class="panel">
                    <div class="panel-header">
                        <h2>💰 How to Deposit via M-Pesa</h2>
                    </div>
                    <div style="background: linear-gradient(135deg, rgba(0, 212, 255, 0.1), rgba(0, 255, 136, 0.08)); border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 12px; padding: 16px;">
                        <div style="color: #a8b8d0; line-height: 1.8;">
                            <div style="margin-bottom: 16px;">
                                <p style="margin: 0 0 12px; color: #00ff88; font-weight: 600; font-size: 1rem;">📱 Follow these steps to deposit funds:</p>
                                <ol style="margin: 0; padding-left: 24px;">
                                    <li style="margin-bottom: 10px;"><strong>Go to M-Pesa</strong> on your phone</li>
                                    <li style="margin-bottom: 10px;"><strong>Select "Send Money"</strong> from the menu</li>
                                    <li style="margin-bottom: 10px;"><strong>Enter the account number:</strong> <span style="background: rgba(0, 212, 255, 0.2); padding: 4px 8px; border-radius: 4px; color: #00d4ff; font-weight: bold;">0783797841</span></li>
                                    <li style="margin-bottom: 10px;"><strong>Enter the amount</strong> you want to deposit</li>
                                    <li style="margin-bottom: 10px;"><strong>Enter your M-Pesa PIN</strong> and confirm the transaction</li>
                                    <li><strong>Your account will be credited immediately</strong> after payment confirmation</li>
                                </ol>
                            </div>
                            <div style="background: rgba(255, 165, 2, 0.1); border-left: 3px solid #ffa502; padding: 12px; border-radius: 6px; margin-top: 12px;">
                                <strong style="color: #ffa502;">⚠️ Important:</strong> Make sure to use the correct account number. Your balance will update automatically once the transaction is confirmed.
                            </div>
                        </div>
                    </div>
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
                    <div style="margin-top:16px; background: rgba(255,255,255,0.02); border:1px solid rgba(99,179,255,0.12); padding:14px; border-radius:10px; color:var(--muted); font-size:0.95rem;">
                        <h3 style="margin:0 0 8px; color: #ffb84d;">WITHDRAWAL DESCRIPTION &amp; RULES</h3>
                        <ul style="margin:0; padding-left:18px; line-height:1.6;">
                            <li><strong>Allowed amounts: KSh 100, KSh 240, KSh 800</strong></li>
                            <li><strong>Withdrawal per day: 1 time only</strong></li>
                            <li>Ensure your M-Pesa number is correct before submitting a withdrawal request.</li>
                            <li>Withdrawals are processed only after admin confirmation.</li>
                            <li>All approved withdrawals are completed promptly after verification.</li>
                        </ul>
                    </div>
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

                <div class="panel">
                    <div class="panel-header">
                        <h2>Referral Link</h2>
                    </div>
                    <div style="display: grid; gap: 12px;">
                        <label>
                            Shareable referral link
                            <input type="text" value="<?php echo htmlspecialchars($referralLink); ?>" readonly>
                        </label>
                        <button type="button" class="btn btn-secondary" onclick="copyReferralLink()">Copy Referral Link</button>
                    </div>
                    <script>
                        function copyReferralLink() {
                            const input = document.querySelector('input[type="text"][value*="register.php?ref="]');
                            if (!input) return;
                            input.focus();
                            input.select();
                            navigator.clipboard.writeText(input.value).then(function() {
                                alert('Referral link copied successfully.');
                            }).catch(function() {
                                alert('Copy failed. Please copy the link manually.');
                            });
                        }
                    </script>
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
                                <th>Status</th>
                                <th>Note</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(ucfirst($transaction['type'])); ?></td>
                                    <td>KSh <?php echo number_format((float) $transaction['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($transaction['status'] ?? 'completed')); ?></td>
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
