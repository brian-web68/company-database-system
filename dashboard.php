<?php
session_start();
include "db.php";

// 1. Security Check
if (!isset($_SESSION['full_name'])) {
    header("Location: login.php");
    exit();
}

// 2. Fetch Financial Stats for the current month
$current_month = date("F Y");

// Total Collected (Sum of all payments for this month)
$stmt_paid = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE rent_month = ?");
$stmt_paid->bind_param("s", $current_month);
$stmt_paid->execute();
$res_paid = $stmt_paid->get_result()->fetch_assoc();
$total_collected = $res_paid['total'] ?? 0;

// Tenant Stats
$total_tenants = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0;
$paid_count = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM payments WHERE rent_month = '$current_month'")->fetch_assoc()['count'] ?? 0;
$pending_tenants = $total_tenants - $paid_count;

// 3. Fetch Recent Transactions (Fixed: Sorting by p.id instead of created_at)
$recent_payments = $conn->query("
    SELECT p.*, u.full_name 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.id DESC LIMIT 6
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estate Pro | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-bg: #1a1d20; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .sidebar { min-height: 100vh; background: var(--sidebar-bg); color: #fff; position: sticky; top: 0; }
        .nav-link { color: #ced4da; transition: 0.3s; border-radius: 8px; margin: 4px 15px; }
        .nav-link:hover, .nav-link.active { background: #0d6efd; color: #fff; }
        .stat-card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .payment-badge { font-size: 0.7rem; padding: 4px 8px; border-radius: 20px; text-transform: uppercase; font-weight: bold; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block sidebar px-0 shadow">
            <div class="p-4 text-center">
                <h4 class="fw-bold text-primary mb-0">ESTATE PRO</h4>
                <p class="small text-muted mb-0 text-uppercase" style="letter-spacing: 1px;">Management</p>
            </div>
            <div class="nav flex-column mt-3">
                <a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="pages/rent.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Record Rent</a>
                <a href="pages/payments.php" class="nav-link"><i class="bi bi-wallet2 me-2"></i> Other Payments</a>
                <a href="pages/rent_status.php" class="nav-link"><i class="bi bi-list-check me-2"></i> Payment Status</a>
                <hr class="mx-3 opacity-25">
                <a href="pages/admin_complaints.php" class="nav-link"><i class="bi bi-chat-square-text me-2"></i> Complaints</a>
                <a href="logout.php" class="nav-link text-danger mt-5"><i class="bi bi-power me-2"></i> Logout</a>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold h3">Financial Summary <span class="text-muted fs-6 fw-normal"><?php echo date('M Y'); ?></span></h2>
                <div class="bg-white px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-person-circle text-primary me-2"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card stat-card bg-white p-3">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-cash-stack text-success fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Collected</h6>
                                <h3 class="fw-bold mb-0 text-dark">Ksh <?php echo number_format($total_collected, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-white p-3">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Collection Progress</h6>
                            <h4 class="fw-bold mb-2"><?php echo $paid_count; ?> <span class="fs-6 fw-normal text-muted">of <?php echo $total_tenants; ?> Tenants</span></h4>
                            <div class="progress" style="height: 8px;">
                                <?php $percent = ($total_tenants > 0) ? ($paid_count / $total_tenants) * 100 : 0; ?>
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-white p-3">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-person-x text-danger fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Defaulters</h6>
                                <h3 class="fw-bold mb-0 text-danger"><?php echo $pending_tenants; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0">Recent Transactions</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Member</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Date</th>
                                            <th class="text-center">Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($recent_payments && $recent_payments->num_rows > 0): ?>
                                            <?php while($row = $recent_payments->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                                    <small class="text-muted"><?php echo $row['rent_month']; ?></small>
                                                </td>
                                                <td class="text-success fw-bold">Ksh <?php echo number_format($row['amount'], 2); ?></td>
                                                <td>
                                                    <?php if($row['payment_type'] == 'mpesa'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success payment-badge"><i class="bi bi-phone"></i> M-Pesa</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary payment-badge"><i class="bi bi-cash"></i> Cash</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="small"><?php echo date('d-M', strtotime($row['payment_date'])); ?></td>
                                                <td class="text-center">
                                                    <a href="pages/receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                                        <i class="bi bi-printer"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center py-4 text-muted">No transactions found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-primary text-white rounded-4 p-4">
                        <h5 class="fw-bold mb-3">Quick Actions</h5>
                        <div class="d-grid gap-3">
                            <a href="pages/rent.php" class="btn btn-light btn-lg fs-6 fw-bold text-primary shadow-sm"><i class="bi bi-plus-circle me-2"></i> Record Rent</a>
                            <a href="pages/payments.php" class="btn btn-outline-light"><i class="bi bi-coin me-2"></i> Other Payment</a>
                            <a href="pages/admin_complaints.php" class="btn btn-outline-light border-0 text-start ps-0"><i class="bi bi-megaphone me-2"></i> View Recent Complaints</a>
                        </div>
                        <div class="mt-4 pt-3 border-top border-white border-opacity-25">
                            <p class="small mb-0 opacity-75">Next sync with M-Pesa API scheduled in 5 minutes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>