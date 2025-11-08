<?php
// dashboard/actions/buy_action.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . "/../user/helpers.php"; // creditWallet, logTransaction, giveReferralBonus etc.

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$listing_id = intval($_POST['listing_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 0);

if ($listing_id <= 0 || $quantity <= 0) {
    $_SESSION['buy_error'] = "Invalid request.";
    header("Location: ../user/index.php?page=buy_oil");
    exit();
}

// Fetch listing
$stmt = $conn->prepare("SELECT l.id, l.seller_id, l.available_quantity, l.price FROM oil_listings l WHERE l.id = ? LIMIT 1");
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $_SESSION['buy_error'] = "Listing not found.";
    header("Location: ../user/index.php?page=buy_oil");
    exit();
}

$listing = $res->fetch_assoc();

if ($quantity > intval($listing['available_quantity'])) {
    $_SESSION['buy_error'] = "Quantity exceeds available stock.";
    header("Location: ../user/index.php?page=buy_oil");
    exit();
}

// compute amount
$price = floatval($listing['price']);
$amount = $price * $quantity;

// check wallet
$wallet_balance = get_wallet_balance($user_id);

if ($wallet_balance < $amount) {
    $_SESSION['buy_error'] = "Insufficient wallet balance. Please top up your wallet.";
    header("Location: ../user/index.php?page=buy_oil");
    exit();
}

// Begin transaction (DB)
$conn->begin_transaction();

try {
    // 1) deduct wallet
    // ensure wallet row exists
    $conn->query("INSERT INTO wallets (user_id, balance) VALUES ($user_id, 0) ON DUPLICATE KEY UPDATE balance=balance");
    $stmt = $conn->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?");
    $stmt->bind_param("di", $amount, $user_id);
    $stmt->execute();

    // 2) insert oil_orders (staked) with stake_end = now + 30 days
    $stake_end = date('Y-m-d H:i:s', strtotime("+30 days"));
    $insert = $conn->prepare("INSERT INTO oil_orders (user_id, listing_id, quantity, price, amount, status, stake_end, created_at) VALUES (?, ?, ?, ?, ?, 'staked', ?, NOW())");
    $insert->bind_param("iiidds", $user_id, $listing_id, $quantity, $price, $amount, $stake_end);
    $insert->execute();
    $order_id = $insert->insert_id;

    // 3) decrement available_quantity in listing
    $upd = $conn->prepare("UPDATE oil_listings SET available_quantity = available_quantity - ? WHERE id = ?");
    $upd->bind_param("ii", $quantity, $listing_id);
    $upd->execute();

    // 4) log transaction
    $desc = "Bought $quantity unit(s) from listing #$listing_id (order #$order_id)";
    logTransaction($user_id, -1 * $amount, $desc); // negative amount for purchase

    // 5) commit
    $conn->commit();

    // success message and redirect to portfolio or overview
    $_SESSION['buy_success'] = "Purchase successful and staked until $stake_end. Order ID: $order_id";
    header("Location: ../user/index.php?page=portfolio");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Buy action error: " . $e->getMessage());
    $_SESSION['buy_error'] = "Unable to complete purchase. Try again later.";
    header("Location: ../user/index.php?page=buy_oil");
    exit();
}
