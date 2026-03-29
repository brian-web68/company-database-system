<?php
session_start();
// Verify the path to db.php is correct from the /pages folder
include "../db.php"; 

if (!isset($_SESSION['full_name'])) {
    header("Location: ../login.php");
    exit();
}

// Current Month Logic
$month = date("F Y"); // Dynamically gets "March 2026"

// 1. Fetch Paid Tenants (Using Prepared Statements for safety)
$paid_sql = "SELECT u.full_name, p.amount, p.payment_date 
             FROM users u 
             JOIN payments p ON u.id = p.user_id 
             WHERE p.payment_type = 'rent' AND p.rent_month = ?";
$stmt1 = $conn->prepare($paid_sql);
$stmt1->bind_param("s", $month);
$stmt1->execute();
$paid_result = $stmt1->get_result();

// 2. Fetch Unpaid Tenants
$unpaid_sql = "SELECT full_name FROM users 
               WHERE id NOT IN (
                   SELECT user_id FROM payments 
                   WHERE payment_type = 'rent' AND rent_month = ?
               )";
$stmt2 = $conn->prepare($unpaid_sql);
$stmt2->bind_param("s", $month);
$stmt2->execute();
$unpaid_result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Status - <?php echo $month; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-check text-primary"></i> Rent Status: <?php echo $month; ?></h2>
        <a href="../dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-house-door"></i> Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-check-circle-fill"></i> Paid Tenants
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Amount</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php while($row = $paid_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td class="text-success fw-bold">Ksh <?php echo number_format($row['amount']); ?></td>
                                <td><?php echo date("M d, Y", strtotime($row['payment_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-x-circle-fill"></i> Pending Payments
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Name</th><th class="text-end">Status</th></tr>
                        </thead>
                        <tbody>
                            <?php while($row = $unpaid_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td class="text-end"><span class="badge bg-warning text-dark">Unpaid</span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>