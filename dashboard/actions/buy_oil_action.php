<?php
session_start();
require_once "../../config/db.php";
require_once "../helpers.php";

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) die("Unauthorized");

$listing_id = intval($_POST['listing_id']);
$buy_qty    = intval($_POST['buy_qty']);
$unit_price = floatval($_POST['unit_price']);
$total_cost = $buy_qty * $unit_price;

// 1. Check wallet balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
if (!$userData) die("User not found");
if ($userData['balance'] < $total_cost) die("Insufficient funds!");

// 2. Check seller stock
$stmt2 = $conn->prepare("SELECT seller_id, available_quantity FROM oil_listings WHERE id=?");
$stmt2->bind_param("i", $listing_id);
$stmt2->execute();
$sellerData = $stmt2->get_result()->fetch_assoc();
if (!$sellerData) die("Listing not found");
if ($buy_qty > $sellerData['available_quantity']) die("Seller does not have enough units.");

// 3. Deduct wallet
$stmt3 = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id=?");
$stmt3->bind_param("di", $total_cost, $user_id);
$stmt3->execute();
logTransaction($user_id, $total_cost, "Bought $buy_qty units of oil");

// 4. Deposit & stake
$stmt4 = $conn->prepare("INSERT INTO oil_stakes (user_id, quantity, purchase_price, matures_at, status) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), 'pending')");
$stmt4->bind_param("idd", $user_id, $buy_qty, $unit_price);
$stmt4->execute();

// 5. Reduce seller quantity
$stmt5 = $conn->prepare("UPDATE oil_listings SET available_quantity = available_quantity - ? WHERE id=?");
$stmt5->bind_param("ii", $buy_qty, $listing_id);
$stmt5->execute();

echo "Purchase successful! Your oil has been staked.";
