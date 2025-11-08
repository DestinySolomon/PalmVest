<?php
// dashboard/actions/withdraw_action.php
session_start();
require_once "../../config/db.php";
require_once "../helpers.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = floatval($_POST['amount'] ?? 0);
$bank_account_id = intval($_POST['bank_account_id'] ?? 0);

// Validate input
if ($amount < 1000) {
    $_SESSION['wallet_error'] = "Minimum withdrawal amount is ₦1,000.";
    header("Location: ../user/index.php?page=wallet");
    exit();
}

if ($bank_account_id <= 0) {
    $_SESSION['wallet_error'] = "Please select a bank account.";
    header("Location: ../user/index.php?page=wallet");
    exit();
}

// Check if user has sufficient balance
$current_balance = get_wallet_balance($user_id);
if ($amount > $current_balance) {
    $_SESSION['wallet_error'] = "Insufficient balance for withdrawal.";
    header("Location: ../user/index.php?page=wallet");
    exit();
}

// Get bank account details
$bank_stmt = $conn->prepare("SELECT * FROM user_bank_accounts WHERE id = ? AND user_id = ? AND status = 'active'");
$bank_stmt->bind_param("ii", $bank_account_id, $user_id);
$bank_stmt->execute();
$bank_account = $bank_stmt->get_result()->fetch_assoc();

if (!$bank_account) {
    $_SESSION['wallet_error'] = "Invalid bank account selected.";
    header("Location: ../user/index.php?page=wallet");
    exit();
}

$conn->begin_transaction();

try {
    // 1. Deduct amount from wallet
    $deduct_stmt = $conn->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?");
    $deduct_stmt->bind_param("di", $amount, $user_id);
    $deduct_stmt->execute();

    // 2. Create withdrawal request
    $withdraw_stmt = $conn->prepare("INSERT INTO withdrawal_requests (user_id, bank_account_id, amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $withdraw_stmt->bind_param("iid", $user_id, $bank_account_id, $amount);
    $withdraw_stmt->execute();
    $withdrawal_id = $withdraw_stmt->insert_id;

    // 3. Log transaction
    $description = "Withdrawal request to " . $bank_account['bank_name'] . " - " . $bank_account['account_number'];
    $metadata = "withdrawal|bank:" . $bank_account['bank_name'] . "|request_id:" . $withdrawal_id;
    logTransaction($user_id, -$amount, $description, $metadata);

    $conn->commit();

    $_SESSION['wallet_success'] = "Withdrawal request submitted successfully! ₦" . number_format($amount) . " will be processed within 24 hours.";
    header("Location: ../user/index.php?page=wallet");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['wallet_error'] = "Failed to process withdrawal request. Please try again.";
    header("Location: ../user/index.php?page=wallet");
    exit();
}
?>