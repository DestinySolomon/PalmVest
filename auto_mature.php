<?php
require_once "config/db.php";
require_once "dashboard/user/helpers.php";

$sql = "SELECT * FROM oil_stakes WHERE matures_at <= NOW() AND status = 'pending'";
$res = $conn->query($sql);

while ($stake = $res->fetch_assoc()) {

    $user = $stake['user_id'];
    $quantity = $stake['quantity'];
    $purchase_price = $stake['purchase_price'];

    // 15% ROI
    $roi = $quantity * $purchase_price * 0.15;

    // Credit ROI
    creditWallet($user, $roi);

    // Log transaction
    logTransaction($user, $roi, "30-day ROI matured");

    // Mark stake as paid
    $conn->query("UPDATE oil_stakes SET status='paid' WHERE id=" . $stake['id']);
}

echo "Auto-mature processing completed.";
?>
