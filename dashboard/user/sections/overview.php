<?php
$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/../helpers.php';

$wallet_bal = number_format(get_wallet_balance($user_id), 2);
$total_oil   = get_total_oil_purchased($user_id);
$ref_code    = $_SESSION['ref_code'] ?? '';
$referrals   = get_referral_count($ref_code);
$unread      = get_unread_notifications($user_id);
?>

<div class="container-fluid">

    <!-- ✅ TOP STATS ROW -->
    <div class="row gx-3 mb-3">

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                <small>Wallet Balance</small>
                <h4>₦<?= $wallet_bal ?></h4>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-droplet-half"></i></div>
                <small>Total Oil Units</small>
                <h4><?= $total_oil ?> Units</h4>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <small>Your Referrals</small>
                <h4><?= $referrals ?></h4>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-bell-fill"></i></div>
                <small>Notifications</small>
                <h4><?= $unread ?> New</h4>
            </div>
        </div>

    </div>


    <!-- ✅ MARKET TREND -->
    <div class="glass-card mb-3">
        <h5>Market Trend</h5>
        <canvas id="oilChart" height="100"></canvas>
    </div>


    <!-- ✅ VERIFIED SELLERS -->
    <div class="glass-card mb-3">
        <h5>Verified Sellers</h5>
        <ul>
            <li>Emeka Farms — 10 rubbers — ₦45,000 ✓</li>
            <li>Chinwe Premium — 7 rubbers — ₦46,500 ✓</li>
            <li>Ade Palm Gold — 5 rubbers — ₦47,000 ✓</li>
        </ul>
    </div>

</div>
