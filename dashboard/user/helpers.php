<?php
// ✅ Correct DB path
require_once __DIR__ . "/../../config/db.php";


// ===========================================================
// ✅ GET USER WALLET BALANCE
// ===========================================================
function get_wallet_balance($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) return 0;

    return floatval($res->fetch_assoc()['balance']);
}



// ===========================================================
// ✅ GET TOTAL OIL PURCHASED
// ===========================================================
function get_total_oil_purchased($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM oil_orders WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return $row['total'] ?? 0;
}



// ===========================================================
// ✅ GET NUMBER OF DIRECT REFERRALS
// ===========================================================
// ✅ This is the correct version — fast, stable, accurate
function get_referral_count($ref_code) {
    global $conn;

    // Step 1: get user_id of referrer
    $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ? LIMIT 1");
    $stmt->bind_param("s", $ref_code);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) return 0;

    $referrer_id = intval($res->fetch_assoc()['id']);

    // Step 2: count referrals
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE referrer_id = ?");
    $stmt->bind_param("i", $referrer_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return intval($row['total']);
}



// ===========================================================
// ✅ GET UNREAD NOTIFICATIONS
// ===========================================================
function get_unread_notifications($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND status='unread'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return $row['total'] ?? 0;
}



// ===========================================================
// ✅ GET RECENT TRANSACTIONS
// ===========================================================
function get_recent_transactions($user_id, $limit = 5) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



// ===========================================================
// ✅ GLOBAL ADMIN (GLOBAL UPLINE)
// ===========================================================
function getGlobalAdminID() {
    global $conn;

    $q = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key='global_upline_id' LIMIT 1");
    $row = $q->fetch_assoc();

    return intval($row['setting_value']);  // Should always return 1
}



// ===========================================================
// ✅ CREDIT WALLET (AUTO CREATES WALLET IF MISSING)
// ===========================================================
function creditWallet($user_id, $amount) {
    global $conn;

    // ✅ Ensure wallet exists
    $conn->query("INSERT INTO wallets (user_id, balance) VALUES ($user_id, 0)
                  ON DUPLICATE KEY UPDATE balance = balance");

    // ✅ Add balance
    $stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
    $stmt->bind_param("di", $amount, $user_id);
    $stmt->execute();
}



// ===========================================================
// ✅ LOG TRANSACTION
// ===========================================================
function logTransaction($user_id, $amount, $description) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, description, status)
                            VALUES (?, ?, ?, 'success')");
    $stmt->bind_param("ids", $user_id, $amount, $description);
    $stmt->execute();
}



// ===========================================================
// ✅ DIRECT REFERRAL BONUS
// ===========================================================
function giveReferralBonus($referrer_id, $from_user, $amount = 1000) {

    // ✅ Credit referrer
    creditWallet($referrer_id, $amount);

    // ✅ Log transaction
    $desc = "Referral bonus from user #$from_user";
    logTransaction($referrer_id, $amount, $desc);
}



// ===========================================================
// ✅ ADMIN OVERRIDE BONUS
// ===========================================================
function giveAdminBonus($from_user, $amount = 500) {
    global $conn;

    // ✅ credit admin wallet
    $stmt = $conn->prepare("UPDATE admin_wallet SET balance = balance + ? WHERE id = 1");
    $stmt->bind_param("d", $amount);
    $stmt->execute();

    // ✅ log admin transaction (user_id = 0)
    logTransaction(0, $amount, "Admin override bonus from user #$from_user");
}

//INVESTMENT MATURITY DAY CODES
function daysToMaturity($maturity_date) {
    $today = new DateTime();
    $maturity = new DateTime($maturity_date);
    $diff = $today->diff($maturity);

    // If maturity date has passed
    if ($maturity < $today) return 0;

    return $diff->days;
}

