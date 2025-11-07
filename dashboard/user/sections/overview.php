<?php
$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/../helpers.php';

$wallet_bal = number_format(get_wallet_balance($user_id),2);
$total_oil   = get_total_oil_purchased($user_id);
$ref_code    = $_SESSION['ref_code'] ?? '';
$referrals   = get_referral_count($ref_code);
$unread      = get_unread_notifications($user_id);
$recent      = get_recent_transactions($user_id,5);
?>

<div class="container-fluid">

    <!-- STATS ROW -->
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
                <small>Oil Purchased</small>
                <h4><?= $total_oil ?> Units</h4>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                <small>Referrals</small>
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

    <!-- MAIN CONTENT BELOW -->
    <div class="row gx-3">

        <div class="col-12 col-lg-8">
            <div class="glass-card mb-3">
                <h5>Market Trend</h5>
                <canvas id="oilChart" height="100"></canvas>
            </div>

            <div class="glass-card mb-3">
                <h5>Verified Sellers</h5>
                <ul>
                    <li>Emeka Farms — 10 rubbers — ₦45,000 ✓</li>
                    <li>Chinwe Premium — 7 rubbers — ₦46,500 ✓</li>
                    <li>Ade Palm Gold — 5 rubbers — ₦47,000 ✓</li>
                </ul>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="glass-card mb-3">
                <h5>Your Portfolio (Quick)</h5>
                <ul class="small">
                    <li>Total Invested: <strong>₦315,000</strong></li>
                    <li>Active Stakes: <strong>3</strong></li>
                    <li>Pending Returns: <strong>₦157,500</strong></li>
                </ul>
                <a href="?page=portfolio" data-ajax class="btn btn-sm btn-outline-light mt-2">
                    Open Portfolio
                </a>
            </div>

            <div class="glass-card">
                <h5>Recent Transactions</h5>
                <ul class="list-unstyled">
                    <?php foreach($recent as $tx): ?>
                        <li class="small mb-2">
                            <strong><?= $tx['dt'] ?></strong><br>
                            <?= htmlspecialchars($tx['description']) ?><br>
                            ₦<?= number_format($tx['amount']) ?>
                            — <span class="text-muted"><?= $tx['status'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="?page=transactions" data-ajax class="btn btn-sm btn-outline-light">
                    View All
                </a>
            </div>
        </div>

    </div>
</div>
