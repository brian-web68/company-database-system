<?php
session_start();
include "../db.php"; // Going up one level to find db.php

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div class='alert alert-danger'>Error: Receipt ID missing.</div>");
}

$payment_id = $_GET['id'];

// Fetch payment details with tenant name
$stmt = $conn->prepare("
    SELECT p.*, u.full_name 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    die("<div class='alert alert-danger'>Error: Receipt not found.</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt_<?php echo $payment['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .receipt-container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .receipt-header { border-bottom: 2px solid #f0f0f0; margin-bottom: 20px; padding-bottom: 10px; }
        .info-label { color: #6c757d; font-weight: 500; }
        .info-value { font-weight: 600; text-align: right; }
        .total-section { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; }
        
        /* PRINT STYLES */
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            .receipt-container { margin: 0; box-shadow: none; border: 1px solid #eee; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="receipt-container">
        <div class="receipt-header text-center">
            <h4 class="fw-bold text-primary mb-1">ESTATE PRO SYSTEM</h4>
            <p class="text-muted small">Official Payment Receipt</p>
        </div>

        <div class="row mb-2">
            <div class="col-6 info-label">Receipt No:</div>
            <div class="col-6 info-value">#<?php echo $payment['id']; ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-6 info-label">Date:</div>
            <div class="col-6 info-value"><?php echo date("d M Y", strtotime($payment['payment_date'])); ?></div>
        </div>
        <hr class="text-muted">
        <div class="row mb-2">
            <div class="col-6 info-label">Tenant Name:</div>
            <div class="col-6 info-value"><?php echo htmlspecialchars($payment['full_name']); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-6 info-label">Payment Type:</div>
            <div class="col-6 info-value"><?php echo ucfirst($payment['payment_type']); ?></div>
        </div>
        <?php if(!empty($payment['rent_month'])): ?>
        <div class="row mb-2">
            <div class="col-6 info-label">Period:</div>
            <div class="col-6 info-value"><?php echo $payment['rent_month']; ?></div>
        </div>
        <?php endif; ?>

        <div class="total-section">
            <div class="row">
                <div class="col-6 fw-bold">Amount Paid:</div>
                <div class="col-6 text-end fw-bold text-success">KES <?php echo number_format($payment['amount'], 2); ?></div>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="small text-muted italic">Issued by: <?php echo $_SESSION['full_name']; ?></p>
            <p class="fw-bold small">Thank you for your payment!</p>
        </div>

        <div class="mt-4 d-grid gap-2 no-print">
            <button onclick="window.print()" class="btn btn-dark">
                <i class="bi bi-printer me-2"></i> Print Receipt
            </button>
            <div class="row g-2">
                <div class="col-6">
                    <a href="rent.php" class="btn btn-outline-primary w-100">New Payment</a>
                </div>
                <div class="col-6">
                    <a href="../dashboard.php" class="btn btn-outline-secondary w-100">Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>