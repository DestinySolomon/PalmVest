<?php
session_start();
require_once "../config/db.php";
require_once "../dashboard/user/helpers.php";

// ✅ VALIDATE INPUT
if (!isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['password'], $_POST['referral_code'])) {
    die("All fields are required.");
}

$name  = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$pass  = trim($_POST['password']);
$referral_code = trim($_POST['referral_code']);

if ($name === "" || $email === "" || $phone === "" || $pass === "" || $referral_code === "") {
    die("Please fill all fields.");
}

// ✅ HASH PASSWORD
$hashed = password_hash($pass, PASSWORD_DEFAULT);

// ✅ CHECK EMAIL DUPLICATE
$chk = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$chk->bind_param("s", $email);
$chk->execute();
$res = $chk->get_result();
if ($res->num_rows > 0) die("Email already exists.");


// ✅ CHECK REFERRAL CODE
$findRef = $conn->prepare("SELECT id FROM users WHERE referral_code=? LIMIT 1");
$findRef->bind_param("s", $referral_code);
$findRef->execute();
$refResult = $findRef->get_result();

if ($refResult->num_rows == 0) die("Invalid referral code.");

$referrer_id = intval($refResult->fetch_assoc()['id']);


// ✅ GENERATE NEW USER REF CODE
$new_ref_code = substr(md5(uniqid()), 0, 8);


// ✅ INSERT USER
$stmt = $conn->prepare("INSERT INTO users (name,email,phone,password,referral_code,referrer_id) 
VALUES (?,?,?,?,?,?)");
$stmt->bind_param("sssssi", $name, $email, $phone, $hashed, $new_ref_code, $referrer_id);

if (!$stmt->execute()) die("Registration error: " . $conn->error);

$new_user_id = $stmt->insert_id;


// ✅ CREATE WALLET ROW (important!)
$conn->query("INSERT INTO wallets (user_id,balance) VALUES ($new_user_id,0)");


// ✅ PAY REFERRAL BONUS (to user)
giveReferralBonus($referrer_id, $new_user_id);

// ✅ ADMIN OVERRIDE BONUS
giveAdminBonus($new_user_id);


// ✅ REDIRECT TO LOGIN
header("Location: ../login.php?registered=1");
exit();

