<?php
session_start();
require_once "../config/db.php";

// Collect inputs
$name  = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = trim($_POST['password']);
$referral_code = trim($_POST['referral_code']);

// Basic validation
if ($name === "" || $email === "" || $phone === "" || $password === "" || $referral_code === "") {
    $_SESSION['error'] = "All fields including referral code are required.";
    header("Location: ../register.php");
    exit();
}

// Check if email exists
$checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    $_SESSION['error'] = "Email already exists. Please login.";
    header("Location: ../register.php");
    exit();
}

// Step 1: Validate referral code (MUST exist in user or admin table)
$referrer_id = null;
$referral_source = ""; // "user" or "admin"

// Check users table
$checkUserRef = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
$checkUserRef->bind_param("s", $referral_code);
$checkUserRef->execute();
$userRefResult = $checkUserRef->get_result();

if ($userRefResult->num_rows > 0) {
    $refData = $userRefResult->fetch_assoc();
    $referrer_id = $refData['id'];
    $referral_source = "user";  // User referral
} else {
    // Check admin referral table
    $checkAdminRef = $conn->prepare("SELECT id FROM referral_codes WHERE code = ?");
    $checkAdminRef->bind_param("s", $referral_code);
    $checkAdminRef->execute();
    $adminRefResult = $checkAdminRef->get_result();

    if ($adminRefResult->num_rows > 0) {
        $referral_source = "admin"; // Admin-generated code
    } else {
        // No match at all → invalid referral
        $_SESSION['error'] = "Invalid referral code. Registration blocked.";
        header("Location: ../register.php");
        exit();
    }
}

// STEP 2: Create user's own referral code
$myRefCode = strtoupper(substr($name, 0, 3)) . rand(1000, 9999);

// STEP 3: Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// STEP 4: Insert new user
$insert = $conn->prepare("INSERT INTO users (name, email, phone, password, referral_code, referred_by) 
                          VALUES (?, ?, ?, ?, ?, ?)");
$insert->bind_param("ssssss", $name, $email, $phone, $hashedPassword, $myRefCode, $referral_code);

if (!$insert->execute()) {
    $_SESSION['error'] = "Something went wrong. Please try again.";
    header("Location: ../register.php");
    exit();
}

$newUserID = $insert->insert_id;

// STEP 5: Handle referral bonus (ONLY for user-referrals)
if ($referral_source === "user") {
    $bonus = 2000; // ✅ YOU CAN CHANGE THIS ANYTIME

    // Add referral record
    $log = $conn->prepare("INSERT INTO referrals (referrer_id, referee_id, bonus_amount) 
                           VALUES (?, ?, ?)");
    $log->bind_param("iii", $referrer_id, $newUserID, $bonus);
    $log->execute();

    // Add bonus to referrer's balance
    $updateBal = $conn->prepare("UPDATE users SET referral_balance = referral_balance + ? WHERE id = ?");
    $updateBal->bind_param("ii", $bonus, $referrer_id);
    $updateBal->execute();
}

// ✅ Registration successful
$_SESSION['success'] = "Registration successful! Please login.";
header("Location: ../login.php");
exit();
