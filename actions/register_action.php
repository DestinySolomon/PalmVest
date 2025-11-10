<?php
session_start();
require_once "../config/db.php";
require_once "../dashboard/user/helpers.php";

// ✅ VALIDATE INPUT
if (!isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['password'], $_POST['referral_code'], $_POST['currency'])) {
    die("All fields are required.");
}

$name  = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$pass  = trim($_POST['password']);
$referral_code = trim($_POST['referral_code']);
$currency = strtoupper(trim($_POST['currency']));

// ✅ BASIC VALIDATIONS
if ($name === "" || $email === "" || $phone === "" || $pass === "" || $referral_code === "" || $currency === "") {
    die("Please fill all fields.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format.");
}

if (!in_array($currency, ["NGN", "USDT"])) {
    die("Invalid currency selection.");
}

// ✅ HASH PASSWORD
$hashed = password_hash($pass, PASSWORD_DEFAULT);

// ✅ CHECK EMAIL DUPLICATE
$chk = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$chk->bind_param("s", $email);
$chk->execute();
$res = $chk->get_result();
if ($res->num_rows > 0) die("Email already exists.");

// ✅ CHECK REFERRAL CODE (User or Admin)
$referrer_id = null;
$ref_type = "";

// Check if referral belongs to user
$findUserRef = $conn->prepare("SELECT id FROM users WHERE referral_code=? LIMIT 1");
$findUserRef->bind_param("s", $referral_code);
$findUserRef->execute();
$userRefResult = $findUserRef->get_result();

if ($userRefResult->num_rows > 0) {
    $referrer_id = intval($userRefResult->fetch_assoc()['id']);
    $ref_type = "user";
} else {
    // Check if referral belongs to admin-generated code
    $findAdminRef = $conn->prepare("SELECT id FROM referral_codes WHERE code=? LIMIT 1");
    $findAdminRef->bind_param("s", $referral_code);
    $findAdminRef->execute();
    $adminRefResult = $findAdminRef->get_result();

    if ($adminRefResult->num_rows > 0) {
        $ref_type = "admin";
    } else {
        die("Invalid referral code.");
    }
}

// ✅ GENERATE NEW USER REF CODE
$new_ref_code = substr(md5(uniqid()), 0, 8);

// ✅ INSERT USER
$stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, referral_code, referrer_id, currency) 
VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssis", $name, $email, $phone, $hashed, $new_ref_code, $referrer_id, $currency);

if (!$stmt->execute()) {
    die("Registration error: " . $conn->error);
}

$new_user_id = $stmt->insert_id;

// ✅ CREATE WALLET ROW (with currency)
$walletStmt = $conn->prepare("INSERT INTO wallets (user_id, balance, currency) VALUES (?, 0, ?)");
$walletStmt->bind_param("is", $new_user_id, $currency);
$walletStmt->execute();

// ✅ PAY REFERRAL BONUSES
// 1️⃣ Direct referrer (if a user invited someone)
if ($ref_type === "user" && $referrer_id !== null) {
    giveReferralBonus($referrer_id, $new_user_id);
}

// 2️⃣ Admin always earns a bonus for every registration
giveAdminBonus($new_user_id);

// ✅ REDIRECT TO LOGIN
header("Location: ../login.php?registered=1");
exit();
