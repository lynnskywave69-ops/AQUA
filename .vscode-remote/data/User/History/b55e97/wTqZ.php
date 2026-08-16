<?php
session_start();

// Use JSON file for storage instead of MySQL
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$usersFile = $dataDir . '/users.json';
$transactionsFile = $dataDir . '/transactions.json';
$investmentsFile = $dataDir . '/investments.json';

function normalizeUserRow(array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'name' => $row['full_name'] ?? $row['name'] ?? 'User',
        'email' => $row['email'] ?? '',
        'phone' => $row['phone'] ?? '',
        'password' => $row['password'] ?? '',
        'role' => $row['role'] ?? 'user',
        'balance' => (float) ($row['balance'] ?? 0),
        'invested' => (float) ($row['invested'] ?? 0),
        'profit' => (float) ($row['profit'] ?? 0),
        'available_withdrawal' => (float) ($row['available_withdrawal'] ?? 0),
        'country' => $row['country'] ?? 'Kenya',
        'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
    ];
}

function getUsers(): array
{
    global $usersFile;
    if (!file_exists($usersFile)) {
        return [];
    }
    $data = json_decode(file_get_contents($usersFile), true) ?? [];
    return array_map('normalizeUserRow', $data);
}

function saveUsers(array $users): void
{
    global $usersFile;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
}

function updateUserInList(array $targetUser): void
{
    $users = getUsers();
    foreach ($users as &$user) {
        if ($user['id'] === $targetUser['id']) {
            $user = $targetUser;
            break;
        }
    }
    saveUsers($users);
}

function requireAuth(): void
{
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function addTransaction(int $userId, string $type, float $amount, string $note): void
{
    global $transactionsFile;
    $transactions = file_exists($transactionsFile) ? json_decode(file_get_contents($transactionsFile), true) ?? [] : [];
    
    $user = array_values(array_filter(getUsers(), fn($u) => $u['id'] === $userId))[0] ?? null;
    $userName = $user['name'] ?? 'Unknown';

    $transactions[] = [
        'id' => count($transactions) + 1,
        'user_id' => $userId,
        'user_name' => $userName,
        'type' => $type,
        'amount' => $amount,
        'note' => $note,
        'date' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($transactionsFile, json_encode($transactions, JSON_PRETTY_PRINT), LOCK_EX);
}

function getTransactionsForUser(int $userId): array
{
    global $transactionsFile;
    if (!file_exists($transactionsFile)) {
        return [];
    }
    $transactions = json_decode(file_get_contents($transactionsFile), true) ?? [];
    return array_reverse(array_filter($transactions, fn($t) => $t['user_id'] === $userId));
}

function getAllTransactions(): array
{
    global $transactionsFile;
    if (!file_exists($transactionsFile)) {
        return [];
    }
    $transactions = json_decode(file_get_contents($transactionsFile), true) ?? [];
    return array_reverse($transactions);
}

function addInvestment(int $userId, string $plan, float $amount, float $rate): void
{
    global $investmentsFile;
    $investments = file_exists($investmentsFile) ? json_decode(file_get_contents($investmentsFile), true) ?? [] : [];
    
    $user = array_values(array_filter(getUsers(), fn($u) => $u['id'] === $userId))[0] ?? null;
    $userName = $user['name'] ?? 'Unknown';

    $investments[] = [
        'id' => count($investments) + 1,
        'user_id' => $userId,
        'user_name' => $userName,
        'plan' => $plan,
        'amount' => $amount,
        'rate' => $rate,
        'date' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($investmentsFile, json_encode($investments, JSON_PRETTY_PRINT), LOCK_EX);
}

function getInvestmentsForUser(int $userId): array
{
    global $investmentsFile;
    if (!file_exists($investmentsFile)) {
        return [];
    }
    $investments = json_decode(file_get_contents($investmentsFile), true) ?? [];
    return array_reverse(array_filter($investments, fn($i) => $i['user_id'] === $userId));
}

function getAllInvestments(): array
{
    global $investmentsFile;
    if (!file_exists($investmentsFile)) {
        return [];
    }
    $investments = json_decode(file_get_contents($investmentsFile), true) ?? [];
    return array_reverse($investments);
}
