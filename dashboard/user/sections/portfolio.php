<?php
require_once __DIR__ .'/../../../config/db.php';
require_once __DIR__ . '/../helpers.php';

$user_id = $_SESSION['user_id'];

// Fetch user's oil orders from oil_orders table (matches your buy_action.php)
$orders = $conn->query("
    SELECT 
        oo.*, 
        DATEDIFF(oo.stake_end, NOW()) AS days_left,
        s.name as seller_name,
        ol.seller_id
    FROM oil_orders oo
    LEFT JOIN oil_listings ol ON oo.listing_id = ol.id  
    LEFT JOIN sellers s ON ol.seller_id = s.id
    WHERE oo.user_id = $user_id AND oo.status != 'cancelled'
    ORDER BY oo.id DESC
");

// Calculate portfolio metrics
$metrics_sql = "SELECT 
    COUNT(*) as total_investments,
    SUM(quantity) as total_units,
    SUM(amount) as total_invested,
    AVG(price) as avg_purchase_price
FROM oil_orders 
WHERE user_id = $user_id AND status IN ('staked', 'active')";

$metrics_result = $conn->query($metrics_sql);
$metrics = $metrics_result->fetch_assoc();

// Get wallet balance
$wallet_balance = get_wallet_balance($user_id);
?>

<div class="container-fluid">
    <!-- Portfolio Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">My Portfolio</h4>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['buy_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show auto-hide" role="alert" data-bs-delay="5000">
                    <?= $_SESSION['buy_success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['buy_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['portfolio_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show auto-hide" role="alert" data-bs-delay="5000">
                    <?= $_SESSION['portfolio_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['portfolio_error']); ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Portfolio Summary Cards -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Total Investments</h6>
                <h4 class="text-warning"><?= $metrics['total_investments'] ?? 0 ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Oil Units</h6>
                <h4 class="text-success"><?= number_format($metrics['total_units'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Total Invested</h6>
                <h4 class="text-info">₦<?= number_format($metrics['total_invested'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Wallet Balance</h6>
                <h4 class="text-primary">₦<?= number_format($wallet_balance) ?></h4>
            </div>
        </div>
    </div>

    <!-- Active Investments Table -->
    <div class="row">
        <div class="col-12">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Active Investments</h5>
                    <span class="badge bg-success"><?= $orders->num_rows ?> active</span>
                </div>

                <?php if ($orders->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Seller</th>
                                    <th>Quantity</th>
                                    <th>Price/Unit</th>
                                    <th>Total Amount</th>
                                    <th>Purchase Date</th>
                                    <th>Matures In</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $orders->fetch_assoc()): 
                                    $is_matured = $order['days_left'] <= 0;
                                ?>
                                    <tr>
                                        <td>#<?= $order['id'] ?></td>
                                        <td><?= htmlspecialchars($order['seller_name'] ?? 'N/A') ?></td>
                                        <td><?= number_format($order['quantity']) ?> units</td>
                                        <td>₦<?= number_format($order['price'], 2) ?></td>
                                        <td>₦<?= number_format($order['amount']) ?></td>
                                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <?php if ($is_matured): ?>
                                                <span class="badge bg-success">Ready to Harvest</span>
                                            <?php else: ?>
                                                <span class="text-warning"><?= $order['days_left'] ?> days</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $order['status'] == 'staked' ? 'warning' : 
                                                ($order['status'] == 'harvested' ? 'success' : 'secondary')
                                            ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($is_matured && $order['status'] == 'staked'): ?>
                                                <button class="btn btn-sm btn-success harvest-btn" 
                                                        data-order-id="<?= $order['id'] ?>"
                                                        data-quantity="<?= $order['quantity'] ?>">
                                                    Harvest
                                                </button>
                                            <?php elseif ($order['status'] == 'staked'): ?>
                                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                                    Staked
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-oil-can fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Active Investments</h5>
                        <p class="text-muted">You haven't made any oil purchases yet.</p>
                        <a href="index.php?page=buy_oil" class="btn btn-warning">Buy Palm Oil Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="glass-card p-4">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="index.php?page=buy_oil" class="btn btn-warning">Buy More Oil</a>
                    <a href="index.php?page=wallet" class="btn btn-outline-info">Fund Wallet</a>
                    <a href="index.php?page=transactions" class="btn btn-outline-secondary">View Transactions</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Harvest Confirmation Modal -->
<div class="modal fade" id="harvestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Harvest Oil</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/harvest_action.php" method="POST" id="harvestForm">
                <div class="modal-body">
                    <p>Are you sure you want to harvest this oil investment?</p>
                    <p><strong>Quantity: <span id="harvestQuantity"></span> units</strong></p>
                    <p class="text-muted small">The oil will be converted to cash and credited to your wallet.</p>
                    
                    <input type="hidden" name="order_id" id="harvestOrderId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Harvest</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Harvest button functionality
document.addEventListener('DOMContentLoaded', function() {
    const harvestBtns = document.querySelectorAll('.harvest-btn');
    const harvestModal = new bootstrap.Modal(document.getElementById('harvestModal'));
    const harvestQuantity = document.getElementById('harvestQuantity');
    const harvestOrderId = document.getElementById('harvestOrderId');
    
    harvestBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const quantity = this.getAttribute('data-quantity');
            
            harvestOrderId.value = orderId;
            harvestQuantity.textContent = quantity;
            
            harvestModal.show();
        });
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert.auto-hide');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>