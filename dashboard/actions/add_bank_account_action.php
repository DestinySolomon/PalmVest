<?php
// dashboard/actions/add_bank_account_action.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../user/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$bank_name = $_POST['bank_name'] ?? '';
$account_number = $_POST['account_number'] ?? '';
$account_name = $_POST['account_name'] ?? '';
$is_default = isset($_POST['is_default']) ? 1 : 0;

// Validate input
if (empty($bank_name) || empty($account_number) || empty($account_name)) {
    $_SESSION['wallet_error'] = "Please fill in all bank account details.";
    header("Location: ../user/index.php?page=wallet");
    exit();
}

if (!preg_match('/^\d{10}$/', $account_number)) {
    $_SESSION['wallet_error'] = "Please enter a valid 10-digit account number.";
    header("Location: ../user/index.php?page=wallet");
    exit();
}

$conn->begin_transaction();

try {
    // If setting as default, remove default status from other accounts
    if ($is_default) {
        $update_stmt = $conn->prepare("UPDATE user_bank_accounts SET is_default = 0 WHERE user_id = ?");
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();
    }

    // Insert new bank account
    $insert_stmt = $conn->prepare("INSERT INTO user_bank_accounts (user_id, bank_name, account_number, account_name, is_default, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
    $insert_stmt->bind_param("isssi", $user_id, $bank_name, $account_number, $account_name, $is_default);
    $insert_stmt->execute();

    $conn->commit();

    $_SESSION['wallet_success'] = "Bank account added successfully!";
    header("Location: ../user/index.php?page=wallet");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['wallet_error'] = "Failed to add bank account. Please try again.";
    header("Location: ../user/index.php?page=wallet");
    exit();
}
