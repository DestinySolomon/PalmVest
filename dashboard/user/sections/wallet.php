<?php
// CORRECT PATHS for your structure
require_once __DIR__ .'/../../../config/db.php';
require_once __DIR__ . '/../helpers.php';

$user_id = $_SESSION['user_id'];

// Get current wallet balance
$wallet_balance = get_wallet_balance($user_id);

// Get recent wallet transactions (last 10)
$recent_transactions_sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt = $conn->prepare($recent_transactions_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_transactions = $stmt->get_result();

// Get wallet statistics
$stats_sql = "SELECT 
    COUNT(*) as total_transactions,
    SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_deposits,
    SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END) as total_withdrawals
FROM transactions 
WHERE user_id = ?";

$stmt2 = $conn->prepare($stats_sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$stats = $stmt2->get_result()->fetch_assoc();

// Get user's bank accounts
$bank_accounts_sql = "SELECT * FROM user_bank_accounts WHERE user_id = ? AND status = 'active' ORDER BY is_default DESC, id DESC";
$stmt3 = $conn->prepare($bank_accounts_sql);
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$bank_accounts = $stmt3->get_result();

// Get pending withdrawals
$pending_withdrawals_sql = "SELECT COUNT(*) as pending_count FROM withdrawal_requests WHERE user_id = ? AND status = 'pending'";
$stmt4 = $conn->prepare($pending_withdrawals_sql);
$stmt4->bind_param("i", $user_id);
$stmt4->execute();
$pending_result = $stmt4->get_result()->fetch_assoc();
$pending_withdrawals = $pending_result['pending_count'] ?? 0;
?>

<div class="container-fluid">
    <!-- Wallet Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">My Wallet</h4>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['wallet_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show auto-hide" role="alert" data-bs-delay="5000">
                    <?= $_SESSION['wallet_success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['wallet_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['wallet_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show auto-hide" role="alert" data-bs-delay="5000">
                    <?= $_SESSION['wallet_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['wallet_error']); ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Wallet Balance Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-card p-4 text-center wallet-balance-card">
                <div class="row align-items-center">
                    <div class="col-md-6 text-md-start">
                        <h6 class="text-muted mb-1">Available Balance</h6>
                        <h1 class="text-success mb-0">₦<?= number_format($wallet_balance, 2) ?></h1>
                        <small class="text-muted">Last updated: <?= date('M j, Y g:i A') ?></small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button class="btn btn-warning btn-lg me-2" data-bs-toggle="modal" data-bs-target="#fundWalletModal">
                            <i class="fas fa-plus me-1"></i> Fund Wallet
                        </button>
                        <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#withdrawModal" <?= $wallet_balance < 1000 ? 'disabled' : '' ?>>
                            <i class="fas fa-money-bill-wave me-1"></i> Withdraw
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet Statistics -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Total Deposits</h6>
                <h4 class="text-success">₦<?= number_format($stats['total_deposits'] ?? 0, 2) ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Total Withdrawals</h6>
                <h4 class="text-danger">₦<?= number_format(abs($stats['total_withdrawals'] ?? 0), 2) ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Total Transactions</h6>
                <h4 class="text-info"><?= $stats['total_transactions'] ?? 0 ?></h4>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="glass-card p-3 text-center">
                <h6 class="text-muted">Pending Withdrawals</h6>
                <h4 class="text-warning"><?= $pending_withdrawals ?></h4>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-card p-4">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#fundWalletModal">
                        <i class="fas fa-plus me-1"></i> Fund Wallet
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#withdrawModal" <?= $wallet_balance < 1000 ? 'disabled' : '' ?>>
                        <i class="fas fa-money-bill-wave me-1"></i> Withdraw Funds
                    </button>
                    <a href="index.php?page=buy_oil" class="btn btn-info">
                        <i class="fas fa-shopping-cart me-1"></i> Buy Palm Oil
                    </a>
                    <a href="index.php?page=bank-accounts" class="btn btn-outline-primary">
                        <i class="fas fa-university me-1"></i> Bank Accounts
                    </a>
                    <a href="index.php?page=transactions" class="btn btn-outline-secondary">
                        <i class="fas fa-history me-1"></i> View All Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Accounts Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Bank Accounts</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal">
                        <i class="fas fa-plus me-1"></i> Add Bank Account
                    </button>
                </div>
                
                <?php if ($bank_accounts->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($account = $bank_accounts->fetch_assoc()): ?>
                            <div class="col-md-6 mb-3">
                                <div class="bank-account-card p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($account['bank_name']) ?></h6>
                                            <p class="mb-1"><?= htmlspecialchars($account['account_number']) ?></p>
                                            <small class="text-muted"><?= htmlspecialchars($account['account_name']) ?></small>
                                        </div>
                                        <div>
                                            <?php if ($account['is_default']): ?>
                                                <span class="badge bg-success">Default</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-university fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No bank accounts added yet.</p>
                        <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addBankModal">
                            Add Your First Bank Account
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Recent Transactions</h5>
                    <a href="index.php?page=transactions" class="btn btn-sm btn-outline-info">View All</a>
                </div>

                <?php if ($recent_transactions->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($transaction = $recent_transactions->fetch_assoc()): 
                                    $is_credit = $transaction['amount'] > 0;
                                    $amount_class = $is_credit ? 'text-success' : 'text-danger';
                                    $type_badge = $is_credit ? 'bg-success' : 'bg-danger';
                                    $type_text = $is_credit ? 'Credit' : 'Debit';
                                    $amount_sign = $is_credit ? '+' : '-';
                                ?>
                                    <tr>
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
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-wallet fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No transactions yet. Fund your wallet to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Fund Wallet Modal -->
<div class="modal fade" id="fundWalletModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Fund Your Wallet</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../dashboard/actions/fund_wallet_action.php" method="POST" id="fundWalletForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount to Fund</label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number" class="form-control" name="amount" id="fundAmount" 
                                   min="1000" step="100" placeholder="Enter amount" required>
                        </div>
                        <small class="text-muted">Minimum amount: ₦1,000</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-control" name="payment_method" required>
                            <option value="">Select payment method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Debit/Credit Card</option>
                            <option value="ussd">USSD</option>
                            <option value="bank_deposit">Bank Deposit</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            After submitting, you will be redirected to complete your payment.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Proceed to Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Withdraw Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Withdraw Funds</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../dashboard/actions/withdraw_action.php" method="POST" id="withdrawForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Available Balance</label>
                        <input type="text" class="form-control" value="₦<?= number_format($wallet_balance, 2) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Withdrawal Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number" class="form-control" name="amount" id="withdrawAmount" 
                                   min="1000" max="<?= $wallet_balance ?>" step="100" placeholder="Enter amount" required>
                        </div>
                        <small class="text-muted">Minimum withdrawal: ₦1,000 | Maximum: ₦<?= number_format($wallet_balance, 2) ?></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bank Account</label>
                        <select class="form-control" name="bank_account_id" required>
                            <option value="">Select bank account</option>
                            <?php 
                            $bank_accounts->data_seek(0); // Reset pointer
                            while ($account = $bank_accounts->fetch_assoc()): 
                            ?>
                                <option value="<?= $account['id'] ?>" <?= $account['is_default'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($account['bank_name']) ?> - <?= htmlspecialchars($account['account_number']) ?> (<?= htmlspecialchars($account['account_name']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#addBankModal">Add new bank account</a>
                        </small>
                    </div>

                    <div class="alert alert-warning">
                        <small>
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Withdrawals are processed within 24 hours. A transaction fee may apply.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit Withdrawal Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Bank Account Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title">Add Bank Account</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../dashboard/actions/add_bank_account_action.php" method="POST" id="addBankForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Bank Name</label>
                        <select class="form-control" name="bank_name" required>
                            <option value="">Select Bank</option>
                            <option value="Access Bank">Access Bank</option>
                            <option value="First Bank">First Bank</option>
                            <option value="Guaranty Trust Bank">Guaranty Trust Bank</option>
                            <option value="Zenith Bank">Zenith Bank</option>
                            <option value="United Bank for Africa">United Bank for Africa</option>
                            <option value="Ecobank Nigeria">Ecobank Nigeria</option>
                            <option value="Fidelity Bank">Fidelity Bank</option>
                            <option value="Stanbic IBTC Bank">Stanbic IBTC Bank</option>
                            <option value="Sterling Bank">Sterling Bank</option>
                            <option value="Union Bank">Union Bank</option>
                            <option value="Wema Bank">Wema Bank</option>
                            <option value="Polaris Bank">Polaris Bank</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" class="form-control" name="account_number" maxlength="10" placeholder="Enter 10-digit account number" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" class="form-control" name="account_name" placeholder="Account name as it appears on bank records" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_default" id="isDefault" checked>
                        <label class="form-check-label" for="isDefault">Set as default account</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Bank Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
