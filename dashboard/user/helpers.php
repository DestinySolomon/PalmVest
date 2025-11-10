<?php
// ✅ Correct DB path
require_once __DIR__ . "/../../config/db.php";


// ===========================================================
// ✅ GET USER WALLET BALANCE (supports currency)
// ===========================================================
function get_wallet_balance($user_id, $currency = null) {
    global $conn;

    if ($currency) {
        $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = ? LIMIT 1");
        $stmt->bind_param("is", $user_id, $currency);
    } else {
        $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
    }

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
function get_referral_count($ref_code) {
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ? LIMIT 1");
    $stmt->bind_param("s", $ref_code);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) return 0;

    $referrer_id = intval($res->fetch_assoc()['id']);

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

    return intval($row['setting_value']); // usually 1
}



// ===========================================================
// ✅ CREDIT WALLET (AUTO CREATES IF MISSING, supports currency)
// ===========================================================
function creditWallet($user_id, $amount, $currency = 'NGN') {
    global $conn;

    // Ensure wallet exists
    $check = $conn->prepare("SELECT id FROM wallets WHERE user_id=? AND currency=? LIMIT 1");
    $check->bind_param("is", $user_id, $currency);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows == 0) {
        $create = $conn->prepare("INSERT INTO wallets (user_id, balance, currency) VALUES (?, 0, ?)");
        $create->bind_param("is", $user_id, $currency);
        $create->execute();
    }

    // Update balance
    $stmt = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = ?");
    $stmt->bind_param("dis", $amount, $user_id, $currency);
    $stmt->execute();
}



// ===========================================================
// ✅ LOG TRANSACTION (supports currency)
// ===========================================================
function logTransaction($user_id, $amount, $description, $metadata = '', $currency = 'NGN') {
    global $conn;
    
    $current_balance = get_wallet_balance($user_id, $currency);
    $new_balance = $current_balance + $amount;
    $type = $amount > 0 ? 'credit' : 'debit';
    
    $stmt = $conn->prepare("INSERT INTO transactions 
        (user_id, amount, currency, description, metadata, balance_after, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("idsssss", $user_id, $amount, $currency, $description, $metadata, $new_balance, $type);
    
    return $stmt->execute();
}



// ===========================================================
// ✅ DIRECT REFERRAL BONUS
// ===========================================================
function giveReferralBonus($referrer_id, $from_user, $amount = 1000) {
    global $conn;

    // Get referrer’s currency
    $curQuery = $conn->prepare("SELECT currency FROM users WHERE id=? LIMIT 1");
    $curQuery->bind_param("i", $referrer_id);
    $curQuery->execute();
    $refCurrency = $curQuery->get_result()->fetch_assoc()['currency'] ?? 'NGN';

    creditWallet($referrer_id, $amount, $refCurrency);

    $desc = "Referral bonus from user #$from_user";
    $metadata = "referral_bonus";
    logTransaction($referrer_id, $amount, $desc, $metadata, $refCurrency);
}



// ===========================================================
// ✅ ADMIN OVERRIDE BONUS (global bonus on all registrations)
// ===========================================================
function giveAdminBonus($from_user, $amount = 500) {
    global $conn;

    $admin_id = getGlobalAdminID();

    // Get admin’s currency (defaults to NGN)
    $curQuery = $conn->prepare("SELECT currency FROM users WHERE id=? LIMIT 1");
    $curQuery->bind_param("i", $admin_id);
    $curQuery->execute();
    $adminCurrency = $curQuery->get_result()->fetch_assoc()['currency'] ?? 'NGN';

    // Credit admin wallet
    creditWallet($admin_id, $amount, $adminCurrency);

    // Log transaction
    $desc = "Admin override bonus from user #$from_user";
    $metadata = "admin_bonus";
    logTransaction($admin_id, $amount, $desc, $metadata, $adminCurrency);
}



// ===========================================================
// ✅ INVESTMENT MATURITY DAY CALCULATION
// ===========================================================
function daysToMaturity($maturity_date) {
    $today = new DateTime();
    $maturity = new DateTime($maturity_date);
    $diff = $today->diff($maturity);

    return $maturity < $today ? 0 : $diff->days;
}



// ===========================================================
// ✅ GET TRANSACTION STATISTICS
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
// ✅ GET TRANSACTIONS WITH FILTERS
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
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



// ===========================================================
// ✅ FORMAT CURRENCY SYMBOL (based on currency)
// ===========================================================
function format_currency($amount, $currency = 'NGN') {
    $symbol = ($currency === 'USDT') ? '$' : '₦';
    return $symbol . number_format(abs($amount), 2);
}



// ===========================================================
// ✅ TRANSACTION TYPE BADGE
// ===========================================================
function get_transaction_type_badge($amount) {
    if ($amount > 0) {
        return '<span class="badge bg-success">Credit</span>';
    } else {
        return '<span class="badge bg-danger">Debit</span>';
    }
}
