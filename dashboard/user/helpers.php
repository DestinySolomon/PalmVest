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
// ✅ LOG TRANSACTION (UPDATED FOR NEW TRANSACTIONS TABLE)
// ===========================================================
function logTransaction($user_id, $amount, $description, $metadata = '') {
    global $conn;
    
    // Get current balance for the user
    $current_balance = get_wallet_balance($user_id);
    $new_balance = $current_balance + $amount;
    
    // Determine transaction type based on amount
    $type = $amount > 0 ? 'credit' : 'debit';
    
    // Insert transaction with all required fields
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, description, metadata, balance_after, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("idssds", $user_id, $amount, $description, $metadata, $new_balance, $type);
    
    return $stmt->execute();
}



// ===========================================================
// ✅ DIRECT REFERRAL BONUS
// ===========================================================
function giveReferralBonus($referrer_id, $from_user, $amount = 1000) {
    // ✅ Credit referrer
    creditWallet($referrer_id, $amount);

    // ✅ Log transaction with metadata
    $desc = "Referral bonus from user #$from_user";
    $metadata = "referral_bonus";
    logTransaction($referrer_id, $amount, $desc, $metadata);
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
    $desc = "Admin override bonus from user #$from_user";
    $metadata = "admin_bonus";
    logTransaction(0, $amount, $desc, $metadata);
}



// ===========================================================
// ✅ INVESTMENT MATURITY DAY CALCULATION
// ===========================================================
function daysToMaturity($maturity_date) {
    $today = new DateTime();
    $maturity = new DateTime($maturity_date);
    $diff = $today->diff($maturity);

    // If maturity date has passed
    if ($maturity < $today) return 0;

    return $diff->days;
}



// ===========================================================
// ✅ GET TRANSACTION STATISTICS (NEW)
// ===========================================================
function get_transaction_stats($user_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END) as total_expenses
    FROM transactions 
    WHERE user_id = ?");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}



// ===========================================================
// ✅ GET TRANSACTIONS WITH FILTERS (NEW)
// ===========================================================
function get_filtered_transactions($user_id, $type = '', $date_filter = '') {
    global $conn;

    $sql = "SELECT * FROM transactions WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";

    if ($type === 'credit') {
        $sql .= " AND amount > 0";
    } elseif ($type === 'debit') {
        $sql .= " AND amount < 0";
    }

    if ($date_filter) {
        $current_date = date('Y-m-d');
        switch($date_filter) {
            case 'today':
                $sql .= " AND DATE(created_at) = ?";
                $params[] = $current_date;
                $types .= "s";
                break;
            case 'week':
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    
    if (count($params) > 1) {
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param($types, $user_id);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



// ===========================================================
// ✅ FORMAT CURRENCY (NEW)
// ===========================================================
function format_currency($amount) {
    return '₦' . number_format(abs($amount), 2);
}



// ===========================================================
// ✅ GET TRANSACTION TYPE BADGE (NEW)
// ===========================================================
function get_transaction_type_badge($amount) {
    if ($amount > 0) {
        return '<span class="badge bg-success">Credit</span>';
    } else {
        return '<span class="badge bg-danger">Debit</span>';
    }
}