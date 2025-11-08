<?php
// dashboard/actions/harvest_action.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../user/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    $_SESSION['portfolio_error'] = "Invalid request.";
    header("Location: ../user/index.php?page=portfolio");
    exit();
}

// Fetch order details from oil_orders table
$stmt = $conn->prepare("SELECT * FROM oil_orders WHERE id = ? AND user_id = ? AND status = 'staked'");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['portfolio_error'] = "Order not found or already harvested.";
    header("Location: ../user/index.php?page=portfolio");
    exit();
}

// Check if stake period has ended
$current_date = new DateTime();
$stake_end = new DateTime($order['stake_end']);

if ($current_date < $stake_end) {
    $_SESSION['portfolio_error'] = "This investment is not ready for harvest yet.";
    header("Location: ../user/index.php?page=portfolio");
    exit();
}

// Calculate profit (15% profit example)
$profit_percentage = 1.15; // 15% profit
$harvest_amount = $order['amount'] * $profit_percentage;

$conn->begin_transaction();

try {
    // 1. Update order status to harvested
    $update_order = $conn->prepare("UPDATE oil_orders SET status = 'harvested', harvested_at = NOW() WHERE id = ?");
    $update_order->bind_param("i", $order_id);
    $update_order->execute();

    // 2. Credit user's wallet with harvest amount
    $credit_wallet = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
    $credit_wallet->bind_param("di", $harvest_amount, $user_id);
    $credit_wallet->execute();

    // 3. Log transaction
    $description = "Harvested {$order['quantity']} units of oil from order #$order_id";
    logTransaction($user_id, $harvest_amount, $description);

    $conn->commit();

    $_SESSION['buy_success'] = "Successfully harvested {$order['quantity']} units! ₦" . number_format($harvest_amount) . " credited to your wallet.";
    header("Location: ../user/index.php?page=portfolio");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['portfolio_error'] = "Harvest failed: " . $e->getMessage();
    header("Location: ../user/index.php?page=portfolio");
    exit();
}
