<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../helpers.php';



// Display success/error messages

// Display success/error messages
if (isset($_SESSION['buy_success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show auto-hide" role="alert" data-bs-delay="5000">
            ' . $_SESSION['buy_success'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['buy_success']);
}

if (isset($_SESSION['buy_error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show auto-hide" role="alert" data-bs-delay="5000">
            ' . $_SESSION['buy_error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['buy_error']);
}

// Fetch verified sellers with listings
$sql = "SELECT 
            oil_listings.id AS listing_id,
            sellers.name AS seller_name,
            sellers.location,
            oil_listings.quantity,
            oil_listings.price_per_unit,
            oil_listings.available_quantity
        FROM oil_listings
        JOIN sellers ON sellers.id = oil_listings.seller_id
        WHERE sellers.verified = 1
        ORDER BY oil_listings.id DESC";
$listings = $conn->query($sql);
?>

<div class="container-fluid">
    <h4 class="mb-3">Buy Palm Oil</h4>
    <div class="row">
        <?php while ($row = $listings->fetch_assoc()): ?>
            <div class="col-12 col-md-6 col-lg-4 mb-3">
                <div class="glass-card p-3">
                    <h5><?= htmlspecialchars($row['seller_name']) ?></h5>
                    <small class="text-warning">📍 <?= $row['location'] ?></small>
                    <div class="mt-2 small">
                        Total Qty: <strong><?= $row['quantity'] ?> units</strong><br>
                        Available: <strong><?= $row['available_quantity'] ?> units</strong><br>
                        Price per unit: <strong>₦<?= number_format($row['price_per_unit']) ?></strong>
                    </div>
                    <button 
                        class="btn btn-success w-100 mt-3 buy-btn"
                        data-listing="<?= $row['listing_id'] ?>"
                        data-seller="<?= htmlspecialchars($row['seller_name']) ?>"
                        data-price="<?= $row['price_per_unit'] ?>"
                        data-available="<?= $row['available_quantity'] ?>"
                    >Buy Oil</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- BUY CONFIRMATION MODAL -->
<div class="modal fade" id="buyModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="margin-top:100px;">
    <div class="modal-content text-dark">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Purchase</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="/PalmVest/dashboard/actions/buy_action.php" method="POST" id="buyForm">
        <div class="modal-body">
            <p class="small text-muted">
                Seller: <strong id="sellerName"></strong><br>
                Price per unit: <strong id="unitPrice"></strong><br>
                Available: <strong id="availableQty"></strong>
            </p>

            <label>Quantity to Buy</label>
            <input type="number" min="1" class="form-control" name="buy_qty" id="buy_qty" required>

            <input type="hidden" name="listing_id" id="listing_id">
            <input type="hidden" name="unit_price" id="unit_price">

            <p class="mt-3 fw-bold">
                Total: <span id="totalPrice">₦0</span>
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cancel</button>
            <button  class="btn btn-success" type="submit" id="confirmBtn">Confirm Purchase</button>
        </div>
      </form>
    </div>
  </div>
</div>
