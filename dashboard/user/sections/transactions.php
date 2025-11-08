<?php
require_once __DIR__ .'/../../../config/db.php';
require_once __DIR__ . '/../helpers.php';
$user_id = $_SESSION['user_id'];

// Fetch all transactions for the user
$transactions_sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($transactions_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result();

// Calculate transaction statistics
$stats_sql = "SELECT 
    COUNT(*) as total_transactions,
    SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END) as total_expenses
FROM transactions 
WHERE user_id = ?";

$stmt2 = $conn->prepare($stats_sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$stats = $stmt2->get_result()->fetch_assoc();
?>

<div class="container-fluid">
    <!-- Transactions Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Transaction History</h4>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['transaction_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show auto-hide" role="alert" data-bs-delay="5000">
                    <?= $_SESSION['transaction_success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['transaction_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['transaction_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show auto-hide" role="alert" data-bs-delay="5000">
                    <?= $_SESSION['transaction_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['transaction_error']); ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Transaction Statistics Cards -->
    <div class="row mb-4">
        <div class="col-6 col-md-4 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Total Transactions</h6>
                <h4 class="text-warning"><?= $stats['total_transactions'] ?? 0 ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-4 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Total Income</h6>
                <h4 class="text-success">₦<?= number_format($stats['total_income'] ?? 0, 2) ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-4 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Total Expenses</h6>
                <h4 class="text-danger">₦<?= number_format(abs($stats['total_expenses'] ?? 0), 2) ?></h4>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="glass-card p-3">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" class="form-control" id="searchTransactions" placeholder="Search transactions...">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" id="filterType">
                            <option value="">All Types</option>
                            <option value="credit">Income</option>
                            <option value="debit">Expenses</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" id="filterDate">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>All Transactions</h5>
                    <span class="badge bg-info"><?= $transactions->num_rows ?> records</span>
                </div>

                <?php if ($transactions->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover" id="transactionsTable">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Balance After</th>
                                    <th>Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($transaction = $transactions->fetch_assoc()): 
                                    $is_credit = $transaction['amount'] > 0;
                                    $amount_class = $is_credit ? 'text-success' : 'text-danger';
                                    $type_badge = $is_credit ? 'bg-success' : 'bg-danger';
                                    $type_text = $is_credit ? 'Credit' : 'Debit';
                                    $amount_sign = $is_credit ? '+' : '-';
                                ?>
                                    <tr class="transaction-row">
                                        <td>
                                            <small><?= date('M j, Y', strtotime($transaction['created_at'])) ?></small><br>
                                            <small class="text-muted"><?= date('g:i A', strtotime($transaction['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($transaction['description']) ?></div>
                                            <?php if (!empty($transaction['metadata'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($transaction['metadata']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $type_badge ?>"><?= $type_text ?></span>
                                        </td>
                                        <td class="<?= $amount_class ?> fw-bold">
                                            <?= $amount_sign ?>₦<?= number_format(abs($transaction['amount']), 2) ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">₦<?= number_format($transaction['balance_after'] ?? 0, 2) ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted">#<?= $transaction['id'] ?></small>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Export Button -->
                    <div class="mt-3">
                        <button class="btn btn-outline-info btn-sm" onclick="exportTransactions()">
                            <i class="fas fa-download me-1"></i> Export to CSV
                        </button>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Transactions Yet</h5>
                        <p class="text-muted">Your transaction history will appear here.</p>
                        <a href="index.php?page=buy_oil" class="btn btn-warning">Make Your First Purchase</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Summary -->
    <div class="row mt-4">
        <div class="col-12 col-md-6">
            <div class="glass-card p-4">
                <h6 class="mb-3">Transaction Summary</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end border-secondary">
                            <h4 class="text-success">₦<?= number_format($stats['total_income'] ?? 0, 2) ?></h4>
                            <small class="text-muted">Total Income</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div>
                            <h4 class="text-danger">₦<?= number_format(abs($stats['total_expenses'] ?? 0), 2) ?></h4>
                            <small class="text-muted">Total Expenses</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="glass-card p-4">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="index.php?page=buy_oil" class="btn btn-warning">Buy Palm Oil</a>
                    <a href="index.php?page=wallet" class="btn btn-outline-info">Fund Wallet</a>
                    <a href="index.php?page=portfolio" class="btn btn-outline-success">View Portfolio</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Filter and search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchTransactions');
    const filterType = document.getElementById('filterType');
    const filterDate = document.getElementById('filterDate');
    const transactionRows = document.querySelectorAll('.transaction-row');

    function filterTransactions() {
        const searchTerm = searchInput.value.toLowerCase();
        const typeFilter = filterType.value;
        const dateFilter = filterDate.value;
        
        const now = new Date();
        
        transactionRows.forEach(row => {
            const description = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const type = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const dateText = row.querySelector('td:nth-child(1)').textContent;
            
            let matchesSearch = description.includes(searchTerm);
            let matchesType = !typeFilter || 
                (typeFilter === 'credit' && type.includes('credit')) ||
                (typeFilter === 'debit' && type.includes('debit'));
            let matchesDate = true;
            
            // Date filtering logic
            if (dateFilter && dateText) {
                const rowDate = new Date(dateText);
                switch(dateFilter) {
                    case 'today':
                        matchesDate = rowDate.toDateString() === now.toDateString();
                        break;
                    case 'week':
                        const weekAgo = new Date(now - 7 * 24 * 60 * 60 * 1000);
                        matchesDate = rowDate >= weekAgo;
                        break;
                    case 'month':
                        const monthAgo = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate());
                        matchesDate = rowDate >= monthAgo;
                        break;
                }
            }
            
            row.style.display = (matchesSearch && matchesType && matchesDate) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTransactions);
    filterType.addEventListener('change', filterTransactions);
    filterDate.addEventListener('change', filterTransactions);
    
    // Auto-hide alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert.auto-hide');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

// Export to CSV functionality
function exportTransactions() {
    const rows = [];
    const headers = ['Date', 'Time', 'Description', 'Type', 'Amount', 'Reference'];
    
    // Add headers
    rows.push(headers.join(','));
    
    // Add data rows
    document.querySelectorAll('.transaction-row').forEach(row => {
        if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            const rowData = [
                cells[0].querySelector('small:first-child').textContent.trim(),
                cells[0].querySelector('small:last-child').textContent.trim(),
                `"${cells[1].querySelector('.fw-bold').textContent.trim()}"`,
                cells[2].textContent.trim(),
                cells[3].textContent.trim().replace('₦', ''),
                cells[5].textContent.trim().replace('#', '')
            ];
            rows.push(rowData.join(','));
        }
    });
    
    const csvContent = rows.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `transactions-${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>