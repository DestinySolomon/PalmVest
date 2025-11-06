<?php
session_start();
require_once "../config/db.php";

// Collect login inputs
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Basic validation
if ($email === "" || $password === "") {
    $_SESSION['login_error'] = "All fields are required.";
    header("Location: ../login.php");
    exit();
}

// Check if email exists
$stmt = $conn->prepare("SELECT id, name, email, password, referral_code FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// If no user found
if ($result->num_rows === 0) {
    $_SESSION['login_error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit();
}

$user = $result->fetch_assoc();

// Validate password
if (!password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit();
}

// Successful login → Set session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['ref_code'] = $user['referral_code'];

// Redirect to dashboard
header("Location: ../dashboard/user/index.php");
exit();
